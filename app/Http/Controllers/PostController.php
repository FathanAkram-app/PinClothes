<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Attraction;
use App\Models\AttractionsPosts;
use Illuminate\Support\Str;


class PostController extends Controller
{
    public function getPosts()
    {
        $posts = Post::latest('created_at')->take(5)->get();

        return response()->json([
            "status"=>"success",
            "comments"=>$posts
        ],200);
    }

    public function getComments(Request $request)
    {
        $comments = Comment::where("post_id",$request->post_id)->get();

        return response()->json([
            "status"=>"success",
            "comments"=>$comments
        ],200);
    }
    

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
            
            foreach ($request->attractions as $attraction) {
                $objAttraction = new Attraction();
                $objAttractionsPosts = new AttractionsPosts();
                $objAttraction->attractions = Str::lower($attraction);
                $findAttraction = Attraction::where('attractions',$objAttraction->attractions)->first();
                if ($findAttraction) {
                    $objAttractionsPosts->attractions_id = $findAttraction->id;
                }else{
                    $objAttraction->save();
                    $objAttractionsPosts->attractions_id = $objAttraction->id;
                    
                }
                $findAttractionsPosts = AttractionsPosts::where('post_id',$post->id)
                    ->where('attractions_id',$objAttractionsPosts->attractions_id)
                    ->first();
                if ($findAttractionsPosts==null) {
                    $objAttractionsPosts->post_id = $user->id;
                    $objAttractionsPosts->save();
                }
                
            }
            
            return response()->json([
                "status"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>"failed",
            "message"=>"Please login first"
        ],200);
        
    }

    public function addComment(Request $request){
        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($user) {
            if (Post::where('id', $request->post_id)->first()) {
                $comment = new Comment();
                $comment->comment = $request->comment;
                $comment->user_id = $user->id;
                $comment->post_id = $request->post_id;
                $comment->save();
                return response()->json([
                    "status"=>"success"
                ],200);
            }
            return response()->json([
                "status"=>"failed",
                "message"=>"Post not found"
            ],200);
        }
        return response()->json([
            "status"=>"failed",
            "message"=>"Please login first"
        ],200);
    }
}
