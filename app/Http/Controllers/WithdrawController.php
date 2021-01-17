<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Exception;
use Illuminate\Support\Str;
use App\Models\Withdraw;
use App\Models\UserInfo;
use App\Models\User;
use App\Functions\AllFunction;

class WithdrawController extends Controller
{
    public function withdraw(Request $request){
        try{
            $transaction_id = Str::random(9);
            // withdraw amount through UPI 
            if(strtoupper($request->mode) == 'UPI'){
                $valid = Validator::make($request->all(),['upi_id'=>'required','mode'=>'required']);
                if($valid->passes()){
                        $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                        if($userinfo->withdrawal_amount < 50){
                            return response()->json([
                                'status' => false,
                                'msg' => 'You have Minimum 50rs for Withdraw'
                            ]);
                        }
                        $withdraw = new Withdraw();
                        $withdraw->user_id = auth()->user()->id;
                        $withdraw->upi_id = $request->upi_id;
                        $withdraw->mode = $request->mode;
                        $withdraw->name = User::where('id',auth()->user()->id)->get()->first()->name;
                        $withdraw->transaction_id = $transaction_id;
                        $withdraw->amount = $userinfo->withdrawal_amount;
                        if($withdraw->save()){
                            $transaction = new AllFunction();
                            // save transaction for history
                            if($transaction->transaction($transaction_id,$userinfo->withdrawal_amount,'Withdraw Amount')){
                                $userinfo->withdrawal_amount = 0;
                                $userinfo->save();
                                return response()->json([
                                    'status' => true,
                                    'msg' => 'Your Payment Transfer Within 24 Hours In Your Acount'
                                ]);
                            }else{
                                return response()->json([
                                    'status' => false,
                                    'msg' => 'something went wrong'
                                ]);
                            }
                        }

                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => $valid->errors()->all()
                    ]);
                }
                // withdraw amount through PAYTM
            }else if(strtoupper($request->mode) == 'PAYTM'){
                $valid = Validator::make($request->all(),['paytm_no'=>'required|numeric','mode'=>'required']);
                if($valid->passes()){
                        $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                        if($userinfo->withdrawal_amount < 50){
                            return response()->json([
                                'status' => false,
                                'msg' => 'You have Minimum 50rs for Withdraw'
                            ]);
                        }
                        $withdraw = new Withdraw();
                        $withdraw->user_id = auth()->user()->id;
                        $withdraw->paytm_no = $request->paytm_no;
                        $withdraw->name = User::where('id',auth()->user()->id)->get()->first()->name;
                        $withdraw->mode = $request->mode;
                        $withdraw->name = User::where('id',auth()->user()->id)->get()->first()->name;
                        $withdraw->transaction_id = $transaction_id;
                        $withdraw->amount = $userinfo->withdrawal_amount;
                        if($withdraw->save()){
                            $transaction = new AllFunction();
                            // save the transaction for history
                            if($transaction->transaction($transaction_id,$userinfo->withdrawal_amount,'Withdraw Amount')){
                                $userinfo->withdrawal_amount = 0;
                                $userinfo->save();
                                return response()->json([
                                    'status' => true,
                                    'msg' => 'Your Payment Transfer Within 24 Hours In Your Acount'
                                ]);
                            }else{
                                return response()->json([
                                    'status' => false,
                                    'msg' => 'something went wrong'
                                ]);
                            }
                        }
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => $valid->errors()->all()
                    ]);
                }
                // withdraw amount through BANK
            }else if(strtoupper($request->mode) == 'BANK'){
                $valid = Validator::make($request->all(),['acount_no'=>'required|numeric','ifsc_code'=>'required','mode'=>'required','bank' => 'required']);
                if($valid->passes()){
                        $userinfo = UserInfo::where('user_id',auth()->user()->id)->get()->first();
                        if($userinfo->withdrawal_amount < 50){
                            return response()->json([
                                'status' => false,
                                'msg' => 'You have Minimum 50rs for Withdraw'
                            ]);
                        }
                        $withdraw = new Withdraw();
                        $withdraw->user_id = auth()->user()->id;
                        $withdraw->acount_no = $request->acount_no;
                        $withdraw->mode = $request->mode;
                        $withdraw->name = $request->name;
                        $withdraw->bank_name = $request->bank;
                        $withdraw->ifsc_code = $request->ifsc_code;
                        $withdraw->transaction_id = $transaction_id;
                        $withdraw->amount = $userinfo->withdrawal_amount;
                        if($withdraw->save()){
                            $transaction = new AllFunction();
                            // save the transaction for history
                            if($transaction->transaction($transaction_id,$userinfo->withdrawal_amount,'Withdraw Amount')){
                                $userinfo->withdrawal_amount = 0;
                                $userinfo->save();
                                return response()->json([
                                    'status' => true,
                                    'msg' => 'Your Payment Transfer Within 24 Hours In Your Acount'
                                ]);
                            }else{
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
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => $valid->errors()->all()
                    ]);
                }

            }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
    }


    public function withdrawDone($id){
        $withdraw = Withdraw::where('id',$id)->get()->first();
        if($withdraw){
            $withdraw->completed = 1;
            $withdraw->save();
            $notifi = new AllFunction();
            $data = $notifi->sendNotification(['id' => $withdraw->user_id , 'title'=> 'Transfer Amount' , 'msg' => 'Your Amount '.$withdraw->amount.' Withdraw To your account'  , 'icon' => 'money']);
            if($data){
                return response()->json([
                    'status' => true,
                    'msg' => 'Payment Done'
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Oops Something Went Wrong'
                ]);
            }
            
        }else{
            return response()->json([
                'status' => false,
                'msg' => 'Oops! SOmething Went Wrong'
            ]);
        }
    }
}
