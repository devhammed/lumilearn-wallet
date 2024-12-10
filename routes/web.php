<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post('/user', [UserController::class, 'register'])
     ->middleware('guest:sanctum')
     ->name('users.register');

Route::post('/login', [UserController::class, 'login'])
     ->middleware('guest:sanctum')
     ->name('users.login');

Route::get('/user', [UserController::class, 'user'])
     ->middleware('auth:sanctum')
     ->name('users.user');
