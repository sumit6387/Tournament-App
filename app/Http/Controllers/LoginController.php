<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserInfo;
use App\Functions\AllFunction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;


class LoginController extends Controller
{
    public function resendOtp(Request $request){
        $sendsms = new AllFunction();
        $otp = $sendsms->sendSms($request->mobile_no);
        $data = User::where('mobile_no',$request->mobile_no)
                ->get()
                ->first();
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
                'msg' => $validator->errors()->all()
            ]);
        }
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),['name'=>'required','mobile_no'=>'required','email'=>'required','password'=>'required']);
        if($validator->passes()){
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
                            $image = "https://ui-avatars.com/api/?name=".$new_user->name;
                            $referal = Str::random(6);
                            $user_info = new UserInfo();
                            $user_info->user_id = $new_user->id;
                            $user_info->refferal_code = $referal;
                            $user_info->profile_image = $image;
                            $user_info->save();
                            return response()->json(array('status'=>true,'msg'=>'user registered successfully','otp'=>$new_user->verification_code));
                        }else{
                            return response()->json([

                                'status' => false,
                                'msg' => 'Some Problem Occured'
                            ]);
                        }
                    }else{
                        return array('status'=>false,'msg'=>'Some Problem Occured');
                    }
                    
                }catch(Exception $e){
                    return array('status'=>false,'msg'=>'Some Error Occured');
                }
            }else{
                return array('status'=>false,'msg'=>'Some Problem Occured');
            }
        
    }

    public function login(Request $req){
        $validator = Validator::make($req->all(),['mobile_no'=>'required']);
        if($validator->passes()){
            $data = User::where('mobile_no',$req->mobile_no)->get()->first();
            if (Hash::check($req->password, $data->password)) {
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
        }else{
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->all(),
                ]);
            }
    }

    public function verifyOtp(Request $request){
        $validator = Validator::make($request->all(),['mobile_no'=>'required','otp'=>'required']);
        if($validator->passes()){
            $user = User::where('mobile_no',$request->mobile_no)
            ->where('verification_code' , $request->otp)
            ->get()
            ->first();
            if(!$user){
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter Right Credentials'
                ]); 
            }
            if($user->verified== 1){
                return response()->json([
                    'status' => false,
                    'msg' => 'You Are Already Verified'
                ]);
            }
            if($user){
                $user->verified = '1';
                $user->update();
                return response()->json([
                    'status' => true,
                    'msg' => 'OTP Verified Successfully'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter Valid OTP'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->all()
            ]);
        }
    }
}
