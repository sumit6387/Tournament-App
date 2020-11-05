<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix' => 'v1'],function(){
        Route::post('/register',[LoginController::class , 'register']);
        Route::post('/login',[LoginController::class , 'login']);
        Route::post('/resendotp' , [LoginController::class , 'resendotp']);
        Route::post('/verifyotp',[LoginController::class , 'verifyOtp']);


        Route::group(['middleware' => 'auth:sanctum'], function(){
            Route::get('/user',[UserController::class , 'user']);
            Route::post('/uploadImage',[UserController::class , 'store']);
            Route::post('/apply_ref_code',[UserController::class,'applyRefCode'])->middleware('CheckRefCode');  
        });
});


