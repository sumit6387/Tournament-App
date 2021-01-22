<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInfo;
use App\Models\LudoTournament;
use App\Models\Transaction;
use App\Models\User;
use App\Functions\AllFunction;
use Illuminate\Support\Str;
use Validator;
use Exception;

class LudoController extends Controller
{
    public function createLudoTournament(Request $request){
        $valid = Validator::make($request->all() , ["username" => "required" , "entry_fee" => "required" ,"game" => "required" , "room_id" => "required"]);
        if($valid->passes()){
          try{
            $user = UserInfo::where('user_id',auth()->user()->id)->get()->first();
            if($user->wallet_amount >= $request->entry_fee){
                $user->wallet_amount = $user->wallet_amount-$request->entry_fee;
                $transaction = new Transaction();
                $transaction->user_id = auth()->user()->id;
                $transaction->reciept_id = Str::random(10);
                $transaction->amount = $request->entry_fee;
                $transaction->description = "For Joining Ludo Game";
                $transaction->action = "C";
                $transaction->payment_id = Str::random(10);
                $transaction->payment_done = 1;
                $transaction->save();
                $user->save();
                $newtournament = new LudoTournament();
                $newtournament->ludo_id = "ludo_".Str::random(8);
                $newtournament->user1 = json_encode(array(["user_id" => auth()->user()->id , "username"=>$request->username]));
                $newtournament->entry_fee = $request->entry_fee;
                $newtournament->game = $request->game;
                $newtournament->room_id = $request->room_id;
                $newtournament->winning = round($request->entry_fee*2 - (($request->entry_fee*2)*10)/100); //10% comission
                if($newtournament->save()){
                    return response()->json([
                        'status' => true,
                        "msg" => "You created match successfully.So please wait until anyone accept your chalenge."
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        "msg" => "Something Went Wrong"
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    "msg" => "Insufficient Balance"
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                "msg" => "Something Went Wrong"
            ]);
        }
        }else{
            return response()->json([
                'status' => false,
                "msg" => $valid->errors()->all()
            ]);
        }
    }


    public function joinLudoTournament(Request $request){
        $valid = Validator::make($request->all() , ['tournament_id' => "required" , 'username' => "required"]);
        if($valid->passes()){
            $user = User::where('id',auth()->user()->id)->get()->first();
            $tournament = LudoTournament::where('id',$request->tournament_id)->get()->first();
            if($user && $tournament){
                $joined = json_decode($tournament->user1);
                if($joined[0]->user_id != auth()->user()->id){
                    if($user->wallet_amount >= $tournament->entry_fee){
                        $user->wallet_amount = $user->wallet_amount - $tournament->entry_fee;
                        $user2 = json(['user_id' => auth()->user()->id , "username" => $request->username]);
                        $tournament->user2 = $user2;
                        $tournament->save();
                        $user->save();
                        $transaction = new Transaction();
                        $transaction->user_id = auth()->user()->id;
                        $transaction->reciept_id = Str::random(10);
                        $transaction->amount = $tournament->entry_fee;
                        $transaction->description = "For Joining Ludo Game";
                        $transaction->action = "C";
                        $transaction->payment_id = Str::random(10);
                        $transaction->payment_done = 1;
                        $transaction->save();
                        return response()->json([
                            'status' => true,
                            "msg" => "You Joined The Match"
                        ]);
                    }else{
                        return response()->json([
                            'status' => false,
                            'msg' => "You have Insufficient balence. Add Money."
                        ]);
                    }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "You Already Joined This Tournament"
                ]);
            }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "Something Went Wrong"
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                "msg" => $valid->errors()->all()
            ]);
        }
    }
}
