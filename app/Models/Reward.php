<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Member;

class Reward extends Model
{
    use HasFactory;
    
    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }
    
}
