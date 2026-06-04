<?php

namespace App\Jobs;

use App\Models\KycDocumentSubmission;
use App\Services\KycSubmissionMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendKycSubmissionEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int */
    public $submissionId;

    public function __construct(int $submissionId)
    {
        $this->submissionId = $submissionId;
    }

    public function handle(KycSubmissionMailService $mailer): void
    {
        $submission = KycDocumentSubmission::find($this->submissionId);
        if (!$submission || $submission->email_status === KycDocumentSubmission::EMAIL_SENT) {
            return;
        }

        $user = $submission->user;
        if (!$user) {
            Log::error('KYC submission missing user', ['submission_id' => $this->submissionId]);

            return;
        }

        try {
            $mailer->send($submission, $user);
            $submission->update([
                'email_status' => KycDocumentSubmission::EMAIL_SENT,
                'email_error' => null,
            ]);
            $user->email_document_status = 'Verified';
            $user->save();

            $mailer->sendUserConfirmation($submission, $user);
        } catch (\Throwable $e) {
            Log::error('KYC submission email failed', [
                'submission_id' => $submission->id,
                'message' => $e->getMessage(),
            ]);
            $submission->increment('email_retry_count');
            $submission->refresh();
            $submission->update([
                'email_status' => KycDocumentSubmission::EMAIL_FAILED,
                'email_error' => $e->getMessage(),
            ]);
        }
    }
}
