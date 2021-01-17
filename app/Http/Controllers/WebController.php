<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\NewsLetter;
use App\Models\Feedback;
use App\Models\AppVersion;
use Validator;

class WebController extends Controller
{
    public function index(){
      $data['feedback'] = Feedback::select(['feedback.*','user_info.profile_image as img'])->orderby('feedback.id' , 'desc')->join('user_info','feedback.user_id','=','user_info.user_id')->take(5)->get();

      $data['link'] = AppVersion::orderby('id','desc')->get()->first()->app_link;
      
      return view('index',$data);
    }


    public function contact(Request $request){
      $valid = Validator::make($request->all() , ["name" => 'required' , "subject" => "required" , "message" => "required","email" => "required"]);
      if($valid->passes()){
          $new = new Contact();
          $new->name = $request->name;
          $new->subject = $request->subject;
          $new->email = $request->email;
          $new->message = $request->message;
          $new->save();
        return response()->json([
            'status' => true
        ]);
      } else{
        return response()->json([
            'status' => false,
            "msg" => $valid->errors()->all()
        ]);
      }
    }

    public function newsletter(Request $request){
      $valid = Validator::make($request->all() , ["email" => "required" ]);
      if($valid->passes()){
        $newsletter = new NewsLetter();
        $newsletter->email = $request->email;
        $newsletter->save();
        return response()->json([
          'status' => true
        ]);
      }else{
        return response()->json([
          'status' => false,
          "msg" => $valid->errors()->all()
        ]);
      }
    }
}
