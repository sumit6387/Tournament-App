<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\AdminShowController;

Route::group(['middleware' => 'Cors'] , function(){
    Route::post('/login' ,[AdminController::class , 'login']);
    Route::post('/register' ,[AdminController::class , 'register']);
    Route::post('/login' , [AdminController::class , 'login']);
    Route::post('/verifyotp',[AdminController::class , 'verifyOtp']);
    Route::post('/resendotp' , [AdminController::class , 'resendOtp']);

    // Route::group(['middleware' => 'auth:sanctum' , 'api'] , function(){
    //     Route::group(['middleware' => 'CheckAdmin'] , function(){
            Route::get('/user',[AdminController::class , 'user']);
            Route::post('/addAnnouncement' , [MainController::class , 'addAnnouncement']);
            Route::post('/addVersion' , [MainController::class , 'addVersion']);
            Route::post('/addGame' , [MainController::class , 'addGame']);
            Route::post('/addMembership' , [MainController::class , 'addMembership']);
            Route::post('/addTournament' , [MainController::class , 'addTournament']);
            Route::post('/updateIdPassword' , [MainController::class , 'updateIdPassword']);
            Route::post('/UpdateTournamentComplete' , [MainController::class ,'UpdateTournamentComplete']);
            Route::post('/sendnotification' , [MainController::class , 'sendnotification']);
            Route::post('/updateAnnouncement' , [MainController::class , 'updateAnnouncement']);
            Route::post('/updateVersion' , [MainController::class , 'updateVersion']);



            // show details to admin
            Route::get('/showAnnouncement',[AdminShowController::class , 'showAnnouncement']);
            Route::get('/showTournaments',[AdminShowController::class , 'showTournaments']);
            Route::get('/withdraw',[AdminShowController::class , 'withdraw']);
            Route::get('/versions',[AdminShowController::class , 'versions']);
            Route::get('/showTournamentsUser' , [AdminShowController::class , 'showTournamentsUser']);
            Route::get('/complete/{id}', [AdminShowController::class , 'complete']);

            Route::get('/delete_tournament/{tournament_id}',[MainController::class , 'deleteTournament']);
            Route::get('/delete_announcement/{ann_id}' , [MainController::class , 'delete_announcement']);
            Route::get('/index',[AdminShowController::class , 'indexData']);
            Route::get('/editAnnouncement/{id}' , [AdminShowController::class , 'editAnnouncement']);
            Route::get('/editVersion/{v_id}' , [AdminShowController::class , 'editVersion']);
            Route::get('/delete_version/{id}' , [MainController::class , 'delete_version']);

    //     });
    // });

    Route::fallback(function(){
        return response()->json([
            'status' => false,
            'msg' => 'Route Not Found'
        ]);
    });
});
