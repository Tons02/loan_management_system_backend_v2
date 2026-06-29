<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// User Controller
Route::get('user', [UserController::class, 'index']);
Route::get('/profile-picture/{path}', [UserController::class, 'viewProfilePicture'])
    ->name('profile-picture.view');
Route::post('user', [UserController::class, 'store']);
Route::patch('user/{user}', [UserController::class, 'update']);
Route::put('user-archived/{user}', [UserController::class, 'archived']);
