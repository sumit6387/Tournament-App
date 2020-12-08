<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Exception;
use Illuminate\Support\Str;
use App\Models\Withdraw;
use App\Models\UserInfo;

class WithdrawController extends Controller
{
    public function withdraw(Request $request){
        try{
            $transaction_id = Str::random(30);
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
                        $withdraw->transaction_id = $transaction_id;
                        $withdraw->amount = $userinfo->withdrawal_amount;
                        if($withdraw->save()){
                            $userinfo->withdrawal_amount = 0;
                            $userinfo->save();
                            return response()->json([
                                'status' => true,
                                'msg' => 'Your Payment Transfer Within 24 Hours In Your Acount'
                            ]);
                        }

                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => $valid->errors()->all()
                    ]);
                }
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
                        $withdraw->mode = $request->mode;
                        $withdraw->transaction_id = $transaction_id;
                        $withdraw->amount = $userinfo->withdrawal_amount;
                        if($withdraw->save()){
                            $userinfo->withdrawal_amount = 0;
                            $userinfo->save();
                            return response()->json([
                                'status' => true,
                                'msg' => 'Your Payment Transfer Within 24 Hours In Your Acount'
                            ]);
                        }
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => $valid->errors()->all()
                    ]);
                }
            }else if(strtoupper($request->mode) == 'BANK'){
                $valid = Validator::make($request->all(),['acount_no'=>'required|numeric','ifsc_code'=>'required','mode'=>'required']);
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
                        $withdraw->ifsc_code = $request->ifsc_code;
                        $withdraw->transaction_id = $transaction_id;
                        $withdraw->amount = $userinfo->withdrawal_amount;
                        if($withdraw->save()){
                            $userinfo->withdrawal_amount = 0;
                            $userinfo->save();
                            return response()->json([
                                'status' => true,
                                'msg' => 'Your Payment Transfer Within 24 Hours In Your Acount'
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
}