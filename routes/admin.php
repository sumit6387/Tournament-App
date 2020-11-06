<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::post('/login' ,[AdminController::class , 'login']);
Route::post('/register' ,[AdminController::class , 'register']);
Route::get('/email/{email}' ,[AdminController::class , 'email']);

