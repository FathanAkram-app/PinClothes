<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// KALAU KAMU NEMUIN TULISAN INI DENGARKAN SAYA, "PHP ITU JELEK SEKALI!!!!"

// User Authentication endpoint
Route::post('/register', 'App\Http\Controllers\UserAuth@register');
Route::post('/login', 'App\Http\Controllers\UserAuth@login');

// Attraction endpoint
Route::post('/addattractions', 'App\Http\Controllers\AttractionController@addAttractions');
