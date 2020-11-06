<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register',[LoginController::class , 'register']);
Route::post('/login',[LoginController::class , 'login']);
Route::post('/resendotp' , [LoginController::class , 'resendotp']);

Route::group(['middleware' => 'auth:sanctum','prefix' => 'v1'], function(){
    Route::get('/user',[LoginController::class , 'user']);
});
