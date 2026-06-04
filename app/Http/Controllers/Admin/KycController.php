<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocumentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function index()
    {
        $submissions = KycDocumentSubmission::with('user')
            ->orderByDesc('id')
            ->get();

        return view('admin.kyc.index', compact('submissions'));
    }

    public function show($id)
    {
        $submission = KycDocumentSubmission::with('user')->findOrFail($id);

        $files = collect($submission->file_paths ?? [])->map(function ($row) use ($submission) {
            $path = $row['path'] ?? null;
            $exists = $path ? Storage::disk('local')->exists($path) : false;
            $size = $exists ? Storage::disk('local')->size($path) : 0;

            return [
                'field' => $row['field'] ?? '',
                'original_name' => $row['original_name'] ?? basename((string) $path),
                'mime' => $row['mime'] ?? null,
                'path' => $path,
                'exists' => $exists,
                'size' => $size,
                'view_url' => $exists ? url('admin/kyc/'.$submission->id.'/file/'.urlencode($row['field'] ?? '')) : null,
                'download_url' => $exists ? url('admin/kyc/'.$submission->id.'/download/'.urlencode($row['field'] ?? '')) : null,
            ];
        });

        return view('admin.kyc.show', compact('submission', 'files'));
    }

    public function file($id, $field, Request $request)
    {
        return $this->serveFile($id, $field, false);
    }

    public function download($id, $field)
    {
        return $this->serveFile($id, $field, true);
    }

    private function serveFile($id, $field, bool $download)
    {
        $submission = KycDocumentSubmission::findOrFail($id);
        $row = collect($submission->file_paths ?? [])
            ->firstWhere('field', $field);

        if (!$row || empty($row['path'])) {
            abort(404);
        }

        $path = $row['path'];
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $absolutePath = Storage::disk('local')->path($path);
        $name = $row['original_name'] ?? basename($path);
        $mime = $row['mime'] ?? 'application/octet-stream';

        $headers = [
            'Content-Type' => $mime,
        ];

        if ($download) {
            return response()->download($absolutePath, $name, $headers);
        }

        return response()->file($absolutePath, $headers);
    }
}
