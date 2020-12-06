<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\AppVersion;
use App\Functions\AllFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{

    public function store(Request $request){
        $user_info = UserInfo::where('user_id' , auth()->user()->id)->get()->first();
        $user = User::where('id' , auth()->user()->id)->get()->first();
        if($request->file('image')){
            $filename = Str::random(15).".jpg";
            $path = $request->file('image')->move(public_path('/images/user_image'),$filename);
            $url = url('/images/user_image/'.$filename);
            $user_info->profile_image =$url;
        }

        if($request->name){
            $user->name = $request->name;
        }

        if($request->email){
            $user->email = $request->email;
        }

        if($request->state){
            $user_info->state = $request->state;
        }

        if($request->country){
            $user_info->country = $request->country;
        }

        if($request->mobile_no){
            $user->mobile_no = $request->mobile_no;
        }

        if($request->gender){
            $user_info->gender = $request->gender;
        }

        $user->update();
        $user_info->update();
        return response()->json([
            'status' => true,
            'msg' => 'profile updated'
        ]);
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
            $user = UserInfo::where('user_id',auth()->user()->id)->get()->first();
            if($user->wallet_amount < $tournament->entry_fee){
                return response()->json([
                    'status' => false,
                    'msg' => "Insufficient balance"
                ]);
            }
            $joined_user = $tournament->joined_user;
            if($joined_user == null){
                $joined_user = ''.auth()->user()->id;
            }else {
                $arr = explode(',',$joined_user);
                $resp = in_array(auth()->user()->id , $arr);
                if($resp){
                    return response()->json([
                        'status' => false,
                        'msg' => 'You Already Joined The Tournament'
                    ]);
                }
            $joined_user = $joined_user.','.auth()->user()->id;
            }
            $tournament1 = Tournament::where('tournament_id',$request->tournament_id)->update(['joined_user' => $joined_user]);
            $user->wallet_amount = $user->wallet_amount - $tournament->entry_fee;
            $user->ptr_reward = $user->ptr_reward + 2;
            $user->update();
            $transaction = new Transaction();
            $transaction->user_id = auth()->user()->id;
            $transaction->reciept_id = Str::random(20);
            $transaction->amount = $tournament->entry_fee;
            $transaction->description = 'For join the '.$tournament->game_type.' Tournament';
            $transaction->payment_id = Str::random(10);
            $transaction->action = 'D';
            $transaction->payment_done = 1;
            $transaction->save();

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
            'prize_pool' => 'required|numeric',
            'winning' => 'required|numeric',
            'per_kill' => 'required|numeric',
            'entry_fee' => 'required|numeric',
            'type' => 'required',
            'tournament_name' => 'required',
            'map' => 'required',
            'img' => 'required',
            'max_user_participated' => 'required',
            'game_type' => 'required',
            'tournament_type' => 'required',
            'tournament_start_date' => 'required',
            'tournament_start_time' => 'required'
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
          try{
            $tournament1 = Tournament::where(['tournament_id' => $request->tournament_id , 'created_by' => 'User','id'=>auth()->user()->id])->get()->first();
            $arr = explode(',',$tournament1->joined_user);
            $resp = count($arr);
            if($resp< (($tournament1->max_user_participated*50)/100)){
                return response()->json([
                    'status' => false,
                    'msg' => 'Minimum 50% user required for start the tournament'
                ]);
            }
            $tournament = Tournament::where(['tournament_id' => $request->tournament_id , 'created_by' => 'User','id'=>auth()->user()->id])->update(['user_id' => $request->user_id,'password' => $request->password]);
            if($tournament){
                $tournament = Tournament::where(['tournament_id' => $request->tournament_id , 'created_by' => 'User','id'=>auth()->user()->id])->get()->first();
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

    public function claimPrize(){
            try{
                $user = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                if($user->ptr_reward < 10){
                    return response()->json([
                        'status' => false,
                        'msg' => "Insufficient points"
                    ]);
                }else if($user->ptr_reward  >= 100){
                    $user->ptr_reward = $user->ptr_reward - 100;
                    $d2 = explode('T',strval(date('c', strtotime('30 days'))));
                    $users = User::where('id',auth()->user()->id)->get()->first();
                    $users->membership = 1;
                    $users->Ex_date_membership = $d2[0];
                    $amount = 149;
                    $users->save();
                }else if($user->ptr_reward  >= 40 && $user->ptr_reward < 100){
                    $user->ptr_reward = $user->ptr_reward - 40;
                    $user->wallet_amount = $user->wallet_amount + 25;
                    $amount = 25;
                }else if($user->ptr_reward  >= 20 && $user->ptr_reward < 40){
                    $user->ptr_reward = $user->ptr_reward - 20;
                    $user->wallet_amount = $user->wallet_amount + 12;
                    $amount = 12;
                }else if($user->ptr_reward  >= 10 && $user->ptr_reward < 20){
                    $user->ptr_reward = $user->ptr_reward - 10;
                    $user->wallet_amount = $user->wallet_amount + 5;
                    $amount = 5;
                }
                $user->save();
                $transaction = new Transaction();
                $transaction->user_id = auth()->user()->id;
                $transaction->reciept_id = Str::random(20);
                $transaction->amount = $amount;
                $transaction->description = 'For Claim The Prize';
                $transaction->payment_id = Str::random(10);
                $transaction->action = 'C';
                $transaction->payment_done = 1;
                $transaction->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'Your reward credited to your account'
                ]);
            }catch(Exception $e){
                return response()->json([
                    'status' => false,
                    'msg' => "Something Went Wrong"
                ]);
            }
        
    }

    public function changePassword(Request $request){
        $valid = Validator::make($request->all() , [
                'current_password' => 'required' ,
                'new_password' => 'required' , 
                'confirm_password' => 'required'
            ]);
            
            if($valid->passes()){
                $user  = User::where('id' , auth()->user()->id)->get()->first();
                if(Hash::check($request->current_password , $user->password)){
                    if($request->new_password == $request->confirm_password){
                        $user->password = Hash::make($request->new_password);
                        $user->save();
                        return response()->json([
                            'status' => true,
                            'msg' => 'Your Password Changed'
                        ]);
                    }else{
                        return response()->json([
                            'status' => false,
                            'msg' => 'Enter same password'
                        ]);
                    }
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => 'Enter current password right'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => $valid->errors()->all()
                ]);
            }
    }

    public function forgetPassword(Request $request){
        $valid = Validator::make($request->all(),[
            'mobile_no' => 'required',
            'otp' => 'required|numeric|min:4',
            'password' => 'required'
        ]);
        if($valid->passes()){
          try{
            $data = User::where('mobile_no',$request->mobile_no)->get()->first();
            if($data){
                if($data->reset_password_verify == $request->otp){
                    $data->password = Hash::make($request->password);
                    $data->save();
                    return response()->json([
                        'status' => true,
                        'msg' => 'Password Updated'
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
                    'msg' => 'Something Went Wrong'
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

    public function cancelMatch(Request $request){
        $valid = Validator::make($request->all(),['tournament_id' => 'required']);
        if($valid->passes()){
          try{
            $data = Tournament::where(['created_by' => 'User' , 'tournament_id' => $request->tournament_id , 'id' => auth()->user()->id])->get()->first();
            if($data){
                $users = explode(',',$data->joined_user);
                foreach ($users as $key => $value) {
                    $user = UserInfo::where('user_id' , $value)->get()->first();
                    $user->wallet_amount = $user->wallet_amount + $data->entry_fee;
                    $user->save();
                }
                $data->cancel = 1;
                $data->save();
                return response()->json([
                    'status' => true,
                    'msg' => 'Match is canceled'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Something Went Wrong'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'something went wrong'
            ]);
        }
    }else{
            return response()->json([
                'status' => false,
                'msg' => 'something went wrong'
            ]);
        }
    }

    

}
