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
use App\Events\SubmitIdPassword;


class MainController extends Controller
{
    public function addAnnouncement(Request $request){
        $valid = Validator::make($request->all() , ['msg' => 'required']);
        if($valid->passes()){
            try{
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
            'map' => 'required',
            'max_user_participated' => 'required',
            'game_type' => 'required',
            'tournament_type' => 'required',
            'tournament_start_date' => 'required',
            'tournament_start_time' => 'required'
        ]);
        if($valid->passes()){
            try{
                $new = new AllFunction();
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
            $tournament = Tournament::where('tournament_id' , $request->tournament_id)->update(['room_id' => $request->user_id,'password' => $request->password]);
            if($tournament){
                $user = Tournament::where('tournament_id',$request->tournament_id)->get()->first()->joined_user;
                event(new SubmitIdPassword($request->user_id,$request->password,$user));
                // return response()->json([
                //     'status' => true,
                //     'msg' => 'UserId And Password Added'
                // ]);
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
            $result = new Result();
            $result->tournament_id = $req->tournament_id;
            $result->results = $req->results;
            $winner = json_decode($req->results);
            $prize  = new AllFunction();
            foreach ($winner as $key => $value) {
                //prize distribution 
                $prize->prizeDistribution($value->user_id,$value->kill,$value->winner,$req->tournament_id);
                if($value->winner == 1){
                    $result->winner_id = $value->user_id;
                    $users = UserInfo::where('user_id',$value->user_id)->get()->first();
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

}
