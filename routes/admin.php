<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;

Route::post('/login' ,[AdminController::class , 'login']);
Route::post('/register' ,[AdminController::class , 'register']);
Route::post('/login' , [AdminController::class , 'login']);
Route::post('/verifyotp',[AdminController::class , 'verifyOtp']);
Route::post('/resendotp' , [AdminController::class , 'resendOtp']);

Route::group(['middleware' => 'auth:sanctum'] , function(){
    Route::get('/user',[AdminController::class , 'user']);
});

