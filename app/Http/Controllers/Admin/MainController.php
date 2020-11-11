<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AppVersion;
use App\Models\Game;
use App\Models\Membership;
use Validator;
use Exception;
use Illuminate\Support\Str;

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
}
