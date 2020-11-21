<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Transaction;

class ShowController extends Controller
{
    public function showTournaments(){
        $adminTournament = Tournament::where(['created_by'=>'Admin','tournament_type' => 'public','completed'=> 0])->get();
        if($adminTournament){
            $tour = true;
        }else{
            $adminTournament = "Nothing";
        }
        $userTournament = Tournament::where(['created_by'=>'User','tournament_type' => 'public','completed'=> 0])->get();
        if($userTournament){
            $userTour = true;
        }else{
            $userTournament = 'Nothing';
        }
        if($tour || $userTour){
            return response()->json([
                'status' => true,
                'AdminTournament' => $adminTournament,
                'userData' => $userTournament
                ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => "Tournament Not Created"
            ]);
        }
    }

    public function pointTableUser(){
        $users = User::select(['users.name','user_info.ptr_reward as ptr_reward'])->orderBy('ptr_reward','desc')->join('user_info','users.id','=','user_info.user_id')->take(20)->get();
        if($users){
            return response()->json([
                'status' =>true,
                'data' => $users
            ]);
        }else{
            return response()->json([
                'status' =>false,
                'data' => "No User Found"
            ]);
        }
        
    }

    public function myWallet(){
        $money = UserInfo::select('user_info.wallet_amount','user_info.withdrawal_amount')->where('user_id' , auth()->user()->id)->get();
        if($money){
            return response()->json([
                'status' => true,
                'data' => $money
            ]);
        }
    }

    public function allTransactions(){
        $transaction = Transaction::select('transactions.amount','transactions.description','transactions.action','transactions.created_at')->where('user_id',auth()->user()->id)->get();
        if($transaction){
            return response()->json([
                'status' => true,
                'data' => $transaction
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'You have no transactions'
            ]);
        }
    }

    public function allPoint(){
        $points = UserInfo::select('user_info.ptr_reward')->where('user_id',auth()->user()->id)->get()->first();
        if($points){
            return response()->json([
                'status' => true,
                'data' => $points
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Something Went Wrong'
            ]);
        }
    }

    public function referAndEarn(){
        $detail = UserInfo::select('user_info.refferal_code')->where('user_id' , auth()->user()->id)->get()->first();
        $users = UserInfo::where('ref_by' , $detail->refferal_code)->get();
        $user_payment = UserInfo::where(['ref_by'=> $detail->refferal_code,'first_time_payment' => 1])->get();
        return response()->json([
            'status' => true,
            'data' => $detail,
            'user_uses_code' => $users->count(),
            'first_payment' => $user_payment->count()
        ]);
    }
}
