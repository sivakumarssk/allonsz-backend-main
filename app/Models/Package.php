<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory,SoftDeletes;
    
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }
    
    public function circles()
    {
        return $this->hasMany('App\Models\Circle')->where('user_id',\Auth::User()->id);
    }
    
    public function rewards()
    {
        return $this->hasMany('App\Models\Reward');
    }
    
    public function colors()
    {
        return $this->hasMany('App\Models\Color');
    }
}
