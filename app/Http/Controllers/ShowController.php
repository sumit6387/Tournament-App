<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserInfo;

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
        $users = User::select(['users.*','user_info.ptr_reward as ptr_reward'])->orderBy('ptr_reward','desc')->join('user_info','users.id','=','user_info.user_id')->take(20)->get();
        return $users;
    }
}
