<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LudoTournament;
use App\Models\LudoResult;
use App\Models\History;
use App\Functions\AllFunction;
use Illuminate\Support\Carbon;

class CheckMembershipController extends Controller
{
    public function CheckMembershipUser(){
        $user = User::where('membership',1)->get();
            if($user){
                foreach ($user as  $value) {
                    $date = date("y-m-d");
                    $exp_date = strtotime($value->Ex_date_membership);
                    // return $value;
                    if( $exp_date <= strtotime($date)){
                        $value->membership = 0;
                        $value->Ex_date_membership = NULL;
                        $value->update();
                        $notifi = new AllFunction();
                        $notifi->sendNotification(array('id' => $value->id ,'title' => 'Membership Expired' , 'msg' => 'Your Membership Expired ','icon'=> 'gamepad'));
                    }
                }
            }
    }

    public function checkTournamentCompleteOrNot(){
        $tournament = LudoTournament::where(['completed' => 0 , 'cancel' =>0])->whereDate('created_at','!=', Carbon::today())->get();
        $date = date("Y-m-d  H:i:s");
        $cancelPrize =new AllFunction();
        foreach ($tournament as $value) {
            if($value->user1){
                $user1 = json_decode($value->user1);
                $cancelPrize->ludoPrizeDistribution($user1[0]->user_id,$value->id);
                $this->updateHistory($user1[0]->user_id,$value->id);
            }
            if($value->user2){
                $user2 = json_decode($value->user2);
                $cancelPrize->ludoPrizeDistribution($user2[0]->user_id,$value->id);
                $this->updateHistory($user2[0]->user_id,$value->id);
            }
            $value->cancel = 1;
            $value->save();
            $ludoResult = LudoResult::where('tournament_id',$value->id)->get()->first();
            if($ludoResult){
                $ludoResult->delete();
            }
        }
    }

    // for update history
    private function updateHistory($user_id,$tournament_id){
        $history = History::where(['user_id'=>$user_id , 'tournament_id' => $tournament_id,'game' => 'ludo'])->get()->first();
        $history->status = "past";
        $history->save();
    }
}
