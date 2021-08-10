<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpvotesDownvotes extends Model
{
    protected $fillable = [
        'upvoted',
        'users_id',
        'post_id'
    ];
    use HasFactory;
}
