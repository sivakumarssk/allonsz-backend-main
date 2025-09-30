<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasFactory;
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    public function tour()
    {
        return $this->belongsTo('App\Models\Tour');
    }
    
    public function rewards()
    {
        return $this->hasMany('App\Models\CircleReward')->with('circle');
    }
    
    public function photos()
    {
        return $this->hasMany('\App\Models\Photo');
    }
    
}
