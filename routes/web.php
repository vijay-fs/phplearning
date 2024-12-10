<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PicsController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/browse_movies/', [MovieController::class, 'show']);

Route::get('/browse_pics/', [PicsController::class, 'show']);