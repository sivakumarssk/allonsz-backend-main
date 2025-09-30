<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use App\Models\CircleReward;
use App\Models\Member;

class Circle extends Model
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
        return $this->hasMany('App\Models\Member')->with('user:id,username,referal_id,referal_code');
    }
    
    public function circle_member($position_id)
    {
        $member = Member::where('circle_id',$this->id)->where('position',$position_id)->first();
        return $member;
    }
    
    public function rewards()
    {
        return $this->hasMany('App\Models\CircleReward');
    }
}
