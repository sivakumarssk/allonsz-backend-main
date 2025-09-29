<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory,SoftDeletes;
    
    public function state()
    {
        return $this->belongsTo('App\Models\State')->withTrashed();
    }
    
    public function mandals()
    {
        return $this->hasMany('App\Models\Mandal')->withTrashed();
    }
    
    public function users()
    {
        return $this->hasMany('App\Models\User')->withTrashed();
    }
}
