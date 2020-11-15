<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;

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
}
