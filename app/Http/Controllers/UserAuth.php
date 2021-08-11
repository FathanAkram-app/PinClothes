<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\URL;

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
            // curl \
            //     --header "Content-Type" :"application/json"\
            //     --header "Authorization: Basic ".base64_encode($objUser->email)\
            //     --request GET \
            //     https://email.ocatelkom.co.id/api/v1
            $headers = [
                'Authorization'=>'Basic ZmF0aGFuLmEuZGV2QGdtYWlsLmNvbTp6b280XUFLQQ==',
                'Content-Type'=>'application/json'
            ];
            $objUser->save();

            $body = array(
                "sender_name"=>"PinClothes",
                "subject"=>"PinClothes Verification",
                "sender_email"=>"skywalker@dataserver.id",
                "message"=>base64_encode('<a href="'.URL::to('/'.'emailverification/'.$objUser->id).'">Click here to verify your pinclothes account</a>'),
                "email"=>$objUser->email  
            );
               
            
            $client = new GuzzleClient([
                'headers'=>$headers,
                'form_params'=>$body
            ]);
            
            $r = $client->request('POST', 'https://email.ocatelkom.co.id/api/v1/send-single');
            // $response = $r->getBody()->getContents();
            
            return response()->json([
                "status"=>200,
                "message"=>"success",
                "result"=>$objUser
            ],200);
            
        }else{
            return response()->json([
                "status"=>400,
                "message"=>"failed"
            ],200);
        } 
    }

    // Login User
    public function login(Request $request)
    {
        $user = User::where("email",$request->email)->first();
        
        if ($user&&Hash::check($request->password,$user->password)) {
            if ($user->email_verified_at){
                $user->remember_token = Str::random(60);
                $user->save();
                return response()->json([
                    "status"=>"success",
                    "result"=>$user,
                    "token"=>$user->remember_token
                ],200);
            }
            return response()->json([
                "status"=>401,
                "message"=>"you have not verify your account, please check your email"
            ],401);
            
        }
        return response()->json([
            "status"=>401,
            "message"=>"Email/Password is wrong"
        ],401);
    }
}
