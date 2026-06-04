<?php

namespace App\Console\Commands;

use App\Jobs\SendKycSubmissionEmailJob;
use App\Models\KycDocumentSubmission;
use App\Services\KycSubmissionMailService;
use Illuminate\Console\Command;

class RetryFailedKycSubmissions extends Command
{
    protected $signature = 'kyc:retry-failed-emails';

    protected $description = 'Retry KYC document notification emails that failed (max 5 failures per submission)';

    public function handle(KycSubmissionMailService $mailer): int
    {
        $stuckPending = KycDocumentSubmission::query()
            ->where('email_status', KycDocumentSubmission::EMAIL_PENDING)
            ->where('created_at', '<', now()->subMinutes(10))
            ->get();

        foreach ($stuckPending as $submission) {
            (new SendKycSubmissionEmailJob($submission->id))->handle($mailer);
        }

        $submissions = KycDocumentSubmission::query()
            ->where('email_status', KycDocumentSubmission::EMAIL_FAILED)
            ->where('email_retry_count', '<', 5)
            ->get();

        foreach ($submissions as $submission) {
            (new SendKycSubmissionEmailJob($submission->id))->handle($mailer);
        }

        return 0;
    }
}
