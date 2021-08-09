<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttractionsUsers extends Model
{
    use HasFactory;
    protected $fillable = [
        'attractions_id',
        'users_id'
    ];
}
