<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Functions\AllFunction;
use Validator;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

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
        $valid = Validator::make($request->all(),['email' => 'required' , 'password' => 'required', 'secret_key' => 'required']);
        if($valid->passes()){
            $admin = Admin::where('email', $request->email)->where('secret_key', $request->secret_key)->get()->first();
            if(Hash::check($request->password, $admin->password)){
                $token = $admin->createToken('my-app-token')->plainTextToken;
                $email = new AllFunction();
                $code = $email->sendEmail($request->email);
                $admin->email_verification_code = $code;
                if($admin->update()){
                    return response()->json([
                        'status' => true,
                        'token' => $token
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => 'Some Problem Occur'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Incorrect Password'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function verifyOtp(Request $request){
        $valid = Validator::make($request->all() , ['email' => 'required' , 'otp' => 'required']);
        if($valid->passes()){
            $user = Admin::where(['email'=>$request->email , 'email_verification_code' => $request->otp])->get()->first();
            if($user){
                return response()->json([
                    'status' => true,
                    'msg' => 'OTP verified'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter Right OTP'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function resendOtp(Request $request){
        $valid = Validator::make($request->all() , ['email' => 'required']);
        if($valid->passes()){
            $admin = Admin::where('email' , $request->email)->get()->first();
            if($admin){
                $email = new AllFunction();
                $otp = $email->sendEmail($request->email);
                $admin->email_verification_code = $otp;
                if($admin->update()){
                    return response()->json([
                        'status' => true,
                        'msg' => 'OTP Send Successfully'
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => 'Some Problem Occur'
                    ]);
                }                
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Try Again'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function user(){
        return auth()->user();
    }
}
