<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use HasFactory,SoftDeletes;
    
    public function getPhotoAttribute($value)
    {
        return asset('images/toures/'.$value);
    }
    
    public function getPhotoFilenameAttribute()
    {
        return $this->attributes['photo'] ?? null;
    }
    
    public function trips()
    {
        return $this->hasMany('\App\Models\Trip');
    }
    
    public function photos()
    {
        return $this->hasMany('\App\Models\Photo');
    }
    
}
