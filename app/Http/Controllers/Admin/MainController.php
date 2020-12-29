<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AppVersion;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\Result;
use App\Models\UserInfo;
use Validator;
use Exception;
use Illuminate\Support\Str;
use App\Functions\AllFunction;


class MainController extends Controller
{
    public function addAnnouncement(Request $request){
        $valid = Validator::make($request->all() , ['msg' => 'required']);
        if($valid->passes()){
            try{
                // adding the announcement 
                $announcement = new Announcement();
                $announcement->msg = $request->msg;
                $announcement->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'Announcement Added'
                ]);
            }catch(Exception $e){
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
        return $request->all();
    }

    public function addVersion(Request $request){
        $valid = Validator::make($request->all(), ['version' => 'required' , 'short_version' => 'required','app_link'=> 'required']);
        if($valid->passes()){
            try{
                // adding the version
                $version = new AppVersion();
                $version->version = $request->version;
                $version->short_version = $request->short_version;
                $version->app_link = $request->app_link;
                $version->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'New Version Added'
                ]);
            }catch(Exception $e){
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

    public function addGame(Request $request){
        $valid = Validator::make($request->all(),[
            'game_name' => 'required',
            'short_name' => 'required',
            'image' => 'required',
            'description' => 'required',
            'map' => 'required',
            'mode' => 'required',
            'min_player' => 'required',
            'max_player' => 'required'
        ]);
        if($valid->passes()){
            try{
                // adding the game 
                $newgame = new Game();
                $newgame->game_name = $request->game_name;
                $newgame->short_name = $request->short_name;
                $filename = Str::random(15).".jpg";
                $path = $request->file('image')->move(public_path('/images/game image'),$filename);
                $url = url('/images/game image/'.$filename);
                $newgame->image = $url;
                $newgame->description = $request->description;
                $newgame->map = $request->map;
                $newgame->mode = $request->mode;
                $newgame->min_player = $request->min_player;
                $newgame->max_player = $request->max_player;
                $newgame->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'Game Added Successfully',
                    'url' => $url
                ]);

            }catch(Exception $e){
                return response()->json([
                    'status' => false,
                    'msg' => 'Something went wrong'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }



    public function addTournament(Request $request){
        $valid = Validator::make($request->all(),[
            'prize_pool' => 'required',
            'winning' => 'required',
            'per_kill' => 'required',
            'entry_fee' => 'required',
            'type' => 'required',
            'maps' => 'required',
            'max_user_participated' => 'required',
            'game_type' => 'required',
            'tournament_type' => 'required',
            'tournament_start_date' => 'required',
            'tournament_start_time' => 'required'
        ]);
        if($valid->passes()){
            try{
                $new = new AllFunction();
                // adding the tournament
                $data = $new->registerTournament($request->all());
                if($data == true){
                    return response()->json([
                        'status' => true,
                        'msg' => 'Tournament Registered'
                    ]);
                }else{
                    return response()->json([
                        'status' => true,
                        'msg' => 'Some problemm occur! Try Again'
                    ]);
                }
            }catch(Exception $e){
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

    public function updateIdPassword(Request $request){
        $valid = Validator::make($request->all(),['tournament_id' => 'required','user_id' => 'required' , 'password' => 'required']);
        if($valid->passes()){
            // updating the room id and password
            $tournament = Tournament::where('tournament_id' , $request->tournament_id)->update(['room_id' => $request->user_id,'password' => $request->password]);
            if($tournament){
                $user = Tournament::where('tournament_id',$request->tournament_id)->get()->first()->joined_user;
                return response()->json([
                    'status' => true,
                    'msg' => 'UserId And Password Added'
                ]);
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
        return $request->all();
    }

    public function UpdateTournamentComplete(Request $req){
        $valid = Validator::make($req->all(),['tournament_id'=> 'required' , 'results' => 'required']);
        if($valid->passes()){
            // complete the tournament
            $result = new Result();
            $result->tournament_id = $req->tournament_id;
            $result->results = json_encode($req->results);
            $winner = $req->results;
            
            $prize  = new AllFunction();
            foreach ($winner as $key => $value) {
                //distributing prize
                $prize->prizeDistribution($value['user_id'],$value['kill'],$value['winner'],$req->tournament_id);
                if($value['winner'] == 1){
                    $result->winner_id = $value['user_id'];
                    $users = UserInfo::where('user_id',$value['user_id'])->get()->first();
                    $users->ptr_reward = $users->ptr_reward+10;
                    $users->save();
                }
            }
            $result->save();
            $data = Tournament::where('tournament_id',$req->tournament_id)->update(['completed' => 1]);
            if($data){
                return response()->json([
                    'status' => true,
                    'msg' => 'status updated'
                ]);
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

    public function sendnotification(Request $request){
        $valid = Validator::make($request->all() , ['title' => 'required' , 'msg' => 'required' , 'icon'=> 'required' ,'id' => 'required']);
        if($valid->passes()){
            $notification = new AllFunction();
            // sending the notification
            $status = $notification->sendNotification($request->all());
            if($status){
                return response()->json([
                    'status' => true,
                    'msg' => 'Notification send successfully'
                ]);
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

    public function deleteTournament($tournament_id){
        $tournament = Tournament::where('tournament_id',$tournament_id)->get()->first();
        return $tournament;
        if($tournament){
            $users = explode(',',$tournament->joined_user);
                if($tournament->joined_user != null){
                    foreach ($users as $key => $value) {
                        $user = UserInfo::where('user_id' , $value)->get()->first();
                        $user->wallet_amount = $user->wallet_amount + $tournament->entry_fee;
                        $user->save();
                        $notification = new AllFunction();
                        // send all user notification for cancelling who can participated in tournament  the match
                        $notification->sendNotification(array('id' => $value , 'title' => 'Match Canceled' ,'msg' => $tournament->tournament_name." canceled by Organizor",'icon'=> 'gamepad'));
                    }
                }
            $tournament->cancel = 1;
            $tournament->save();
            return response()->json([
                'status' => true,
                'data' => 'Tournament Canceled'
            ]);
        }else{
            return response()->json([
                'status'=> false,
                'data'=> 'Something Went Wrong'
            ]);
        }
    }

    public function updateAnnouncement(Request $request){
        $ann = Announcement::where('ann_id',$request->id)->get()->first();
        if($ann){
            Announcement::where('ann_id',$request->id)->update(['msg' => $request->msg]);
            return response()->json([
                'status' => true,
                'msg' => 'Announcement Updated'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
    }

    public function delete_announcement($ann_id){
        $ann = Announcement::where('ann_id' , $ann_id)->delete();
        if($ann){
            return response()->json([
                'status' => true,
                'msg' => 'Announcement Deleted'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
            
    }

    public function updateVersion(Request $request){
        $data = AppVersion::where('id' , $request->version_id)->update(['version' => $request->version , 'short_version' => $request->short_version , 'app_link' => $request->app_link]);
        if($data){
            return response()->json([
                'status' => true,
                'msg' => 'Version Updated'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
    }

    public function delete_version($id){
        $data = AppVersion::where('id',$id)->delete();
        if($data){
            return response()->json([
                'status' => true,
                'msg' => 'Announcement Deleted'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
    }
}
