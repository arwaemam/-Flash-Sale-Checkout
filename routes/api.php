<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckGenderMiddleware;

Route::get('/users', [UserController::class, 'index'])
    ->middleware(CheckGenderMiddleware::class);
