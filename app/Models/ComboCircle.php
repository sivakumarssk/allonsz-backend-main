<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboCircle extends Model
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

    public function members()
    {
        return $this->hasMany('App\Models\ComboMember')->with('user:id,username,referal_id,referal_code');
    }

    public function rewards()
    {
        return $this->hasMany('App\Models\ComboCircleReward');
    }
}
