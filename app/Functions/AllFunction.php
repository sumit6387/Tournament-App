<?php
    namespace App\Functions;
    use Mail;
    use App\Models\Tournament;
    use App\Models\User;
    use App\Models\UserInfo;
    use App\Models\Admin;
    use App\Models\Notification;
    use App\Models\Transaction;
    use Exception;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;



    class AllFunction{
        public function sendSms($number){
            $curl = curl_init();
                $num = rand(1111,9999);
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://www.fast2sms.com/dev/bulk?authorization=RSaTvUIdq4hYsyBmKPCAXJQx3ONteorj2Lu9bM6nlVF7G10ZHfkeiYorX5FvEwL3K2WZxAdUChmc9DyI&sender_id=FSTSMS&message=".urlencode('This is your OTP for Tournament App '.$num)."&language=english&route=p&numbers=".urlencode($number),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "cache-control: no-cache"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    return '';
                } else {
                    if($response){
                        return $num;
                    }else{
                        return '';
                    }
                }

            }

            public function sendEmail($email){
                $code = rand(1111,9999);
                $to_name = 'User';
                $to_email = $email;
                $data = ['code'=> $code];
               $status =  Mail::send('emails.verificationEmail', $data, function($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)
                    ->subject('Email Verification of Tournament App');
                    $message->from('funtoos456@gmail.com','Tournament App');
                });
                return $code;
                       
        }
        public function registerTournament($data){
            try{
                $tour_no = Tournament::get()->count();
                $tour_no = $tour_no+1;
                if($tour_no < 10){
                    $tour_id = '0000'.$tour_no;
                }else if($tour_no < 100){
                    $tour_id = '000'.$tour_no;
                }else if($tour_no < 1000){
                    $tour_id = '00'.$tour_no;
                }else if($tour_no < 10000){
                    $tour_id = '0'.$tour_no;
                }else{
                    $tour_id = $tour_no;
                }
                
                $new_tournament = new Tournament();
                $new_tournament->prize_pool = $data['prize_pool'];
                $new_tournament->tour_id = $tour_id;
                $new_tournament->winning = $data['winning'];
                $new_tournament->per_kill = $data['per_kill'];
                $new_tournament->entry_fee = $data['entry_fee'];
                $new_tournament->type = $data['type'];
                $new_tournament->maps = $data['maps'];
                $new_tournament->tournament_name = $data['tournament_name'];
                $new_tournament->img = $data['img'];
                $new_tournament->max_user_participated = $data['max_user_participated'];
                $new_tournament->game_type = $data['game_type'];
                $new_tournament->tournament_type = $data['tournament_type'];
                $new_tournament->tournament_name = $data['tournament_name'];
                
                if (Auth::check()){
                    $email = User::where('email' , auth()->user()->email)->get()->first();
                    if($email){
                        $created_by = 'User';
                        $new_tournament->id = $email->id;
                    }
                }else{
                        $created_by = 'Admin';
                }
                $new_tournament->created_by = $created_by;
                $new_tournament->tournament_start_date = $data['tournament_start_date'];
                $new_tournament->tournament_start_time = $data['tournament_start_time'];
                if($new_tournament->save()){
                    return true;
                }else{
                    return false;
                }
            }catch(Exception $e){
                return response()->json([
                    'status' => false,
                    'msg' => 'Opps! Something Went Wrong'
                ]);
            }
        }

        public function prizeDistribution($id,$kill,$winner,$tournament_id){
           $users = UserInfo::where('user_id',$id)->get()->first();
           $tournament = Tournament::where('tournament_id',$tournament_id)->get()->first();
           $amount = $users->withdrawal_amount;
           $test = 0;
           $winn = 0;
           if($winner == 1 && $tournament->type == 'solo'){
               $amount = $amount + $tournament->winning;
               $test = 1;
               $winn = $winn+$tournament->winning;
           }else if($winner == 1 && $tournament->type == 'duo'){
                $amount = $amount + ($tournament->winning*50)/100;
                $test = 1;
                $winn = $winn+($tournament->winning*50)/100;
           }else if($winner == 1 && $tournament->type == 'squad'){
               $amount = $amount + ($tournament->winning*25)/100;
               $test = 1;
               $winn = $winn+($tournament->winning*25)/100;
           }
           $amount = $amount + ($tournament->per_kill * $kill);
           $winn = $winn+($tournament->per_kill * $kill);
           $users->withdrawal_amount = $amount;
             if($kill > 0){
                    $transaction = new Transaction();
                    $transaction->user_id = $id;
                    $transaction->reciept_id = Str::random(20);
                    $transaction->amount = $winn;
                if($test == 1 ){
                    $transaction->description = 'For Winning Tournament';
                }else{
                    $transaction->description = 'For Tournament Reward';
                }
                    $transaction->payment_id = Str::random(10);
                    $transaction->action = 'C';
                    $transaction->payment_done = 1;
                    $transaction->save();
             }
            $users->save();
            if($kill > 0){
                $data = $this->sendNotification(['id' => $id , 'title' => 'Tournament Prize' , 'msg' => 'You participated in match.so You won the '.$winn.' rs.' , 'icon' => 'money']);
                if($data){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }

        public function referCode($id,$ref_code){
            $ref = UserInfo::where('refferal_code',$ref_code)->get()->first();
            $valid = UserInfo::where('user_id',$id)->get()->first();
            if($ref){
                if($valid->refferal_code != $ref_code){
                    if($valid->ref_by == null){
                        return $ref_code;
                    }else{
                        return null;
                    }
                }else{
                    return null;
                }
            }else{
                return null;
            }
            
            
        }

        public function sendNotification($data){
            $user = UserInfo::where('user_id' , $data['id'])->get()->first();
            $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization'=>'key=AAAA6lNbeY8:APA91bEVvPfXHiOg8w40IoJ4WS-mBlPmtuv9sCGIeszjEY2Q6clbu91PHgL5MEng7JdCVAFcUAbS4EyyCVKHA6bFT2GpRN8V4H_qi2Lm_ytoPseWbnw17RvvA8hfNbEyj0xTTl8nXvOy'
                ])->post('https://fcm.googleapis.com/fcm/send',[
                      'data' => [
                        'title' => $data['title'],
                        'message' => $data['msg']
                      ],
                        'to' => $user->notification_token
                ]);
                if($resp->status() == 200){
                    $notification =new Notification();
                    $notification->user_id = $data['id'];
                    $notification->title = $data['title'];
                    $notification->message = $data['msg'];
                    $notification->icon = $data['icon'];
                    $notification->save();
                    return true;
                }else{
                    return false;
                }
            
        }

        public function transaction($rec_id,$amount,$desc){
           try{
            $trans = new Transaction();
            $trans->user_id = auth()->user()->id;
            $trans->reciept_id = $rec_id;
            $trans->amount = $amount;
            $trans->payment_id = Str::random(12);
            $trans->description = $desc;
            $trans->action = 'W';
            $trans->payment_done = 1;
            if($trans->save()){
                return true;
            }else{
                return false;
            }
          }catch(Exception $e){
             return false;
          }
        }
}
            



?>