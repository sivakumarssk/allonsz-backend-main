<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphMailService
{
    public function isConfigured(): bool
    {
        return (bool) config('kyc.graph_tenant_id')
            && (bool) config('kyc.graph_client_id')
            && (bool) config('kyc.graph_client_secret');
    }

    public function getAccessToken(): string
    {
        $tenant = config('kyc.graph_tenant_id');

        return Cache::remember('kyc_ms_graph_mail_token', 3500, function () use ($tenant) {
            $response = Http::asForm()->timeout(30)->post(
                "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token",
                [
                    'client_id' => config('kyc.graph_client_id'),
                    'client_secret' => config('kyc.graph_client_secret'),
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ]
            );

            if (!$response->successful()) {
                Log::warning('KYC Graph token request failed', [
                    'status' => $response->status(),
                ]);
                throw new \RuntimeException('Microsoft Graph token request failed');
            }

            $data = $response->json();
            if (empty($data['access_token'])) {
                throw new \RuntimeException('Microsoft Graph token missing in response');
            }

            return $data['access_token'];
        });
    }

    /**
     * @param  array<int, array{name: string, contentType: string, contentBytes: string}>  $attachments
     */
    public function sendMail(
        string $subject,
        string $bodyText,
        string $bodyHtml,
        string $toAddress,
        ?string $replyTo,
        array $attachments
    ): void {
        $token = $this->getAccessToken();
        $mailbox = rawurlencode(config('kyc.graph_sender_mailbox'));

        $message = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $bodyHtml,
            ],
            'toRecipients' => [
                ['emailAddress' => ['address' => $toAddress]],
            ],
            'attachments' => $attachments,
        ];

        if ($replyTo) {
            $message['replyTo'] = [
                ['emailAddress' => ['address' => $replyTo]],
            ];
        }

        $payload = [
            'message' => $message,
            'saveToSentItems' => true,
        ];

        $response = Http::withToken($token)
            ->timeout(120)
            ->acceptJson()
            ->post("https://graph.microsoft.com/v1.0/users/{$mailbox}/sendMail", $payload);

        if (!$response->successful()) {
            Log::warning('KYC Graph sendMail failed', [
                'status' => $response->status(),
            ]);
            throw new \RuntimeException('Microsoft Graph sendMail failed');
        }
    }
}
