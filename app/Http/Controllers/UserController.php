<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Tournament;
use Validator;
use App\Functions\AllFunction;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function user(){
        try{
            return response()->json([
                'status' =>true,
                'data'=>User::all()
            ]);
        }catch(RouteNotFoundException $e){
            return $e;
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),['image'=>'required']);
        if($validator->passes()){
            $filename = Str::random(15).".jpg";
            $path = $request->file('image')->move(public_path('/images/user image'),$filename);
            $url = url('/images/user image/'.$filename);
            $user_info = UserInfo::where('user_id' , auth()->user()->id)->get()->first();
            $user_info->profile_image = $url;
            if($user_info->update()){
                return response()->json([
                    'status' =>true,
                    'path' => $url
                ]);
            }else{
                return response()->json([
                    'status' =>false,
                    'msg' => 'Some Problem Occured'
                ]);
            }
        }else{
            return response()->json([
                'status' =>false,
                'msg' => $validator->errors()->all()
            ]);
        }
    }

    public function applyRefCode(Request $req){
        $user = UserInfo::where('user_id',auth()->user()->id)->get()->first();
        if($user){
            $user->ref_by = $req->ref_code;
            if($user->update()){
                return response()->json([
                    'status' =>true,
                    'msg' => 'Reffer Code Apply'
                ]);
            }else{
                return response()->json([
                    'status' =>false,
                    'msg' => 'Some Problem Occur'
                ]);
            }
        }else{
            return response()->json([
                'status' =>false,
                'msg' => 'Some Problem Occur'
            ]);
        }
        
    }

    public function joinTournament(Request $request){
        try{
            $tournament = Tournament::where('tournament_id',$request->tournament_id)->get()->first();
            $joined_user = $tournament->joined_user;
            if($joined_user == null){
                $joined_user = '';
            }else {
                $arr = explode(',',$joined_user);
                $resp = in_array(auth()->user()->id , $arr);
                if($resp){
                    return response()->json([
                        'status' => false,
                        'msg' => 'You Already Joined The Tournament'
                    ]);
                }
            }
            $joined_user = $joined_user.','.auth()->user()->id;
            $tournament = Tournament::where('tournament_id',$request->tournament_id)->update(['joined_user' => $joined_user]);
            return response()->json([
                'status' => true,
                'msg' => 'You Joined The Tournament'
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
    }

    public function createUserTournament(Request $request){
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
                    'status' => false,
                    'msg' => 'Something went wrong'
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

    public function updatePassword(Request $request){
        $valid = Validator::make($request->all(),['tournament_id' => 'required','user_id' => 'required' , 'password' => 'required']);
        if($valid->passes()){
            $tournament = Tournament::where(['tournament_id' => $request->tournament_id , 'created_by' => 'User','id'=>auth()->user()->id])->update(['user_id' => $request->user_id,'password' => $request->password]);
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
    }

}
