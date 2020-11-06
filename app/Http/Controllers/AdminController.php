<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Functions\AllFunction;
use Validator;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function register(Request $request){
        $valid = Validator::make($request->all(),['name'=>'required','email'=>'required','password'=>'required']);
        if($valid->passes()){
            $user = new Admin();
            $secret_key = 'AkhilFarhanSumit';
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->secret_key = $secret_key;
            if($user->save()){
                return response()->json([
                    'status' =>true , 
                    'msg' => 'Registered successfully'
                  ]);
            }else{
                return response()->json([
                    'status' =>false , 
                    'msg' => 'Some Error Occur'
                 ]);
            }
        }else{
            return response()->json([
                'status' =>false , 
                'msg' => $valid->errors()->all()
             ]);
        }
        
    }

    public function login(Request $request){
        
    }
}
