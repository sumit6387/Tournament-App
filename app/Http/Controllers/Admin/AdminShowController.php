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
        $data = array('totalPublic' => $publicTournament , 'todayPublic' =>$todayPublicTournament,'totalPrivate'=>$privateTournament,'todayPrivate' => $todayPrivateTournament , 'totalCompletedPublic' =>$completedTournamentPublic,'todayCompletedPublic'=>$todayCompletedTournamentPublic , 'completedTournamentPrivate' => $completedTournamentPrivate , 'todayCompletedTournamentPrivate'=>$todayCompletedTournamentPrivate,'totalUser' => $totalUsers,'totalTransactions' => $allTransactions);
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
        $complaints = Complaint::where('status' , 0)->get();
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

}
