<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\UserName;
use App\Models\Transaction;
use App\Models\Notification;

class ShowController extends Controller
{
    public function showTournaments($v ,$game, $type){
        $game = strtoupper($game);
        $data = array();
        $adminTournament = Tournament::orderby('created_at' , 'asc')->where(['created_by'=>'Admin','tournament_type' => 'public','completed'=> 0,'cancel'=>0,'game_type' => $game , 'type' => $type])->get();
        if($adminTournament){
            $tour = true;
            foreach ($adminTournament as $key => $value) {
                array_push($data,$value);
                $data[$key]->joined_user = explode(',',$data[$key]->joined_user);
            }

        }else{
            $adminTournament = "Nothing";
        }
        $membersTournaments = Tournament::select(['tournaments.*','users.membership as membership'])->orderby('tournaments.created_at' , 'asc')->where(['tournaments.created_by'=>'User','tournaments.tournament_type' => 'public','tournaments.completed'=> 0,'tournaments.game_type' => $game , 'tournaments.type' => $type,'tournaments.cancel'=>0,'users.membership' => 1])->join('users','tournaments.id','=','users.id')->get();
        if($membersTournaments){
            $member = true;
            foreach ($membersTournaments as $key => $value) {
                array_push($data,$value);
                $data[$key]->joined_user = explode(',',$data[$key]->joined_user);
            }
        }else{
            $membersTournaments = "Nothing";
        }

        $userTournament = Tournament::select(['tournaments.*','users.membership as membership'])->orderby('tournaments.created_at' , 'asc')->where(['tournaments.created_by'=>'User','tournaments.tournament_type' => 'public','tournaments.completed'=> 0,'tournaments.cancel'=>0,'tournaments.game_type'=>$game,'tournaments.type'=>$type,'users.membership' => 0])->join('users','tournaments.id','=','users.id')->get();
        if($userTournament){
            $userTour = true;
            foreach ($userTournament as $key => $value) {
                array_push($data,$value);
                $data[$key]->joined_user = explode(',',$data[$key]->joined_user);
            }

        }else{
            $userTournament = 'Nothing';
        }
        if($tour || $userTour || $member){
            return response()->json([
                'status' => true,
                'data' => $data
                ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => "Tournament Not Created"
            ]);
        }
    }

    public function pointTableUser(){
        $users = User::select(['user_info.profile_image','users.name','user_info.ptr_reward as ptr_reward'])->orderBy('ptr_reward','desc')->join('user_info','users.id','=','user_info.user_id')->take(20)->get();
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
        $transaction = Transaction::select('transactions.amount','transactions.description','transactions.action','transactions.created_at')->orderby('id','desc')->where(['user_id'=>auth()->user()->id , 'payment_done' => 1]);
        if($transaction->get()->count()){
            return response()->json([
                'status' => true,
                'data' => $transaction->paginate(10)
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
        $data = Tournament::orderby('id','desc')->where(['created_by' => 'User','id' => auth()->user()->id,'completed' => 0 , 'cancel' => 0])->get();
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
        $data = User::select(['users.*','user_info.gender','user_info.profile_image','user_info.state','user_info.country','user_info.refferal_code','user_info.ref_by','user_info.withdrawal_amount','user_info.wallet_amount','user_info.ptr_reward'])->where('users.id' , auth()->user()->id)->join('user_info','users.id','=','user_info.user_id')->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
        public function search(Request $req){
            $data = Tournament::where(['created_by'=> 'User','tour_id'=>$req->id])->get();
            if($data->count()){
                return response()->json([
                    'status'=> true,
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    'status'=> false,
                    'data' => 'Enter Valid ID'
                ]);
            }
        }

        public function numberOfNotification(){
            $no_of_notification = Notification::where(['user_id' => auth()->user()->id , 'seen' => 0])->get()->count();
            if($no_of_notification){
                return response()->json([
                    'status' => true,
                    'data' => $no_of_notification
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => 0
                ]);
            }
        }

        public function notification(){
            $notifications  = Notification::where('user_id',auth()->user()->id);
            if($notifications->get()->count()){
                return response()->json([
                    'status'=> true,
                    'data' => $notifications->paginate(10)
                ]);
            }else{
                return response()->json([
                    'status'=> false,
                    'data' => 'You have no notification'
                ]);
            }
        }

        public function updateSeen(){
            $notifi = Notification::where('user_id',auth()->user()->id)->update(['seen' => 1]);
            if($notifi){
                return response()->json([
                    'status' => true
                ]);
            }else{
                return response()->json([
                    'status' => false
                ]);
            }
        }

        public function showUsername($id){
            $data = Tournament::where('tournament_id',$id)->get()->first();
            $arr = explode(',',$data->joined_user);
            $usernames = array();
            if(sizeof($arr)){
            for ($i=0; $i < sizeof($arr); $i++) { 
                $username = UserName::select(['usernames.pubg_username','usernames.pubg_user_id'])->where(['user_id' => $arr[$i] , 'tournament_id' => $id])->get()->first();
                array_push($usernames,$username);
            }
            return response()->json([
                'status' => true,
                'data'=> $usernames
            ]);

        }else{
            return response()->json([
                'status' => false,
                'msg' => 'No User Participated'
            ]);
        }
        }

        public function history($v,$game , $time){
            $game = strtoupper($game);
            $data = Tournament::select(['tournaments.*','history.*'])->orderby('tournaments.tournament_id' , 'desc')->where(['history.status'=>$time,'history.game'=>$game])->join('history','tournaments.tournament_id','=','history.tournament_id');
            if($data->get()->count()){
                return response()->json([
                    'status' => true,
                    'data' => $data->paginate(10)
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => 'You have no history'
                ]);
            }
        }
}
