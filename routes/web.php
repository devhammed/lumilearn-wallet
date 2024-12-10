<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserRegisterController;

Route::post('/user', UserRegisterController::class)
     ->middleware('guest:sanctum')
     ->name('users.register');

Route::post('/login', UserLoginController::class)
     ->middleware('guest:sanctum')
     ->name('users.login');

Route::get('/user', UserProfileController::class)
     ->middleware('auth:sanctum')
     ->name('users.profile');
