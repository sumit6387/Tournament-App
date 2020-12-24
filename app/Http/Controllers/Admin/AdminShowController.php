<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Tournament;
use App\Models\Withdraw;
use App\Models\AppVersion;

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

    public function showTournaments(){
        $tournament = Tournament::where(['completed'=> 0 , 'created_by'=>'Admin','cancel'=> 0])->get();
        if($tournament->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $tournament
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Add Tournaments'
            ]);
        }
    }

    public function withdraw(){
        $withdraw_record = Withdraw::where('completed',0)->get();
        if($withdraw_record->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $withdraw_record
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'No Records'
            ]);
        }
    }

    public function versions(){
        $versions = AppVersion::orderby('id','desc')->get();
        return response()->json([
            'status' => true,
            'data' => $versions
        ]);
    }
}
