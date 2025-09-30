<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    
    
    public function getLogoAttribute($value)
    {
        return asset('images/bussiness/'.$value);
    }
    
    public function getLogoFilenameAttribute()
    {
        return $this->attributes['logo'] ?? null;
    }
    
    public function getFaviconAttribute($value)
    {
        return asset('images/bussiness/'.$value);
    }
    
    public function getFaviconFilenameAttribute()
    {
        return $this->attributes['favicon'] ?? null;
    }
    
    public function getAddUrlAttribute($value)
    {
        return asset($value);
    }

}
