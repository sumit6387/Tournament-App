<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use App\Models\Transaction;
use Validator;
use Exception;

class PaymentController extends Controller
{
    private $razorpayId = "rzp_test_c1rvyv7xbgstcZ";
    private $razorpayKey = "im2eSBzk4Y51VTtKxgT1SK36";

    public function createPaymentOrder(Request $request){
        $valid = Validator::make($request->all(), ['amount' => 'required']);
        if($valid->passes()){
          try{
            $api = new Api($this->razorpayId, $this->razorpayKey);
            $reciept_id = Str::random(20);
            $order = $api->order->create(array(
                'receipt' => $reciept_id,
                'amount' => $request->amount * 100,
                'currency' => 'INR'
                )
            );
            $newTransaction = new Transaction();
            $newTransaction->user_id = auth()->user()->id;
            $newTransaction->reciept_id = $reciept_id;
            $newTransaction->amount = $request->amount;
            $newTransaction->payment_id = $order['id'];
            $newTransaction->save();

            return response()->json([
                'status' => true,
                'razorpayID' => $this->razorpayId,
                'orderID' => $order['id'],
                'amount' => $request->amount *100,
                'userID' => auth()->user()->id,
                'email' => auth()->user()->email,
                'contact' => auth()->user()->mobile_no
            ]);
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
}
