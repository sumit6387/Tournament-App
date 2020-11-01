<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Functions\AllFunction;
use Session;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function test(){
        echo Str::random(20);
    }

    public function resendOtp(Request $request){
        $sendsms = new AllFunction();
        $otp = $sendsms->sendSms($request->mobile_no);
        $data = User::where('mobile_no',$request->mobile_no)->get()->first();
        $data->verification_code = $otp;
        $data1 = $data->save();
        if($data1 == 1){
            return response()->json([
                'status' => true,
                'otp' => $otp,
                'msg' => 'OTP resend successfully'
            ]);
        }else{
            return response()->json([
                'status' => false , 
                'msg' => 'Some Error occured'
            ]);
        }
    }

    public function register(Request $request){
        try{
            $new_user = new User();
            $sendsms = new AllFunction();
            $new_user->name = $request->name;
            $new_user->email = $request->email;
            $new_user->mobile_no = $request->mobile_no;
            $new_user->password = Hash::make($request->password);
            $otp = $sendsms->sendSms($request->mobile_no);
            $new_user->verification_code = $otp;
            if($otp){
                if($new_user->save()==1){
                    $token = Str::random(20);
                    return array('status'=>true,'msg'=>'user registered successfully','token'=>$token,'otp'=>$new_user->verification_code);
                }else{
                    
                }
            }else{
                return array('status'=>false,'msg'=>'some problem occured');
            }
            
        }catch(Exception $e){
            return array('status'=>false,'msg'=>'Some error occured');
        }
    }

    public function login(Request $req){
        $data = User::where('mobile_no',$req->mobile_no)->get()->first();
        if (Hash::check($req->password, $data->password)) {
            return response()->json([
                'msg' => 'Login Successfully',
                'parmanently_token' => Str::random(30)
        ]);
        }
        
    }
}
