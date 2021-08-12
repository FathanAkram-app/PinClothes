<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Attraction;
use App\Models\AttractionsPosts;
use App\Models\UpvotesDownvotes;
use App\Models\AttractionsUsers;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function getPosts(Request $request)
    {
        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($request->sort == "recently"){
            $posts = Post::latest('created_at')->paginate(5);
        } else if($request->sort == "foryou"&& $request->bearerToken()){
            $attractions_id = AttractionsUsers::
                where(
                    'users_id',
                    $user->id
                )
                ->orderBy('attraction_count','asc')
                ->get()->pluck('attractions_id');
            $post_id = AttractionsPosts::distinct('post_id')
                ->whereIn('attractions_id',$attractions_id)
                ->get()->pluck('post_id');
            $posts = Post::whereIn('id', $post_id)->latest('created_at')->paginate(5);

        }else if($request->sort == "trending"){
            $posts = Post::max('created_at')->paginate(5);

        }else if($request->sort == "mypost"){
            $posts = Post::where('user_id', $user->id)->get();

        }else if($request->sort == "search"){
            $attraction = AttractionsPosts::
                where(
                    'attractions_id',
                    Attraction::where("attractions", "LIKE" ,"%{$request->search}%")->get()->pluck('id')
                )->get()->pluck('post_id');
            $posts = Post::
                whereIn('id', $attraction)
                ->orWhere("caption", "LIKE", "%{$request->search}%")
                ->paginate(5);
        }
        
        $result = array();

        
        foreach ($posts as $post) {
            $upvotes = UpvotesDownvotes::where('post_id',$post->id)->get();
            $post->upvotes = $upvotes;
            $post->img_path = Storage::url($post->img_path);
            array_push($result,$post);
        }
        return response()->json([
            "status"=>200,
            "message"=>"success",
            "result"=>$result
        ],200);
    }

    public function getComments(Request $request)
    {
        $comments = Comment::where("post_id",$request->post_id)->get();

        return response()->json([
            "status"=>200,
            "message"=>"success",
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
                "status"=>200,
                "message"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>401,
            "message"=>"Please login first"
        ],401);
        
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
        if ($user&&$post->user_id==$user->id) {
            if (Storage::exists($post->img_path)){
                Storage::delete($post->img_path);
            }
             
            foreach (AttractionsPosts::where('post_id',$request->post_id)->get() as $post) {
                $post->delete();
            } 
            foreach (Comment::where('post_id',$request->post_id)->get() as $comment) {
                $comment->delete();
            }
            $post->delete();
            return response()->json([
                "status"=>200,
                "message"=>"deleted successfully"
            ],200);
        }
        return response()->json([
            "status"=>400,
            "message"=>"this post is not yours"
        ],400);
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
                $ud->post_id = $request->post_id;
                $ud->save();
            }

            $findAP = AttractionsPosts::where('post_id',$post->id)->get();
            foreach($findAP as $ap){
                $findAU = AttractionsUsers::
                    where('attractions_id', $ap->attractions_id)
                    ->where('users_id',$user->id)
                    ->first();
                if ($findAU){
                    if ($request->upvotes){
                        $findAU->attraction_count += 1;
                    }else{
                        $findAU->attraction_count -= 1;
                    }
                    
                }else{
                    $findAU = new AttractionsUsers();
                    $findAU->users_id = $user->id;
                    $findAU->attractions_id = $ap->attractions_id;
                    if ($request->upvotes){
                        $findAU->attraction_count = 2;
                    }else{
                        $findAU->attraction_count = 1;
                    }

                }
                $findAU->save();
                
            }
            
            
            
            
            return response()->json([
                "status"=>200,
                "message"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>400,
            "message"=>"please login first / post not found"
        ],400);
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
                    "status"=>200,
                    "message"=>"success"
                ],200);
            }
            return response()->json([
                "status"=>400,
                "message"=>"you already unvoted this post"
            ],400);

        }
        return response()->json([
            "status"=>400,
            "message"=>"please login first / post not found"
        ],400);
        
    }
}
