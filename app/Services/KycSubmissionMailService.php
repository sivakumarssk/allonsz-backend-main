<?php

namespace App\Services;

use App\Models\KycDocumentSubmission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class KycSubmissionMailService
{
    /** @var MicrosoftGraphMailService */
    protected $graph;

    public function __construct(MicrosoftGraphMailService $graph)
    {
        $this->graph = $graph;
    }

    public function send(KycDocumentSubmission $submission, User $user): void
    {
        $to = config('kyc.internal_mailbox');
        $replyTo = $user->email ?: null;

        [$subject, $plain, $html] = $this->buildContent($submission, $user);
        $attachmentMeta = $this->buildAttachmentMeta($submission);

        $totalBytes = 0;
        foreach ($attachmentMeta as $a) {
            $totalBytes += $a['size'];
        }

        $graphLimit = (int) config('kyc.graph_max_attachment_bytes');
        $useGraph = $this->graph->isConfigured()
            && $totalBytes <= $graphLimit
            && count($attachmentMeta) > 0;

        if ($useGraph) {
            try {
                $graphAttachments = [];
                foreach ($attachmentMeta as $a) {
                    $graphAttachments[] = [
                        '@odata.type' => '#microsoft.graph.fileAttachment',
                        'name' => $a['name'],
                        'contentType' => $a['mime'],
                        'contentBytes' => base64_encode(file_get_contents($a['absolute_path'])),
                    ];
                }
                $this->graph->sendMail($subject, $plain, $html, $to, $replyTo, $graphAttachments);
                return;
            } catch (\Throwable $e) {
                Log::warning('KYC mail: Graph send failed, falling back to SMTP', [
                    'submission_id' => $submission->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $this->sendViaSmtp($subject, $plain, $html, $to, $replyTo, $attachmentMeta);
    }

    public function sendUserConfirmation(KycDocumentSubmission $submission, User $user): void
    {
        if (empty($user->email)) {
            return;
        }

        $setting = Setting::first();
        $logo = $setting->logo ?? null;
        $business = $setting->bussiness_name ?? config('app.name');
        $emailSupport = $setting->email_support ?? null;

        $hasTransaction = $this->submissionHasField($submission, 'transaction_details');

        $payload = [
            'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->username ?? 'there'),
            'submission_id' => $submission->id,
            'submitted_at' => optional($submission->submitted_at ?? $submission->created_at)->format('d M Y h:i A'),
            'referal_code' => $user->referal_code ?? null,
            'has_transaction_details' => $hasTransaction,
            'logo' => $logo,
            'business_name' => $business,
            'email_support' => $emailSupport,
        ];

        $subject = 'KYC Documents Received - '.$business;

        try {
            Mail::send('email.kyc_received', $payload, function ($message) use ($user, $subject) {
                $message->to($user->email, $payload['name'] ?? null)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('KYC user confirmation email failed', [
                'submission_id' => $submission->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected function buildContent(KycDocumentSubmission $submission, User $user): array
    {
        $submittedAt = $submission->submitted_at
            ? $submission->submitted_at->toIso8601String()
            : $submission->created_at->toIso8601String();

        $hasTransaction = $this->submissionHasField($submission, 'transaction_details');

        $lines = [
            'KYC document submission received.',
            '',
            'User ID:        '.$user->id,
            'Name:           '.trim($user->first_name.' '.$user->last_name),
            'Username:       '.($user->username ?? ''),
            'Email:          '.($user->email ?? ''),
            'Phone:          '.($user->phone ?? ''),
            'Referral code:  '.($user->referal_code ?? ''),
            'Upliner:        '.($user->referal_id ?? ''),
            'Submitted at:   '.$submittedAt,
            'Documents attached:',
            '  - PAN Card',
            '  - Aadhar Card (front)',
            '  - Aadhar Card (back)',
            '  - Bank Details',
        ];
        if ($hasTransaction) {
            $lines[] = '  - Other / Transaction docs (if any)';
        }
        $lines[] = '';
        $lines[] = 'Submission ID: '.$submission->id;

        $plain = implode("\n", $lines);

        $esc = function ($v) {
            return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $html = '<p>KYC document submission received.</p>'
            .'<table style="border-collapse:collapse">'
            .'<tr><td>User ID</td><td>'.$esc($user->id).'</td></tr>'
            .'<tr><td>Name</td><td>'.$esc(trim($user->first_name.' '.$user->last_name)).'</td></tr>'
            .'<tr><td>Username</td><td>'.$esc($user->username ?? '').'</td></tr>'
            .'<tr><td>Email</td><td>'.$esc($user->email ?? '').'</td></tr>'
            .'<tr><td>Phone</td><td>'.$esc($user->phone ?? '').'</td></tr>'
            .'<tr><td>Referral code</td><td>'.$esc($user->referal_code ?? '').'</td></tr>'
            .'<tr><td>Upliner</td><td>'.$esc($user->referal_id ?? '').'</td></tr>'
            .'<tr><td>Submitted at</td><td>'.$esc($submittedAt).'</td></tr>'
            .'</table>'
            .'<p>Documents attached:</p><ul>'
            .'<li>PAN Card</li>'
            .'<li>Aadhar Card (front)</li>'
            .'<li>Aadhar Card (back)</li>'
            .'<li>Bank Details</li>'
            .($hasTransaction ? '<li>Other / Transaction docs</li>' : '')
            .'</ul>'
            .'<p>Submission ID: '.$esc($submission->id).'</p>';

        $subject = 'KYC Documents — '.trim($user->first_name.' '.$user->last_name)
            .' ('.($user->referal_code ?? '').')';

        return [$subject, $plain, $html];
    }

    protected function submissionHasField(KycDocumentSubmission $submission, string $field): bool
    {
        foreach ($submission->file_paths as $row) {
            if (($row['field'] ?? '') === $field) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{name: string, mime: string, absolute_path: string, size: int}>
     */
    protected function buildAttachmentMeta(KycDocumentSubmission $submission): array
    {
        $out = [];
        foreach ($submission->file_paths as $row) {
            $relative = $row['path'] ?? null;
            if (!$relative) {
                continue;
            }
            $absolute = Storage::disk('local')->path($relative);
            if (!is_file($absolute)) {
                throw new \RuntimeException('Stored KYC file missing');
            }
            $out[] = [
                'name' => $row['original_name'] ?? basename($relative),
                'mime' => $row['mime'] ?? 'application/octet-stream',
                'absolute_path' => $absolute,
                'size' => filesize($absolute) ?: 0,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, array{name: string, mime: string, absolute_path: string, size: int}>  $attachmentMeta
     */
    protected function sendViaSmtp(
        string $subject,
        string $plain,
        string $html,
        string $to,
        ?string $replyTo,
        array $attachmentMeta
    ): void {
        Mail::send([], [], function ($message) use ($subject, $plain, $html, $to, $replyTo, $attachmentMeta) {
            $from = config('kyc.internal_mailbox');
            $message->from($from, config('app.name'));
            $message->to($to)->subject($subject);

            if ($replyTo) {
                $message->replyTo($replyTo);
            }

            $swift = $message->getSwiftMessage();
            $swift->setBody($html, 'text/html');
            $swift->addPart($plain, 'text/plain');

            foreach ($attachmentMeta as $a) {
                $message->attach($a['absolute_path'], [
                    'as' => $a['name'],
                    'mime' => $a['mime'],
                ]);
            }
        });
    }
}
