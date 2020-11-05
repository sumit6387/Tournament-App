<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserInfo;
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
            $url = url('/images/user_image/'.$filename);
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
}
