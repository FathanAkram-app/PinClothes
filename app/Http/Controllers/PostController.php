<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;


class PostController extends Controller
{
    public function addPost(Request $request)
    {
        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($user) {
            $post = new Post();
            $img = $request->img->store('images');
            $post->img_path = $img;
            $post->caption = $request->caption;
            $post->user_id = $user->id;
            $post->save();
            return response()->json([
                "status"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>"failed",
            "message"=>"Please login first"
        ],200);
        
    }
}
