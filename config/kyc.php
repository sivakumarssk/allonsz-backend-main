<?php

return [

    'internal_mailbox' => env('KYC_INTERNAL_MAILBOX', 'info@allons-z.com'),

    'duplicate_submission_window_minutes' => (int) env('KYC_DUPLICATE_WINDOW_MINUTES', 10),

    /** Microsoft Graph (application permissions, client credentials) */
    'graph_tenant_id' => env('MS_GRAPH_TENANT_ID'),
    'graph_client_id' => env('MS_GRAPH_CLIENT_ID'),
    'graph_client_secret' => env('MS_GRAPH_CLIENT_SECRET'),

    /** Mailbox user principal name used in Graph sendMail URL */
    'graph_sender_mailbox' => env('MS_GRAPH_SENDER_MAILBOX', env('KYC_INTERNAL_MAILBOX', 'info@allons-z.com')),

    /**
     * Graph simple sendMail attachment payload limit; above this use SMTP.
     */
    'graph_max_attachment_bytes' => (int) env('KYC_GRAPH_MAX_ATTACHMENT_BYTES', 4 * 1024 * 1024),

    'max_file_bytes' => (int) env('KYC_MAX_FILE_BYTES', 5 * 1024 * 1024),

    'max_total_bytes' => (int) env('KYC_MAX_TOTAL_BYTES', 20 * 1024 * 1024),

];
