<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendKycSubmissionEmailJob;
use App\Models\KycDocumentSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;

class KycDocumentController extends Controller
{
    /** @var string[] */
    private const FILE_FIELDS = ['pan_card', 'aadhar_card', 'aadhar_card_back', 'bank_details', 'transaction_details'];

    public function store(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $dupWindow = (int) config('kyc.duplicate_submission_window_minutes', 10);
            $existing = KycDocumentSubmission::query()
                ->where('user_id', $user->id)
                ->where('email_status', KycDocumentSubmission::EMAIL_PENDING)
                ->where('created_at', '>=', now()->subMinutes($dupWindow))
                ->orderByDesc('id')
                ->first();
            if ($existing) {
                return response()->json([
                    'status' => 'ok',
                    'submission_id' => $existing->id,
                    'email_status' => $existing->email_status,
                ], 200);
            }

            $rules = [
                'pan_card' => 'required|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
                'aadhar_card' => 'required|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
                'aadhar_card_back' => 'required|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
                'bank_details' => 'required|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
                'transaction_details' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
            ];
            $messages = [
                'pan_card.required' => 'PAN card is required',
                'aadhar_card.required' => 'Aadhar card (front) is required',
                'aadhar_card_back.required' => 'Aadhar card (back) is required',
                'bank_details.required' => 'Bank details document is required',
                'pan_card.mimetypes' => 'PAN card must be a JPEG, PNG, or PDF file',
                'aadhar_card.mimetypes' => 'Aadhar card (front) must be a JPEG, PNG, or PDF file',
                'aadhar_card_back.mimetypes' => 'Aadhar card (back) must be a JPEG, PNG, or PDF file',
                'bank_details.mimetypes' => 'Bank details must be a JPEG, PNG, or PDF file',
                'transaction_details.mimetypes' => 'Transaction details must be a JPEG, PNG, or PDF file',
                'pan_card.max' => 'PAN card must not exceed 5 MB',
                'aadhar_card.max' => 'Aadhar card (front) must not exceed 5 MB',
                'aadhar_card_back.max' => 'Aadhar card (back) must not exceed 5 MB',
                'bank_details.max' => 'Bank details must not exceed 5 MB',
                'transaction_details.max' => 'Transaction details must not exceed 5 MB',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $maxTotal = (int) config('kyc.max_total_bytes');
            $total = 0;
            foreach (self::FILE_FIELDS as $field) {
                if ($request->hasFile($field)) {
                    $total += (int) $request->file($field)->getSize();
                }
            }
            if ($total > $maxTotal) {
                return response()->json(['error' => 'Total upload size must not exceed 20 MB'], 422);
            }

            $rateKey = 'kyc-upload:'.$user->id;
            if (RateLimiter::tooManyAttempts($rateKey, 5)) {
                return response()->json([
                    'error' => 'Too many KYC document submissions. Please try again later.',
                ], 429);
            }

            $ts = now()->format('YmdHis').'_'.substr(str_replace('.', '', (string) microtime(true)), -6);
            $baseDir = 'kyc/'.$user->id;
            Storage::disk('local')->makeDirectory($baseDir);

            $savedPaths = [];
            $fileRows = [];

            try {
                DB::beginTransaction();

                foreach (self::FILE_FIELDS as $field) {
                    if (!$request->hasFile($field)) {
                        continue;
                    }
                    $upload = $request->file($field);
                    $ext = strtolower($upload->getClientOriginalExtension()) ?: 'bin';
                    $storedName = $ts.'_'.$field.'.'.$ext;
                    $upload->storeAs($baseDir, $storedName, 'local');
                    $relativePath = $baseDir.'/'.$storedName;
                    $savedPaths[] = $relativePath;

                    $fullPath = Storage::disk('local')->path($relativePath);
                    $mime = $upload->getMimeType() ?: 'application/octet-stream';

                    if (in_array($mime, ['image/jpeg', 'image/png'], true)) {
                        try {
                            $driver = extension_loaded('imagick') ? 'imagick' : 'gd';
                            $manager = new ImageManager(['driver' => $driver]);
                            $img = $manager->make($fullPath);
                            $img->orientate();
                            $img->save($fullPath);
                        } catch (\Throwable $e) {
                            Log::warning('KYC image re-encode skipped', [
                                'field' => $field,
                                'message' => $e->getMessage(),
                            ]);
                        }
                    }

                    $fileRows[] = [
                        'field' => $field,
                        'path' => $relativePath,
                        'original_name' => $upload->getClientOriginalName(),
                        'mime' => $mime,
                    ];
                }

                $submission = KycDocumentSubmission::query()->create([
                    'user_id' => $user->id,
                    'file_paths' => $fileRows,
                    'submitted_at' => now(),
                    'email_status' => KycDocumentSubmission::EMAIL_PENDING,
                ]);

                $user->kyc_status = 'documents_submitted';
                $user->email_document_status = 'Pending';
                $user->save();

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                foreach ($savedPaths as $path) {
                    Storage::disk('local')->delete($path);
                }
                throw $e;
            }

            RateLimiter::hit($rateKey, 3600);

            SendKycSubmissionEmailJob::dispatch($submission->id);

            $submission->refresh();

            return response()->json([
                'status' => 'ok',
                'submission_id' => $submission->id,
                'email_status' => $submission->email_status,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('KYC upload server error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Server error, please retry'], 500);
        }
    }
}
