<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Tournament;
use App\Models\Withdraw;
use App\Models\AppVersion;
use App\Models\User;
use App\Models\UserName;
use App\Models\ludoTournament;
use App\Models\Transaction;
use App\Models\Complaint;
use Carbon\Carbon;

class AdminShowController extends Controller
{
    public function showAnnouncement(){
        $data = Announcement::orderby('ann_id','desc')->get();
        if($data->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Nothing to Show'
            ]);
        }
    }

    public function showTournaments(){
        $tournament = Tournament::where(['completed'=> 0 , 'created_by'=>'Admin','cancel'=> 0])->get();
        if($tournament->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $tournament
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Add Tournaments'
            ]);
        }
    }

    public function withdraw(){
        $withdraw_record = Withdraw::where('completed',0)->get();
        if($withdraw_record->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $withdraw_record
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'No Records'
            ]);
        }
    }

    public function versions(){
        $versions = AppVersion::orderby('id','desc')->get();
        return response()->json([
            'status' => true,
            'data' => $versions
        ]);
    }

    public function showTournamentsUser(){
        $tournaments = Tournament::orderby('id','desc')->where(['created_by'=>'User','completed'=> 0 ,'cancel' => 0])->get();
        return response()->json([
            'status' => true,
            'data' => $tournaments
        ]);
    }

    public function complete($id){
        $tournament = Tournament::where('tournament_id',$id)->get()->first();
        if($tournament->count() > 0){
            if($tournament->joined_user != null){
                $arr = explode(',',$tournament->joined_user);
                $data = array();
                for ($i=0; $i < count($arr); $i++) { 
                    $user = User::select('name')->where('id',$arr[$i])->get()->first();
                    $data1 = UserName::select('pubg_username','pubg_user_id','user_id')->where(['user_id' => $arr[$i] , 'tournament_id' => $id])->get()->first();
                    $user->user_id = $data1->user_id;
                    $user->pubg_username = $data1->pubg_username;
                    $user->pubg_user_id = $data1->pubg_user_id;
                    array_push($data,$user);
                }
                return response()->json([
                    'status' => true,
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => 'You have No Users To complete the Tournament'
                ]);
            }
        }
    }

    public function indexData(){
        $publicTournament = Tournament::where('tournament_type','public')->get()->count();
        $todayPublicTournament = Tournament::where(['tournament_type'=>'public','created_at'=> Carbon::today()])->get()->count();
        $privateTournament = Tournament::where('tournament_type','private')->get()->count();
        $todayPrivateTournament = Tournament::where(['tournament_type'=>'private','created_at'=> Carbon::today()])->get()->count();
        $completedTournamentPublic = Tournament::where(['completed'=>1 , 'tournament_type'=>'public'])->get()->count();
        $todayCompletedTournamentPublic = Tournament::where(['completed'=>1, 'tournament_type'=>'public','created_at'=> Carbon::today()])->get()->count();
        $completedTournamentPrivate = Tournament::where(['completed'=>1 , 'tournament_type'=>'private'])->get()->count();
        $todayCompletedTournamentPrivate = Tournament::where(['completed'=>1, 'tournament_type'=>'private','created_at'=> Carbon::today()])->get()->count();
        $totalUsers = User::get()->count();
        $allTransactions = Transaction::where('razorpay_id','!=',null)->where('payment_done',1)->get()->count();
        $users = User::select(['users.*','user_info.profile_image as img','user_info.withdrawal_amount','user_info.wallet_amount'])->orderby('users.id' , 'desc')->join('user_info','users.id','=','user_info.user_id')->take(5)->get();
        $data = array('totalPublic' => $publicTournament , 'todayPublic' =>$todayPublicTournament,'totalPrivate'=>$privateTournament,'todayPrivate' => $todayPrivateTournament , 'totalCompletedPublic' =>$completedTournamentPublic,'todayCompletedPublic'=>$todayCompletedTournamentPublic , 'completedTournamentPrivate' => $completedTournamentPrivate , 'todayCompletedTournamentPrivate'=>$todayCompletedTournamentPrivate,'totalUser' => $totalUsers,'totalTransactions' => $allTransactions,'users' => $users);
        return response()->json([
            'status'=> true,
            'data' => $data
        ]);
    }

    public function editAnnouncement($id){
        $data = Announcement::where('ann_id',$id)->get()->first();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function editVersion($v_id){
        $version = AppVersion::where('id' , $v_id)->get()->first();
        return response()->json([
            'status' => true,
            'data' => $version
        ]);
    }

    public function tournamentsHistory(){
        $data = Tournament::where('completed',1)->orwhere('cancel',1)->get();
        if($data->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }
    }

    public function showComplaints(){
        $complaints = Complaint::select(['complaints.*' , 'users.name'])->where('complaints.status',0)->join('users','complaints.user_id','=','users.id')->get();
        if($complaints->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $complaints
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'No Records Found'
            ]);
        }  
    }

    public function users(){
        $user = User::select(['users.*','user_info.profile_image as img','user_info.withdrawal_amount','user_info.wallet_amount','user_info.ptr_reward'])->orderby('users.id' , 'desc')->join('user_info','users.id','=','user_info.user_id')->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    public function ludoTournament(){
        $ludotournament = ludoTournament::select(['ludo_tournament.*','ludo_tournament.id as ludoID','ludotournamentresult.*'])
                            ->orderby('ludo_tournament.id' , 'desc')->where(['ludo_tournament.completed'=>0,'ludo_tournament.cancel'=>0,'ludotournamentresult.status'=>0])->where('ludotournamentresult.winner','!=', null)
                            ->orwhere('ludotournamentresult.winner','!=', null)
                            ->where('ludotournamentresult.error1','!=', null)
                            ->join('ludotournamentresult','ludo_tournament.id','=','ludotournamentresult.tournament_id')->get();
        return response()->json([
            'status' => true,
            'data' => $ludotournament
        ]);
    }
    
    public function ludoResult($tournament_id){
        $tournamentDetail = ludoTournament::select(['ludo_tournament.user1','ludo_tournament.user2','ludotournamentresult.img1','ludotournamentresult.img2','ludotournamentresult.winner','ludotournamentresult.looser1','ludotournamentresult.error1'])->where('ludo_tournament.id',$tournament_id)->join('ludotournamentresult','ludo_tournament.id','=','ludotournamentresult.tournament_id')->get()->first();
        if($tournamentDetail){
            $user1 = json_decode($tournamentDetail->user1);
            $user2 = json_decode($tournamentDetail->user2);
            $tournamentDetail->username1 = $user1[0]->username;
            $tournamentDetail->username2 = $user2[0]->username;
            $tournamentDetail->user_id1 = $user1[0]->user_id;
            $tournamentDetail->user_id2 = $user2[0]->user_id;
            return response()->json([
                'status' => true,
                "data" => $tournamentDetail
            ]);
        }
    }

    public function updateLudoResult(Request $request){
        return $request->all();
    }

}
