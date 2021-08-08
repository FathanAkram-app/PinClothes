<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attraction extends Model
{
    use HasFactory;
    protected $fillable = [
        'attractions',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class,'attractions_users','attractions_id','users_id');
    }
    
}
