<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MainController;

Route::post('/login' ,[AdminController::class , 'login']);
Route::post('/register' ,[AdminController::class , 'register']);
Route::post('/login' , [AdminController::class , 'login']);
Route::post('/verifyotp',[AdminController::class , 'verifyOtp']);
Route::post('/resendotp' , [AdminController::class , 'resendOtp']);

Route::group(['middleware' => 'auth:sanctum' , 'api'] , function(){
    Route::group(['middleware' => 'CheckAdmin'] , function(){
        Route::get('/user',[AdminController::class , 'user']);
        Route::post('/addAnnouncement' , [MainController::class , 'addAnnouncement']);
        Route::post('/addVersion' , [MainController::class , 'addVersion']);
        Route::post('/addGame' , [MainController::class , 'addGame']);
        Route::post('/addMembership' , [MainController::class , 'addMembership']);
        Route::post('/addTournament' , [MainController::class , 'addTournament']);
        Route::post('/updateIdPassword' , [MainController::class , 'updateIdPassword']);
        Route::post('/UpdateTournamentComplete' , [MainController::class ,'UpdateTournamentComplete']);
    });
});

Route::fallback(function(){
    return response()->json([
        'status' => false,
        'msg' => 'Route Not Found'
    ]);
});