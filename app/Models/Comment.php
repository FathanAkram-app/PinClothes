<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment',
    ];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
