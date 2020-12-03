<?php
    namespace App\Functions;
    use Mail;
    use App\Models\Tournament;
    use App\Models\User;
    use App\Models\UserInfo;
    use App\Models\Admin;
    use App\Models\Notification;
    use Exception;
    use Illuminate\Support\Facades\Http;

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
                    $message->from('groceryshop6387@gmail.com','Tournament App');
                });
                return $code;
                       
        }
        public function registerTournament($data){
            try{
                $new_tournament = new Tournament();
                $new_tournament->prize_pool = $data['prize_pool'];
                $new_tournament->winning = $data['winning'];
                $new_tournament->per_kill = $data['per_kill'];
                $new_tournament->entry_fee = $data['entry_fee'];
                $new_tournament->type = $data['type'];
                $new_tournament->map = $data['map'];
                $new_tournament->map = $data['tournament_name'];
                $new_tournament->img = $data['img'];
                $new_tournament->max_user_participated = $data['max_user_participated'];
                $new_tournament->game_type = $data['game_type'];
                $new_tournament->tournament_type = $data['tournament_type'];
                $new_tournament->tournament_name = $data['tournament_name'];
                $email = User::where('email' , auth()->user()->email)->get()->first();
                if($email){
                    $created_by = 'User';
                    $new_tournament->id = $email->id;
                }
                $record = Admin::where('email' , auth()->user()->email)->get()->first();
                if($record){
                    $created_by = 'Admin';
                }
                $new_tournament->created_by = $created_by;
                $new_tournament->tournament_start_date = $data['tournament_start_date'];
                $new_tournament->tournament_start_time = $data['tournament_start_time'];
                $new_tournament->save();
                return true;
            }catch(Exception $e){
                return false;
            }
        }

        public function prizeDistribution($id,$kill,$winner,$tournament_id){
           $users = UserInfo::where('user_id',$id)->get()->first();
           $tournament = Tournament::where('tournament_id',$tournament_id)->get()->first();
           $amount = $users->withdrawal_amount;
           if($winner == 1){
               $amount = $amount + $tournament->winning;
               $test = 1;
           }
           $amount = $amount + ($tournament->per_kill * $kill);
           $users->withdrawal_amount = $amount;
           $transaction = new Transaction();
            $transaction->user_id = $id;
            $transaction->reciept_id = Str::random(20);
            $transaction->amount = $amount;
           if($test == 1 ){
               $transaction->description = 'For Winning Tournament';
           }else{
            $transaction->description = 'For Tournament Reward';
           }
            $transaction->payment_id = Str::random(10);
            $transaction->action = 'Credit';
            $transaction->payment_done = 1;
            $transaction->save();
           $users->save();
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
                'Authorization'=>'AAAA6lNbeY8:APA91bEVvPfXHiOg8w40IoJ4WS-mBlPmtuv9sCGIeszjEY2Q6clbu91PHgL5MEng7JdCVAFcUAbS4EyyCVKHA6bFT2GpRN8V4H_qi2Lm_ytoPseWbnw17RvvA8hfNbEyj0xTTl8nXvOy'
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
                    $notification->message = $data['message'];
                    $notification->icon = $data['icon'];
                    $notification->save();
                    return true;
                }else{
                    return false;
                }
            
        }
}
            



?>