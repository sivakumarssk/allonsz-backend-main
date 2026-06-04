<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycDocumentSubmission extends Model
{
    public const EMAIL_PENDING = 'pending';
    public const EMAIL_SENT = 'sent';
    public const EMAIL_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'file_paths',
        'submitted_at',
        'email_status',
        'email_message_id',
        'email_error',
        'email_retry_count',
    ];

    protected $casts = [
        'file_paths' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
