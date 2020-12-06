<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserInfo;
use App\Functions\AllFunction;
use App\Models\AppVersion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;
use Exception;


class LoginController extends Controller
{
    public function forgetOtp(Request $request){
        try{
            $valid = Validator::make($request->all(),[
                'mobile_no' => 'required'
            ]);
            if($valid->passes()){
            $data = User::where('mobile_no',$request->mobile_no)->get()->first();
            if($data){
                $sendsms = new AllFunction();
                $otp = $sendsms->sendSms($request->mobile_no);
                $data->reset_password_verify = $otp;
                $data->save();
                if($otp){
                    return response()->json([
                        'status' => true,
                        'msg' => 'OTP send successfully'
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => 'Something Went Wrong! try again'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter Registered Number'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'Something went wrong'
             ]);
        }
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),['name'=>'required','mobile_no'=>'required','email'=>'required','password'=>'required','gender'=>'required']);
        if($validator->passes()){
                try{
                    $already_exist = User::where('mobile_no',$request->mobile_no)->get()->first();
                    if($already_exist){
                        return response()->json([
                            'status' => false,
                            'msg' => 'You already registered'
                        ]);
                    }
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
                            $user_info->gender = $request->gender;
                            $user_info->user_current_version = AppVersion::orderby('id','desc')->get()->first()->short_version;
                            $user_info->save();
                            $code = $sendsms->referCode($new_user->id,$request->ref_code);
                            $user = UserInfo::where('user_id',$new_user->id)->get()->first();
                            if($user){
                                if($code == $request->ref_code){
                                    $user->ref_by = $code;
                                    $user->wallet_amount = 5;
                                    $user->save();
                                }
                            }
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
                    return array('status'=>false,'msg'=>'Entered Email Or Mobile No alredy registered.');
                }
            }else{
                return array('status'=>false,'msg'=>'Some Problem Occured');
            }
        
    }

    public function login(Request $req){
        $validator = Validator::make($req->all(),['mobile_no'=>'required','password'=>'required']);
        if($validator->passes()){
          try{
            $data = User::where('mobile_no',$req->mobile_no)->get()->first();
            if (Hash::check($req->password, $data->password)) {
                    $token = $data->createToken('my-app-token')->plainTextToken;
                    $user = UserInfo::where('user_id' , $data->id)->get()->first();
                    $user->notification_token = $req->notification_token;
                    $user->save();
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
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
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
        $validator = Validator::make($request->all(),['mobile_no'=>'required','otp'=>'required|numeric']);
        if($validator->passes()){
        try{
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
        }catch(Exception $e){
            return response()->json([
                'status' => false , 
                'msg' => 'Something Went Wrong'
                ]);
        }
    }else{
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->all()
            ]);
        }
    }

    public function resendOtp(Request $request){
        try{
            $valid = Validator::make($request->all(),[
                'mobile_no' => 'required'
            ]);
            if($valid->passes()){
            $data = User::where('mobile_no',$request->mobile_no)->get()->first();
            if($data){
                $sendsms = new AllFunction();
                $otp = $sendsms->sendSms($request->mobile_no);
                $data->verification_code = $otp;
                $data->save();
                if($otp){
                    return response()->json([
                        'status' => true,
                        'msg' => 'OTP send successfully'
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => 'Something Went Wrong! try again'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter Registered Number'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'Something went wrong'
             ]);
        }
    }

    // public function check(){
    //     $arr = '[{"name" : "sumit" ,"class" : "BTECH CSE" , "roll" : 50},{"name" : "sumit" ,"class" : "BTECH CSE" , "roll" : 50},{"name" : "sumit" ,"class" : "BTECH CSE" , "roll" : 50}]';
    //     $arr1 =json_decode($arr);
    //     foreach ($arr1 as $key => $value) {
    //         print_r($value);
    //         echo "<br>";
    //         echo $value->name;
    //         echo "<br>";
    //     }
        
    // }

}
