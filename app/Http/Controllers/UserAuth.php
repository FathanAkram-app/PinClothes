<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAuth extends Controller
{
    // Register User
    public function register(Request $request)
    {
        $user = $request->validate([
            'email' => 'required|unique:users|max:254',
            'username' => 'required|max:32|min:3',
            'password' => 'required|min:6|max:32',
            'gender' => 'required'
        ]);
        
        if ($user) {
            
            $objUser = new User($user);
            $objUser->password = Hash::make($objUser->password);
            $objUser->save();
            return response()->json([
                "status"=>"success",
                "user"=>$objUser
            ],200);
            
        }else{
            return response()->json([
                "status"=>"failed"
            ],200);
        } 
    }

    // Login User
    public function login(Request $request)
    {
        $user = User::where("email",$request->email)->first();
        
        if ($user&&Hash::check($request->password,$user->password)) {
            $user->remember_token = Str::random(60);
            $user->save();
            return response()->json([
                "status"=>"success"
            ],200);
        }
        return response()->json([
            "status"=>"failed",
            "msg"=>"Email/Password is wrong"
        ],200);
    }
}
