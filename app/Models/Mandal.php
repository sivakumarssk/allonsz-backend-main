<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mandal extends Model
{
    use HasFactory,SoftDeletes;
    
    public function district()
    {
        return $this->belongsTo('App\Models\District')->withTrashed();
    }
    
    public function state()
    {
        return $this->belongsTo('App\Models\State')->withTrashed();
    }
    
    public function users()
    {
        return $this->hasMany('App\Models\User')->withTrashed();
    }
}
