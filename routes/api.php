<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShowController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\CheckMembershipController;


Route::group(['prefix' => '{version}','middleware' => 'CheckVersion'],function(){
    Route::get('/CheckMembership' , [CheckMembershipController::class , 'CheckMembershipUser']);
    Route::post('/register',[LoginController::class , 'register']); //mobile_no,name, email,password,gender,ref_code
    Route::post('/login',[LoginController::class , 'login']); //mobile_no , password,notification_token
    Route::post('/verifyotp',[LoginController::class , 'verifyOtp']); // mobile_no , otp
    Route::post('/resendOtp' , [LoginController::class , 'resendOtp']); // mobile_no
    // Route::get('/check' , [LoginController::class , 'check']);
    Route::post('/forgetPasswordProcess',[UserController::class , 'forgetPassword']); //mobile_no,otp,password
    Route::post('/forgetPassword' , [LoginController::class , 'forgetOtp']); //mobile_no
    
    Route::group(['middleware' => 'auth:sanctum','api'], function(){
        Route::post('/updatedata',[UserController::class , 'store']); //name , image,email,state,country,mobike_no,gender
        Route::post('/joinTournament' , [UserController::class , 'joinTournament']); //tournament_id,pubg_username , pubg_userid
        Route::post('/addTournament' , [UserController::class , 'createUserTournament'])->middleware('CheckTournament'); //prize_pool,winning,per_kill,entry_feeentry_fee,type,map,tournament_name,img,max_user_participated,game_type,tournament_type,tournament_name,tournament_start_date,tournament_start_time
        Route::post('/startmatch' , [UserController::class , 'updatePassword']); //tournament_id,room_id,password    in this route we update games id and password realtime
        Route::get('/claimPrize',[UserController::class , 'claimPrize']);
        Route::post('/changePassword' , [UserController::class , 'changePassword']); // current_password,new_password , confirm_password
        Route::post('/cancelMatch' , [UserController::class , 'cancelMatch']);   //tournament_id
        Route::post('/UpdateTournamentComplete' , [UserController::class , 'UpdateTournamentComplete']);//tournament_id , results this is json{user_id: , kill:, winner:0 or 1}
        Route::post('/addFeedback' , [UserController::class , 'addFeedback']); //title,description


        // payment route for to add balance
        Route::post('/payment-request' , [PaymentController::class , 'createPaymentOrder']); //amount
        Route::post('/payment-complete', [PaymentController::class , 'paymentComplete']); //razorpay_payment_id,razorpay_order_id,razorpay_signature


        // payment route for membership
        Route::post('/payment-request-membership' , [PaymentController::class , 'createMembershipOrder']);
        Route::post('/payment-complete-membership', [PaymentController::class , 'paymentCompleteMembership']);//razorpay_payment_id,razorpay_order_id,razorpay_signature

        // withdraw amount
        Route::post('/withdraw',[WithdrawController::class , 'withdraw']); //mode , upi_id,paytm_no,acount_no,ifsc_code,name

        // show data route
        Route::get('/showtournament/{game}/{type}/{page}', [ShowController::class , 'showTournaments']);
        Route::get('/pointTableUser' , [ShowController::class , 'pointTableUser']);
        Route::get('/mywallet' , [ShowController::class , 'myWallet']);
        Route::get('/allTransactions' , [ShowController::class , 'allTransactions']);
        Route::get('/allPoint' , [ShowController::class , 'allPoint']);
        Route::get('/referAndEarn' , [ShowController::class , 'referAndEarn']);
        Route::get('/ourTournament' , [ShowController::class , 'ourTournament']);
        Route::post('/tournamentDetail' , [ShowController::class , 'tournamentDetail']); //tournament_id
        Route::post('/complaint' , [UserController::class , 'complaint']); // tournament_id , msg , img if importent then get

        Route::get('/user' , [ShowController::class , 'user']);
        Route::post('/search',[ShowController::class, 'search']);//id
        Route::get('/numberOfNotification' , [ShowController::class , 'numberOfNotification']);
        Route::get('/notifications' , [ShowController::class , 'notification']);
        Route::get('/updateSeen' , [ShowController::class , 'updateSeen']);
        Route::get('/usernames/{id}' , [ShowController::class , 'showUsername']); //tournament_id
        Route::get('/history/{game}/{type}',[ShowController::class , 'history']);//game,type ex live, past
        Route::get('/usernamesForCreator/{id}' , [ShowController::class , 'showUsernameForCreator']); //tournament_id
        //show username to organizer 
        Route::get('/check' , [LoginController::class , 'check']);
    });
    Route::fallback(function(){
        return response()->json([
            'status' => false,
            'msg' => 'Route Not Found'
        ]);
    });
    
});