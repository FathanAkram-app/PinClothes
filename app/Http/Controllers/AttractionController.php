<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attraction;
use App\Models\User;
use Illuminate\Support\Str;

class AttractionController extends Controller
{
    public function addAttractions(Request $request)
    {

        $userId = User::where('remember_token',$request->bearerToken())->first()->id;
        $objAttractions = array();
        foreach ($request->attractions as $attraction) {
            $objAttraction = new Attraction();
            $objAttraction->attractions = Str::lower($attraction);
            if (Attraction::where('attractions',$at->attractions)->first() == null) {
                $objAttraction->save();
            }
            array_push($objAttractions, $at);
        }
        foreach ($objAttractions as $a) {
            $a->users()->attach($userId);
        }
        
        return response()->json([
            "status"=>"success"
        ],200);
    }
}
