<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AdminShowController extends Controller
{
    public function showAnnouncement(){
        $data = Announcement::orderby('ann_id','desc')->get();
        if($data->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Nothing to Show'
            ]);
        }
    }
}
