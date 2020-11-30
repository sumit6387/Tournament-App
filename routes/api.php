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
    Route::get('/check',[LoginController::class , 'check']);
    Route::post('/verifyotp',[LoginController::class , 'verifyOtp']);
    Route::group(['middleware' => 'auth:sanctum','api'], function(){
        Route::get('/user', [UserController::class , 'user']);
        Route::post('/updatedata',[UserController::class , 'store']);
        Route::post('/apply_ref_code',[UserController::class,'applyRefCode'])
        ->middleware('CheckRefCode');  
        Route::post('/joinTournament' , [UserController::class , 'joinTournament']);
        Route::post('/addTournament' , [UserController::class , 'createUserTournament'])->middleware('CheckTournament');
        Route::post('/updatePassword' , [UserController::class , 'updatePassword']);
        Route::get('/claimPrize',[UserController::class , 'claimPrize']);
        Route::post('/changePassword' , [UserController::class , 'changePassword']);
        Route::post('/forgetPasswordProcess',[UserController::class , 'forgetPassword']);
        Route::post('/forgetPassword' , [LoginController::class , 'resendotp']);
        Route::post('/cancelMatch' , [UserController::class , 'cancelMatch']);  
        
        // payment route for join tournament
        Route::post('/payment-request' , [PaymentController::class , 'createPaymentOrder']);
        Route::post('/payment-complete', [PaymentController::class , 'paymentComplete']);


        // payment route for membership
        Route::post('/payment-request-membership' , [PaymentController::class , 'createMembershipOrder']);
        Route::post('/payment-complete-membership', [PaymentController::class , 'paymentCompleteMembership']);

        // show data route
        Route::get('/showtournament' , [ShowController::class , 'showTournaments']);
        Route::get('/pointTableUser' , [ShowController::class , 'pointTableUser']);
        Route::get('/mywallet' , [ShowController::class , 'myWallet']);
        Route::get('/allTransactions' , [ShowController::class , 'allTransactions']);
        Route::get('/allPoint' , [ShowController::class , 'allPoint']);
        Route::get('/referAndEarn' , [ShowController::class , 'referAndEarn']);
        Route::get('/ourTournament' , [ShowController::class , 'ourTournament']);
        Route::post('/tournamentDetail' , [ShowController::class , 'tournamentDetail']);
    });
    Route::fallback(function(){
        return response()->json([
            'status' => false,
            'msg' => 'Route Not Found'
        ]);
    });
    
});