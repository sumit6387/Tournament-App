<?php
    namespace App\Functions;
    use Mail;
    class AllFunction{
        public function sendSms($number){
            $curl = curl_init();
                $num = rand(0000,9999);
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







        }
            



?>