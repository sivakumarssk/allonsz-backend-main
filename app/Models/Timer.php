<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\CircleReward;
use App\Models\Member;

class Timer extends Model
{
    use HasFactory;
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    public function getStartedAtAttribute($value)
    {
        return Carbon::parse($value);
    }
}
