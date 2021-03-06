<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// KALAU KAMU NEMUIN TULISAN INI DENGARKAN SAYA, "PHP ITU JELEK SEKALI!!!!"

// User Authentication endpoint
Route::post('/register', 'App\Http\Controllers\UserAuth@register');
Route::post('/login', 'App\Http\Controllers\UserAuth@login');

// Attraction endpoint
Route::post('/addattractions', 'App\Http\Controllers\AttractionController@addAttractions');
Route::post('/deleteattraction', 'App\Http\Controllers\AttractionController@deleteAttraction');

// Posts endpoint
Route::get('/getcomments', 'App\Http\Controllers\PostController@getComments');
Route::get('/getposts', 'App\Http\Controllers\PostController@getPosts');
Route::get('/getpostssearch', 'App\Http\Controllers\PostController@getPostsSearch');
Route::post('/addpost', 'App\Http\Controllers\PostController@addPost');
Route::post('/addcomment', 'App\Http\Controllers\PostController@addComment');
Route::post('/deletepost', 'App\Http\Controllers\PostController@deletePost');
Route::post('/setupvotedownvote', 'App\Http\Controllers\PostController@setUpvotesDownvotes');
Route::post('/unvote', 'App\Http\Controllers\PostController@unvote');





