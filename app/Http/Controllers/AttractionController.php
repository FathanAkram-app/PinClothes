<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attraction;
use App\Models\User;
use App\Models\AttractionsUsers;
use Illuminate\Support\Str;

class AttractionController extends Controller
{
    public function addAttractions(Request $request)
    {

        $user = User::where('remember_token',$request->bearerToken())->first();
        if ($user) {
            foreach ($request->attractions as $attraction) {
                $objAttraction = new Attraction();
                $objAttractionsUsers = new AttractionsUsers();
                $objAttraction->attractions = Str::lower($attraction);
                $findAttraction = Attraction::where('attractions',$objAttraction->attractions)->first();
                if ($findAttraction) {
                    $objAttractionsUsers->attractions_id = $findAttraction->id;
                }else{
                    $objAttraction->save();
                    $objAttractionsUsers->attractions_id = $objAttraction->id;
                    
                }
                
                $findAttractionsUsers = AttractionsUsers::where('users_id',$user->id)
                    ->where('attractions_id',$objAttractionsUsers->attractions_id)
                    ->first();
                if ($findAttractionsUsers==null) {
                    $objAttractionsUsers->users_id = $user->id;
                    $objAttractionsUsers->attraction_count = 1;
                    $objAttractionsUsers->save();
                }
                
            }
            
            
            return response()->json([
                "status"=>200,
                "message"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>401,
            "message"=>"please login first"
        ],401);
        
    }

    public function deleteAttraction(Request $request)
    {
        $user = User::where('remember_token', $request->bearerToken())->first();
        if ($user){
            AttractionsUsers::
                where('users_id', $user->id)
                ->where('attractions_id', $request->attraction_id)->first()->delete();
            return response()->json([
                "status"=>200,
                "message"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>401,
            "message"=>"please login first"
        ],401);
        
    }

    
}
