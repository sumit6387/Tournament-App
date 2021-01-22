<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Functions\AllFunction;

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
}
