<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Attraction;
use App\Models\AttractionsPosts;
use App\Models\UpvotesDownvotes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    public function getPosts()
    {
        $posts = Post::latest('created_at')->take(5)->get();
        $result = array();
        
        foreach ($posts as $post) {
            $upvotes = UpvotesDownvotes::where('post_id',$post->id)->get();
            $post->upvotes = $upvotes;
            $post->img_path = Storage::url($post->img_path);
            array_push($result,$post);
        }
        return response()->json([
            "status"=>"success",
            "result"=>$result
        ],200);
    }

    public function getComments(Request $request)
    {
        $comments = Comment::where("post_id",$request->post_id)->get();

        return response()->json([
            "status"=>"success",
            "result"=>$comments
        ],200);
    }
    

    public function addPost(Request $request)
    {
        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($user) {
            $post = new Post();
            $img = $request->img->storeAs('public',Carbon::now()->timestamp.'.jpg');
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
                    $objAttractionsPosts->post_id = $post->id;
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

    public function deletePost(Request $request)
    {
        $post = Post::where('id',$request->post_id)->first();
        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($post->user_id==$user->id) {
            foreach (AttractionsPosts::where('post_id',$request->post_id)->get() as $post) {
                $post->delete();
            } 
            foreach (Comment::where('post_id',$request->post_id)->get() as $comment) {
                $comment->delete();
            }
            $post->delete();
            return response()->json([
                "status"=>"success",
                "message"=>"deleted successfully"
            ],200);
        }
        return response()->json([
            "status"=>"failed",
            "message"=>"this post is not yours"
        ],200);
    }

    public function setUpvotesDownvotes(Request $request)
    {
        $post = Post::where('id',$request->post_id)->first();
        $user = User::where('remember_token', $request->bearerToken())->first();
        if ($user && $post) {
            $findUD = UpvotesDownvotes::where('post_id',$request->post_id)->where('user_id',$user->id)->first();
            if ($findUD) {
                $findUD->update(['upvoted' => $request->upvotes]);
            }else{
                $ud = new UpvotesDownvotes();
                $ud->upvoted = $request->upvotes;
                $ud->user_id = $user->id;
                $ud->post_id = $post->id;
                $ud->save();
            }
            return response()->json([
                "status"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>"failed",
            "message"=>"please login first / post not found"
        ],200);
    }

    public function unvote(Request $request)
    {
        $post = Post::where('id',$request->post_id)->first();
        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($user && $post) {
            $findUD = UpvotesDownvotes::where('post_id',$request->post_id)->where('user_id',$user->id)->first();
            if ($findUD) {
                $findUD->delete();
                return response()->json([
                    "status"=>"success"
                ],200);
            }
            return response()->json([
                "status"=>"failed",
                "message"=>"you already unvoted this post"
            ],200);

        }
        return response()->json([
            "status"=>"failed",
            "message"=>"please login first / post not found"
        ],200);
        
    }
}
