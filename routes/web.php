<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DebitController;
use App\Http\Controllers\RegisterController;

Route::get('/user', UserController::class)
     ->middleware('auth:sanctum')
     ->name('user');

Route::post('/user', RegisterController::class)
     ->middleware('guest:sanctum')
     ->name('register');

Route::post('/login', LoginController::class)
     ->middleware('guest:sanctum')
     ->name('login');

Route::post('/debit', DebitController::class)
     ->middleware('auth:sanctum')
     ->name('debit');
