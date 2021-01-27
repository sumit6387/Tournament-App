<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInfo;
use App\Models\LudoTournament;
use App\Models\Transaction;
use App\Models\User;
use App\Models\History;
use App\Models\LudoResult;
use App\Functions\AllFunction;
use Illuminate\Support\Str;
use Validator;
use Exception;
use Illuminate\Support\Carbon;

class LudoController extends Controller
{
    public function createLudoTournament(Request $request){
        $valid = Validator::make($request->all() , ["username" => "required" , "entry_fee" => "required" ,"game" => "required"]);
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
                $transaction->action = "D";
                $transaction->payment_id = Str::random(10);
                $transaction->payment_done = 1;
                $transaction->save();
                $user->save();
                $newtournament = new LudoTournament();
                if($request->game == "ludo"){
                    $game = "ludo_".Str::random(8);
                }
                if($request->game == "snake"){
                    $game = "snake_".Str::random(8);
                }
                $newtournament->ludo_id = $game;
                $newtournament->user1 = json_encode(array(["user_id" => auth()->user()->id , "username"=>$request->username]));
                $newtournament->entry_fee = $request->entry_fee;
                $newtournament->game = $request->game;
                $newtournament->winning = round($request->entry_fee*2 - (($request->entry_fee*2)*10)/100); //10% comission
                
                if($newtournament->save()){
                    $history =new History();
                    $history->user_id = auth()->user()->id;
                    $history->tournament_id = $newtournament->id;
                    $history->game = "ludo";
                    $history->status = "live";
                    $history->save();
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
            $user = UserInfo::where('user_id',auth()->user()->id)->get()->first();
            $tournament = LudoTournament::where('id',$request->tournament_id)->get()->first();
            if($user && $tournament){
                if($tournament->user2==NULL){
                    $joined = json_decode($tournament->user1);
                    if($joined[0]->user_id != auth()->user()->id){
                        if($user->wallet_amount >= $tournament->entry_fee){
                            $user->wallet_amount = $user->wallet_amount - $tournament->entry_fee;
                            $tournament->user2 = json_encode(array(['user_id' => auth()->user()->id , "username" => $request->username]));
                            $tournament->save();
                            $user->save();
                            $transaction = new Transaction();
                            $transaction->user_id = auth()->user()->id;
                            $transaction->reciept_id = Str::random(10);
                            $transaction->amount = $tournament->entry_fee;
                            $transaction->description = "For Joining Ludo Game";
                            $transaction->action = "D";
                            $transaction->payment_id = Str::random(10);
                            $transaction->payment_done = 1;
                            $transaction->save();
                            $notifi = new AllFunction();
                            $user_id1 = json_decode($tournament->user1);
                            $notifi->sendNotification(['id' => $user_id1[0]->user_id , 'title' => 'Joined Tournament' , 'msg' => 'Someone Joined the Tournament.Now play Match.' , 'icon' => 'gamepad']);
                            $history =new History();
                            $history->user_id = auth()->user()->id;
                            $history->tournament_id = $request->tournament_id;
                            $history->game = "ludo";
                            $history->status = "live";
                            $history->save();
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
                    'msg' => 'Someone Alredy Joined The Tournament'
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

    // cancel ludo tournament
    public function cancel(Request $request){
        $valid = Validator::make($request->all() , ["tournament_id" => "required"]);
        if($valid->passes()){
            $tournament = LudoTournament::where("id",$request->tournament_id)->get()->first();
            if($tournament && $tournament->cancel == 0){
                $user1 = json_decode($tournament->user1);
                $user2 = json_decode($tournament->user2);
                if($user1[0]->user_id == auth()->user()->id){
                    $prizedist = new AllFunction();
                    $tournament->cancel = 1;
                    $tournament->save();
                    $resp1=true;
                    if($user1){
                        $resp = $prizedist->ludoPrizeDistribution($user1[0]->user_id,$request->tournament_id);
                    }
                    if($user2){
                        $resp1 = $prizedist->ludoPrizeDistribution($user2[0]->user_id,$request->tournament_id);
                    }
                    if($resp && $resp1){
                        return response()->json([
                            'status' => true,
                            "msg" => "Tournament Canceled and Entry fee credited to your account."
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
                        'msg' => "You can not cancel this tournament because you are not created this tournament"
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    "msg" => "Tournament Alredy Canceled or Something Went Wrong"
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                "msg" => $valid->errors()->all()
            ]);
        }
    }

    public function myChallenges(){
        $tournament = LudoTournament::orderby('id','desc')->whereDate('created_at', Carbon::today())->get();
        if($tournament){
            $data = array();
            foreach ($tournament as $value) {
                    $user = json_decode($value->user1);
                    if($user[0]->user_id == auth()->user()->id){
                            array_push($data,$value);
                            $key = count($data)-1;
                            $data[$key]->iswinner = false;
                            if($value->completed == 1){
                                $result = LudoResult::where('tournament_id',$value->id)->get()->first();
                                if($result){
                                    if($result->winner == auth()->user()->id && $result->status == 1){
                                        $data[$key]->iswinner = true;
                                    }
                                }
                            }
                            $data[$key]->img = UserInfo::where("user_id",auth()->user()->id)->get()->first()->profile_image;
                            $data[$key]->username = $user[0]->username;
                }
            }
            if(count($data) > 0){
                return response()->json([
                    'status' => true,
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    "status" => false,
                    'msg' => "No Challenges Available"
                ]);
            }
        }
    }

    public function liveChallenges($v,$page){
        $tournament = LudoTournament::orderby('id','desc')->whereDate('created_at', Carbon::today())->where('cancel',0)->get();
        $data = array();
        foreach ($tournament as $value) {
            array_push($data,$value);
            $key = count($data)-1;
            $user = json_decode($value->user1);
            $data[$key]->img1 = UserInfo::where("user_id",$user[0]->user_id)->get()->first()->profile_image;
            $data[$key]->username1=$user[0]->username;
            $data[$key]->img2 = null;
            $data[$key]->username2 = null;
            $data[$key]->ujoined = false;
            if($value->user2){
                $user_id = json_decode($value->user2);
                $data[$key]->img2 = UserInfo::where("user_id",$user_id[0]->user_id)->get()->first()->profile_image;
                $data[$key]->username2=$user_id[0]->username;
                $data[$key]->ujoined = true;
            }
        }
        $page = (int)$page;
        if($page == 1){
            $start_data = 1;
        }else{
            $start_data = $page *10 + 1 ;
        }
        
        if(count($data) > 0){
            return response()->json([
                'status' => true,
                'data' => collect($data)->forPage($start_data ,1)
            ]);
        }else{
            return response()->json([
                'status' => false,
                "msg" => "We have No todays challenges"
            ]);
        }
    }

    public function updateRoomId(Request $request){
        $valid = Validator::make($request->all(),['tournament_id' => "required","roomid" => "required"]);
        if($valid->passes()){
            $tournament = LudoTournament::where(['id'=>$request->tournament_id,'completed'=>0,'cancel' => 0])->get()->first();
            if($tournament){
           if($tournament->user2){
            $user = json_decode($tournament->user1);
            if($tournament && $user[0]->user_id == auth()->user()->id){
                $tournament->room_id = $request->roomid;
                $tournament->save();
                if($tournament->user2){
                    $user_id2 = json_decode($tournament->user2);
                    $notifi = new AllFunction();
                    $notifi->sendNotification(['id' => $user_id2[0]->user_id , 'title' => 'Room ID Updated' , 'msg' => 'Room ID Updated. Now Play Match.' , 'icon' => 'gamepad']);
                }
                return response()->json([
                    'status' => true,
                    'msg' => "Room ID Updated."
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "Something Went Wrong"
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => "You can not update RoomID because your opponent not joined."
            ]);
        }
    }else{
        return response()->json([
            'status' => false,
            'msg' => "This Tournament Already Canceled/Completed."
        ]);
    }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function ludoTournamentDetail($v,$tournament_id){
        $tournament = LudoTournament::where('id',$tournament_id)->get()->first();
        if($tournament){
            $user1 = json_decode($tournament->user1);
            $tournament->img1 = UserInfo::where('user_id',$user1[0]->user_id)->get()->first()->profile_image;
            $tournament->username1 = $user1[0]->username;
            $tournament->img2 = null;
            $tournament->ujoined = false;
            $tournament->username2 = null;
            if($tournament->user2){
                $user2 = json_decode($tournament->user2);
                $tournament->img2 = UserInfo::where('user_id',$user2[0]->user_id)->get()->first()->profile_image;
                $tournament->username2 = $user2[0]->username;
                $tournament->ujoined = true;
            }
            return response()->json([
                'status' => true,
                'data' => $tournament
            ]);

        }else{
            return response()->json([
                'status' => false,
                'msg' => "Something Went Wrong"
            ]);
        }
    }

    public function winner(Request $request){
        $valid = Validator::make($request->all(),['tournament_id'=>'required' , 'img' => "required"]);
        if($valid->passes()){
            $tournament = LudoTournament::where('id',$request->tournament_id)->get()->first();
            if($tournament){
               if($tournament->user1 && $tournament->user2){
                $result = LudoResult::where('tournament_id',$request->tournament_id)->get()->first();
                $history = History::where(['user_id'=>auth()->user()->id , 'tournament_id' => $request->tournament_id,'game' => 'ludo'])->get()->first();
                $history->status = 'past';
                $history->save();
                if($result){
                    if($result->winner){
                        $result->img2 = $request->img;
                        $result->save();
                        return response()->json([
                            'status' => true,
                            'msg' => "Your result is reviewing.After 2 hours result will be declayered."
                        ]);
                    }else{
                        $result->winner = auth()->user()->id;
                        $result->img1 = $request->img;
                        $result->save();
                        if($result->error1){
                            return response()->json([
                                'status' => true,
                                'msg' => "Your Match in review After 2 hours result will be declayered."
                            ]);
                        }
                        if($result->looser1 || $result->looser2){
                            $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                            $userinfo->withdrawal_amount = $userinfo->withdrawal_amount + $tournament->winning;
                            $userinfo->save();
                            $notifi = new AllFunction();
                            $notifi->sendNotification(['id' => auth()->user()->id , 'title' => 'Winning Ludo Tournament' , 'msg' => 'You win the ludo tournament.'.$tournament->winning.' added to your account.' , 'icon' => 'money']);
                            $transaction = new Transaction();
                            $transaction->user_id = auth()->user()->id;
                            $transaction->reciept_id = Str::random(10);
                            $transaction->amount = $tournament->winning;
                            $transaction->description = "For Winning the Ludo Tournament";
                            $transaction->action = "C";
                            $transaction->payment_id = Str::random(10);
                            $transaction->payment_done = 1;
                            $transaction->save();
                            $result->status = 1;
                            $result->save();
                            $tournament->completed = 1;
                            $tournament->save();
                            return response()->json([
                                'status' => true,
                                'msg' => "Money Added To your Account"
                            ]);
                        }
                        return response()->json([
                            'status' => true,
                            'msg' => "Your result is reviewing.After 2 hours result will be declayered."
                        ]);
                    }
                }else{
                    $res = new LudoResult();
                    $res->tournament_id = $request->tournament_id;
                    $res->winner = auth()->user()->id;
                    $res->img1 = $request->img;
                    $res->save();
                    return response()->json([
                        'status' => true,
                        'msg'=> "Your result is reviewing.After 2 hours result will be declayered."
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "You can not update result because any opponent not join the tournament."
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
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function looser(Request $request){
        $valid = Validator::make($request->all(),["tournament_id" => "required"]);
        if($valid->passes()){
            $tournament = LudoTournament::where('id',$request->tournament_id)->get()->first();
            if($tournament){
               if($tournament->user1 && $tournament->user2){
                $result = LudoResult::where('tournament_id',$request->tournament_id)->get()->first();
                $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                if($result && $userinfo){
                    if($result->looser1 || $result->looser2){
                            $notifi = new AllFunction();
                            if($result->looser1){
                                $result->looser2 = auth()->user()->id;
                                $notifi->sendNotification(['id' => auth()->user()->id , 'title' => 'Winning Ludo Tournament' , 'msg' => 'Both of you selected loose.So Entry fee 50% amount is credited to your account.', 'icon' => 'money']);
                                $userinfo->wallet_amount = $userinfo->wallet_amount + ($tournament->entry_fee*50)/100;
                                $userinfo->save();
                                $this->insertTransaction(auth()->user()->id,($tournament->entry_fee*50)/100,"For Winning Ludo Tournament","C");
                                $this->updateHistory(auth()->user()->id , $request->tournament_id);

                                // add money to second user
                                $notifi->sendNotification(['id' => $result->looser1 , 'title' => 'Winning Ludo Tournament' , 'msg' => 'Both of you selected loose.So Entry fee 50% amount is credited to your account.', 'icon' => 'money']);
                                $userinfo1 = UserInfo::where('user_id',$result->looser1)->get()->first();
                                $userinfo1->wallet_amount = $userinfo1->wallet_amount + ($tournament->entry_fee*50)/100;
                                $userinfo1->save();
                                $result->status = 1;
                                $result->save();
                                $tournament->completed = 1;
                                $tournament->save();
                                $this->insertTransaction($result->looser1,($tournament->entry_fee*50)/100,"For Winning Ludo Tournament","C");
                                $this->updateHistory($result->looser1 , $request->tournament_id);
                                return response()->json([
                                    'status' => true,
                                    'msg' => "Both of you selected loose.So Entry fee 50% amount is credited to your account."
                                ]);
                            }else{
                                return response()->json([
                                    'status' => false,
                                    "msg" => "Something Went Wrong"
                                ]);
                            }
                    }else{
                        $result->looser1 = auth()->user()->id;
                        $this->updateHistory(auth()->user()->id , $request->tournament_id);
                        $result->save();
                        // check if already error and then looser the distribute entry fee 50-50%
                        if($result->error1){
                            // for current user
                            $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                            $userinfo->wallet_amount = $userinfo->wallet_amount + ($tournament->entry_fee*50)/100;
                            $userinfo->save();
                            $this->insertTransaction(auth()->user()->id,($tournament->entry_fee*50)/100,"For Ludo Tournament Result","C");

                            // for another user
                            $userinfo = UserInfo::where('user_id',$result->error1)->get()->first();
                            $userinfo->wallet_amount = $userinfo->wallet_amount + ($tournament->entry_fee*50)/100;
                            $userinfo->save();
                            $this->insertTransaction($result->error1,($tournament->entry_fee*50)/100,"For Ludo Tournament Result","C");
                            return response()->json([
                                'status' => true,
                                'msg' => "Your Match in review After 2 hours result will be declayered."
                            ]);
                        }
                        $notifi = new AllFunction();
                        if($result->winner){
                            $user = UserInfo::where('user_id',$result->winner)->get()->first();
                            $user->withdrawal_amount = $user->withdrawal_amount + $tournament->winning;
                            $user->save();
                            $notifi->sendNotification(['id' => $result->winner , 'title' => 'Winning Ludo Tournament' , 'msg' => 'You Won the Ludo Tournament.'.$tournament->winning.' Added to your Account.', 'icon' => 'money']);
                            $this->updateHistory($result->winner , $request->tournament_id);
                            $this->insertTransaction($result->winner,$tournament->winning,"Win Ludo Tournament","C");
                            $result->status = 1;
                            $result->save();
                            $tournament->completed = 1;
                            $tournament->save();
                            return response()->json([
                                'status' => true,
                                'msg' => "Result Updated"
                            ]);
                        }
                        return response()->json([
                            'status' => true,
                            'msg' => "Your Result in review.After 2 hours result will be declayered"
                        ]);
                    }
                }else{
                    $newresult = new LudoResult();
                    $newresult->tournament_id = $request->tournament_id;
                    $newresult->looser1 = auth()->user()->id;
                    $newresult->save();
                    $this->updateHistory(auth()->user()->id , $request->tournament_id);
                    return response()->json([
                        'status' => true,
                        'msg' => "Your Result in review.After 2 hours result will be declayered"
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "You can not update result because any opponent not join the tournament."
                ]);
            }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Something Went Wrong'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function error(Request $req){
        $valid = Validator::make($req->all(),["tournament_id" => "required"]);
        if($valid->passes()){
            $tournament = LudoTournament::where(['id' => $req->tournament_id,"completed" => 0 , "cancel" => 0])->get()->first();
            if($tournament){
              if($tournament->user2){
                $result = LudoResult::where('tournament_id',$req->tournament_id)->get()->first();
                $this->updateHistory(auth()->user()->id,$req->tournament_id);
                if($result){
                    if($result->winner){
                        $result->error1 = auth()->user()->id;
                        $result->save();
                        return response()->json([
                            'status' => true,
                            'msg' => "Your Match in review After 2 hours result will be declayered."
                        ]);
                    }
                    // check if looser and error then distribute entry fee 50-50%
                    if($result->looser1){
                        $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                        $userinfo->wallet_amount = $userinfo->wallet_amount + ($tournament->entry_fee*50)/100;
                        $userinfo->save();
                        $this->insertTransaction(auth()->user()->id,($tournament->entry_fee*50)/100,"For Choosing Wrong Option","C");

                        $userinfo = UserInfo::where('user_id',$result->looser1)->get()->first();
                        $userinfo->wallet_amount = $userinfo->wallet_amount + ($tournament->entry_fee*50)/100;
                        $userinfo->save();
                        $this->insertTransaction($result->looser1,($tournament->entry_fee*50)/100,"For Choosing Wrong Option","C");
                        $result->status = 1;
                        $result->save();
                        $tournament->cancel = 1;
                        $tournament->save();
                        return response()->json([
                            'status' => true,
                            'msg' => "Result Updated"
                        ]);
                    }
                    if($result->error1){
                        // for current user
                        $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                        $result->error2 = auth()->user()->id;
                        $result->status = 1;
                        $result->save();
                        $userinfo->wallet_amount = $userinfo->wallet_amount + $tournament->entry_fee;
                        $userinfo->save();
                        $tournament->cancel = 1;
                        $tournament->save();
                        $this->insertTransaction(auth()->user()->id,$tournament->entry_fee,"For Ludo Tournament Cancel","C");

                        // for error1 user
                        $userinfo = UserInfo::where('user_id',$result->error1)->get()->first();
                        $userinfo->wallet_amount = $userinfo->wallet_amount + $tournament->entry_fee;
                        $userinfo->save();
                        $this->insertTransaction($result->error1,$tournament->entry_fee,"For Ludo Tournament Cancel","C");
                        $this->updateHistory($result->error1,$req->tournament_id);
                        return response()->json([
                            'status' => true,
                            'msg' => "Your Entry Fee Return to your Account"
                        ]);
                    }else{
                        return response()->json([
                            'status' => false,
                            'msg' => "Something Went Wrong"
                        ]);
                    }
                }else{
                    $newResult = new LudoResult();
                    $newResult->tournament_id = $req->tournament_id;
                    $newResult->error1 = auth()->user()->id;
                    $newResult->save();
                    return response()->json([
                        'status' => true,
                        'msg' => "Result in review After 2 hours result updated"
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "You can not update result because any opponent not join the tournament."
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

    public function ludoHistory($v,$game,$status,$page){
        $History = History::where(["user_id"=>auth()->user()->id,"game" => $game,"status"=>$status])->orderby('id','desc')->get();
        $data = array();
        foreach ($History as $value) {
            array_push($data,$value);
            $key = count($data) -1;
            $tournament = LudoTournament::where('id',$value->tournament_id)->get()->first();
            $data[$key]->ludo_id = $tournament->ludo_id;
            $data[$key]->entry_fee = $tournament->entry_fee;
            $user1 = json_decode($tournament->user1);
            $data[$key]->username1 = $user1[0]->username;
            $data[$key]->img1 = UserInfo::where('user_id',$user1[0]->user_id)->get()->first()->profile_image;
            $data[$key]->iswinner = false; 
            if($tournament->completed == 1){
                $result = LudoResult::where('tournament_id',$tournament->id)->get()->first();
                if($result){
                    if($result->winner == auth()->user()->id && $result->status == 1){
                        $data[$key]->iswinner = true;
                    }
                }
            } 
            $data[$key]->username2 = null; 
            $data[$key]->img2 = null;
            if($tournament->user2){
                $user2 = json_decode($tournament->user2);
                $data[$key]->username2 = $user2[0]->username; 
                $data[$key]->img2 = UserInfo::where('user_id',$user2[0]->user_id)->get()->first()->profile_image; 
            }
            
        }
        $page = (int)$page;
        if($page == 1){
            $start_data = 1;
        }else{
            $start_data = $page *10 + 1 ;
        }
        if($data){
            return response()->json([
                'status' => true,
                'data' => collect($data)->forPage($start_data,10)
            ]);
        }else{
            return response()->json([
                'status' => false,
                "data" => "No History Found"
            ]);
        }
    }

    // for update history
    private function updateHistory($user_id,$tournament_id){
        $history = History::where(['user_id'=>$user_id , 'tournament_id' => $tournament_id,'game' => 'ludo'])->get()->first();
        $history->status = "past";
        $history->save();
    }
    
    //for insert new transaction 
    private function insertTransaction($user_id,$amount,$desc,$action){
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->reciept_id = Str::random(10);
        $transaction->amount = $amount;
        $transaction->description = $desc;
        $transaction->action = $action;
        $transaction->payment_id = Str::random(10);
        $transaction->payment_done = 1;
        $transaction->save();
    }
}