<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/emailverification/{id}', function ($id) {
    $verify = User::where('id',$id)->first();
    if ($verify->email_verified_at){
        return view('failedverif');
    }else{
        $verify->email_verified_at = Carbon::now()->timestamp;
        $verify->save();
        return view('verification');
    }   
    
    
    
});
