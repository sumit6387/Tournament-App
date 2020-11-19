<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShowController;

Route::group(['prefix' => 'v1','middleware' => 'CheckVersion'],function(){
    Route::post('/register',[LoginController::class , 'register']);
    Route::post('/login',[LoginController::class , 'login']);
    Route::post('/resendotp' , [LoginController::class , 'resendotp']);
    Route::post('/verifyotp',[LoginController::class , 'verifyOtp']);
    Route::group(['middleware' => 'auth:sanctum','api'], function(){
        Route::get('/user', [UserController::class , 'user']);
        Route::post('/updatedata',[UserController::class , 'store']);
        Route::post('/apply_ref_code',[UserController::class,'applyRefCode'])
        ->middleware('CheckRefCode');  
        Route::post('/joinTournament' , [UserController::class , 'joinTournament']);
        Route::post('/addTournament' , [UserController::class , 'createUserTournament']);
        Route::post('/updatePassword' , [UserController::class , 'updatePassword']);
        Route::get('/claimPrize',[UserController::class , 'claimPrize']);
        
        // payment route
        Route::post('/payment-request' , [PaymentController::class , 'createPaymentOrder']);
        Route::post('/payment-complete', [PaymentController::class , 'paymentComplete']);

        // show data route
        Route::get('/showtournament' , [ShowController::class , 'showTournaments']);
    });
    Route::fallback(function(){
        return response()->json([
            'status' => false,
            'msg' => 'Route Not Found'
        ]);
    });
    
});