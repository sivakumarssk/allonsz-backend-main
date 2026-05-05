<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralHistory extends Model
{
    protected $fillable = [
        'user_id', 'old_referal_id', 'old_referal_code',
        'new_referal_id', 'new_referal_code', 'changed_by', 'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function old_referal()
    {
        return $this->belongsTo(User::class, 'old_referal_id');
    }

    public function new_referal()
    {
        return $this->belongsTo(User::class, 'new_referal_id');
    }

    public function changed_by_admin()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
