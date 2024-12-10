<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PicsController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::resource('movies', MovieController::class)->only([
    'store'
]);

Route::resource('pics', PicsController::class)->only([
    'store'
]);
