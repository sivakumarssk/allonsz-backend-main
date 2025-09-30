<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasFactory,SoftDeletes;
    
    public function country()
    {
        return $this->belongsTo('App\Models\Country')->withTrashed();
    }
    
    public function districts()
    {
        return $this->hasMany('App\Models\District')->withTrashed();
    }
    
    public function users()
    {
        return $this->hasMany('App\Models\User')->withTrashed();
    }
}
