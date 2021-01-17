<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

Route::get('/', [WebController::class , "index"]);
Route::post('/contact' , [WebController::class , 'contact']);
Route::post('/newsletter' , [WebController::class , "newsletter"]);