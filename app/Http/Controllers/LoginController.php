<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserInfo;
use App\Functions\AllFunction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function test(){
        echo Str::random(20);
    }

    public function resendOtp(Request $request){
        $sendsms = new AllFunction();
        $otp = $sendsms->sendSms($request->mobile_no);
        $data = User::where('mobile_no',$request->mobile_no)
                ->get()
                ->first();
            if(!$otp){
                return response()->json([
                    'status' =>false,
                    'msg' =>'OTP Not Sent. Try Again.'
                ]);
            }
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
                'msg' => 'OTP not send. Try Again!'
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
                    $referal = Str::random(6);
                    $token = Str::random(20);
                    $user_info = new UserInfo();
                    $user_info->user_id = $new_user->id;
                    $user_info->token = $token;
                    $user_info->refferal_code = $referal;
                    $user_info->save();
                    return array('status'=>true,'msg'=>'user registered successfully','token'=>$token,'otp'=>$new_user->verification_code);
                }else{
                    
                }
            }else{
                return array('status'=>false,'msg'=>'Some Problem Occured');
            }
            
        }catch(Exception $e){
            return array('status'=>false,'msg'=>'Some Error Occured');
        }
    }

    public function login(Request $req){
        $data = User::where('mobile_no',$req->mobile_no)->get()->first();
        if (Hash::check($req->password, $data->password)) {
            $user = Token::where('tokenable_id',$data->id)->get()->first();
                $token = $data->createToken('my-app-token')->plainTextToken;
            return response()->json([
                'status' => true,
                'msg' => 'Login Successfully',
                'token' => $token
        ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Credential are wrong',
        ]);
        }
        
    }
    public function user(){
        return response()->json([
            'status' =>true,
            'data'=>User::all()
        ]);
    }
}
