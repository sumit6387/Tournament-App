<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AppVersion;
use App\Models\Game;
use App\Models\Membership;
use App\Models\Tournament;
use App\Models\History;
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
        $valid = Validator::make($request->all(), ['version' => 'required' , 'short_version' => 'required']);
        if($valid->passes()){
            try{
                $version = new AppVersion();
                $version->version = $request->version;
                $version->short_version = $request->short_version;
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

    public function addMembership(Request $request){
        $valid = Validator::make($request->all() , [
            'rs'=> 'required',
            'time' => 'required'
          ]);
          if($valid->passes()){
            try{
                    $membership = new Membership();
                    $membership->rs = $request->rs;
                    $membership->time = $request->time;
                    $membership->save();
                    return response()->json([
                        'status' => true,
                        'msg' => 'Membership Added'
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
            'tournament_start_at' => 'required'
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
            $tournament = Tournament::where('tournament_id' , $request->tournament_id)->update(['user_id' => $request->user_id,'password' => $request->password]);
            if($tournament){
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
        $data = Tournament::where('tournament_id',$req->tournament_id)->update(['completed' => 1]);
        
        if($data){
            $data1 = Tournament::where('tournament_id',$req->tournament_id)->get()->first();
            $history = new History();
            $history->tournament_id  = $data1->tournament_id;
            $history->prize_pool  = $data1->prize_pool;
            $history->winning  = $data1->winning;
            $history->per_kill  = $data1->per_kill;
            $history->entry_fee  = $data1->entry_fee;
            $history->type  = $data1->type;
            $history->map  = $data1->map;
            $history->completed  = $data1->completed;
            $history->joined_user  = $data1->joined_user;
            $history->max_user_participated  = $data1->max_user_participated;
            $history->game_type  = $data1->game_type;
            $history->tournament_type  = $data1->tournament_type; 
            $history->created_by  = $data1->created_by; 
            $history->id  = $data1->id; 
            $history->tournament_start_at  = $data1->tournament_start_at; 
            $history->save();
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
        
    }

}
