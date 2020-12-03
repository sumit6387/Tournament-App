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
        $adminTournament = Tournament::orderby('tournament_start_date' , 'asc')->orderby('tournament_start_time','asc')->orderby('tournament_id' , 'desc')->where(['created_by'=>'Admin','tournament_type' => 'public','completed'=> 0,'cancel'=>0])->get();
        if($adminTournament){
            $tour = true;
        }else{
            $adminTournament = "Nothing";
        }
        $membersTournaments = Tournament::select(['tournaments.*','users.membership as membership'])->orderby('tournaments.tournament_id' , 'desc')->orderby('tournament_start_time','asc')->where(['tournaments.created_by'=>'User','tournaments.tournament_type' => 'public','tournaments.completed'=> 0,'tournaments.cancel'=>0,'users.membership' => 1])->join('users','tournaments.id','=','users.id')->get();
        if($membersTournaments){
            $member = true;
        }else{
            $membersTournaments = "Nothing";
        }

        $userTournament = Tournament::select(['tournaments.*','users.membership as membership'])->orderby('tournaments.tournament_id' , 'desc')->orderby('tournament_start_time','asc')->where(['tournaments.created_by'=>'User','tournaments.tournament_type' => 'public','tournaments.completed'=> 0,'tournaments.cancel'=>0,'users.membership' => 0])->join('users','tournaments.id','=','users.id')->get();
        if($userTournament){
            $userTour = true;
        }else{
            $userTournament = 'Nothing';
        }
        if($tour || $userTour || $member){
            return response()->json([
                'status' => true,
                'AdminTournament' => $adminTournament,
                'userData' => $userTournament,
                'membersTournaments'=> $membersTournaments
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
    public function ourTournament(){
        $data = Tournament::where(['created_by' => 'User','id' => auth()->user()->id])->get();
        if($data){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => true,
                'data' => 'You did not created a tournament'
            ]);
        } 
    }

    public function tournamentDetail(Request $req){
        $data = Tournament::where(['tournament_id' => $req->tournament_id , 'created_by' => 'User'])->get()->first();
        if($data){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Something Went Wrong'
            ]);
        }
    }


    public function user(){
        $data = User::select(['users.*','user_info.gender','user_info.profile_image','user_info.state','user_info.country','user_info.refferal_code','user_info.ref_by','user_info.withdrawal_amount','user_info.wallet_amount','user_info.ptr_reward'])->where('users.id' , auth()->user()->id)->join('user_info','users.id','=','user_info.user_id')->get()->toArray();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

}
