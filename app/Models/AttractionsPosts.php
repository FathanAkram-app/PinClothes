<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttractionsPosts extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'attractions_id',
        'post_id'
    ];
}
