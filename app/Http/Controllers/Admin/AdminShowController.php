<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Tournament;
use App\Models\Withdraw;
use App\Models\AppVersion;
use App\Models\User;
use App\Models\UserName;
use App\Models\ludoTournament;
use App\Models\Transaction;
use App\Models\Complaint;
use App\Models\UserInfo;
use App\Models\History;
use App\Models\LudoResult;
use App\Functions\AllFunction;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

    public function showTournamentsUser(){
        $tournaments = Tournament::orderby('id','desc')->where(['created_by'=>'User','completed'=> 0 ,'cancel' => 0])->get();
        return response()->json([
            'status' => true,
            'data' => $tournaments
        ]);
    }

    public function complete($id){
        $tournament = Tournament::where('tournament_id',$id)->get()->first();
        if($tournament->count() > 0){
            if($tournament->joined_user != null){
                $arr = explode(',',$tournament->joined_user);
                $data = array();
                for ($i=0; $i < count($arr); $i++) { 
                    $user = User::select('name')->where('id',$arr[$i])->get()->first();
                    $data1 = UserName::select('pubg_username','pubg_user_id','user_id')->where(['user_id' => $arr[$i] , 'tournament_id' => $id])->get()->first();
                    $user->user_id = $data1->user_id;
                    $user->pubg_username = $data1->pubg_username;
                    $user->pubg_user_id = $data1->pubg_user_id;
                    array_push($data,$user);
                }
                return response()->json([
                    'status' => true,
                    'data' => $data
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => 'You have No Users To complete the Tournament'
                ]);
            }
        }
    }

    public function indexData(){
        $publicTournament = Tournament::where('tournament_type','public')->get()->count();
        $todayPublicTournament = Tournament::where(['tournament_type'=>'public','created_at'=> Carbon::today()])->get()->count();
        $privateTournament = Tournament::where('tournament_type','private')->get()->count();
        $todayPrivateTournament = Tournament::where(['tournament_type'=>'private','created_at'=> Carbon::today()])->get()->count();
        $completedTournamentPublic = Tournament::where(['completed'=>1 , 'tournament_type'=>'public'])->get()->count();
        $todayCompletedTournamentPublic = Tournament::where(['completed'=>1, 'tournament_type'=>'public','created_at'=> Carbon::today()])->get()->count();
        $completedTournamentPrivate = Tournament::where(['completed'=>1 , 'tournament_type'=>'private'])->get()->count();
        $todayCompletedTournamentPrivate = Tournament::where(['completed'=>1, 'tournament_type'=>'private','created_at'=> Carbon::today()])->get()->count();
        $totalUsers = User::get()->count();
        $allTransactions = Transaction::where('razorpay_id','!=',null)->where('payment_done',1)->get()->count();
        $users = User::select(['users.*','user_info.profile_image as img','user_info.withdrawal_amount','user_info.wallet_amount'])->orderby('users.id' , 'desc')->join('user_info','users.id','=','user_info.user_id')->take(5)->get();
        $data = array('totalPublic' => $publicTournament , 'todayPublic' =>$todayPublicTournament,'totalPrivate'=>$privateTournament,'todayPrivate' => $todayPrivateTournament , 'totalCompletedPublic' =>$completedTournamentPublic,'todayCompletedPublic'=>$todayCompletedTournamentPublic , 'completedTournamentPrivate' => $completedTournamentPrivate , 'todayCompletedTournamentPrivate'=>$todayCompletedTournamentPrivate,'totalUser' => $totalUsers,'totalTransactions' => $allTransactions,'users' => $users);
        return response()->json([
            'status'=> true,
            'data' => $data
        ]);
    }

    public function editAnnouncement($id){
        $data = Announcement::where('ann_id',$id)->get()->first();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function editVersion($v_id){
        $version = AppVersion::where('id' , $v_id)->get()->first();
        return response()->json([
            'status' => true,
            'data' => $version
        ]);
    }

    public function tournamentsHistory(){
        $data = Tournament::where('completed',1)->orwhere('cancel',1)->get();
        if($data->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }
    }

    public function showComplaints(){
        $complaints = Complaint::select(['complaints.*' , 'users.name'])->where('complaints.status',0)->join('users','complaints.user_id','=','users.id')->get();
        if($complaints->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $complaints
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'No Records Found'
            ]);
        }  
    }

    public function users(){
        $user = User::select(['users.*','user_info.profile_image as img','user_info.withdrawal_amount','user_info.wallet_amount','user_info.ptr_reward'])->orderby('users.id' , 'desc')->join('user_info','users.id','=','user_info.user_id')->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    public function ludoTournament(){
        $ludotournament = LudoTournament::select(['ludo_tournament.*','ludo_tournament.id as ludoID','completedtournamentResults.*'])
                            ->orderby('ludo_tournament.id' , 'desc')->where(['ludo_tournament.completed'=>0,'ludo_tournament.cancel'=>0,'completedtournamentResults.status'=>0])->where('completedtournamentResults.winner','!=', null)
                            ->whereDate('ludo_tournament.created_at', Carbon::today())
                            ->orwhere('completedtournamentResults.error1','!=', null)
                            ->join('completedtournamentResults','ludo_tournament.id','=','completedtournamentResults.tournament_id')->get();
        return response()->json([
            'status' => true,
            'data' => $ludotournament
        ]);
    }
    
    public function ludoResult($tournament_id){
        $tournamentDetail = ludoTournament::select(['ludo_tournament.user1','ludo_tournament.user2','completedtournamentResults.img1','completedtournamentResults.img2','completedtournamentResults.winner','completedtournamentResults.looser1','completedtournamentResults.error1'])->where('ludo_tournament.id',$tournament_id)->join('completedtournamentResults','ludo_tournament.id','=','completedtournamentResults.tournament_id')->get()->first();
        if($tournamentDetail){
            $user1 = json_decode($tournamentDetail->user1);
            $user2 = json_decode($tournamentDetail->user2);
            $tournamentDetail->username1 = $user1[0]->username;
            $tournamentDetail->username2 = $user2[0]->username;
            $tournamentDetail->user_id1 = $user1[0]->user_id;
            $tournamentDetail->user_id2 = $user2[0]->user_id;
            return response()->json([
                'status' => true,
                "data" => $tournamentDetail
            ]);
        }
    }

    public function updateLudoResult(Request $request){
        $tournament = ludoTournament::where('id',$request->tournament_id)->get()->first();
        if($tournament){
            $user = UserInfo::where('user_id',$request->user_id)->get()->first();
            if($user){
                $user->withdrawal_amount = $user->withdrawal_amount + $tournament->winning;
                $user->save();
                
                //send notification to winner user
                $notifi = new AllFunction();
                $notifi->sendNotification(['id' => $request->user_id , 'title' => 'Room ID Updated' , 'msg' => 'You win the ludo tournament.'.$tournament->winning.' added to your account.' , 'icon' => 'money']);

                // transactions
                $this->insertTransaction($request->user_id,$tournament->winning,"For Winning Ludo Tournament","C");
                History::where(['tournament_id' => $request->tournament_id,'user_id' => $request->user_id,'game' => 'ludo'])->update(["status" => "past"]);
                $user = json_decode($tournament->user1);
                $user1 = json_decode($tournament->user2);
        
                if($user[0]->user_id != $request->user_id){
                    History::where(['tournament_id' => $request->tournament_id,'user_id' => $user[0]->user_id,'game' => 'ludo'])->update(["status" => "past"]);
                    $result = LudoResult::where('tournament_id',$request->tournament_id)->get()->first();
                    if($result){
                        $result->winner = $request->user_id;
                        $result->looser1 = $user[0]->user_id;
                        $result->looser2 = null;
                        $result->error1 = null;
                        $result->error2 = null;
                        $result->img2 = null;
                        $result->status = 1;
                    }
                }
                if($user1[0]->user_id != $request->user_id){
                    History::where(['tournament_id' => $request->tournament_id,'user_id' => $user1[0]->user_id,'game' => 'ludo'])->update(["status" => "past"]);
                    $result = LudoResult::where('tournament_id',$request->tournament_id)->get()->first();
                    if($result){
                        $result->winner = $request->user_id;
                        $result->looser1 = $user1[0]->user_id;
                        $result->looser2 = null;
                        $result->error1 = null;
                        $result->error2 = null;
                        $result->img2 = null;
                        $result->status = 1;
                    }
                }
                $tournament->completed = 1;
                $tournament->save();
                $result->save();
                return response()->json([
                    'status' => true,
                    "msg" => "Result Updated"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "User's Info Not Found"
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => "Something Went Wrong"
            ]);
        }
    }

    public function distributeAmount(Request $request){
        $tournament = ludoTournament::where('id',$request->tournament_id)->get()->first();
        $result = LudoResult::where('tournament_id',$request->tournament_id)->get()->first();
        if($tournament && $result){
            $user = json_decode($tournament->user1);
            $user1 = json_decode($tournament->user2);
            $amount = ($tournament->winning*5)/100;
            $amount = ($amount*50)/100;
            // for user1
            $user3 = UserInfo::where('user_id',$user[0]->user_id)->get()->first();
            $user3->withdrawal_amount = $user3->withdrawal_amount + $amount;
            $user3->save();
            $notifi = new AllFunction();
            $notifi->sendNotification(['id' => $user[0]->user_id , 'title' => 'Result Update' , 'msg' => 'Your updated result are wrong so we distribute the prize.'.$amount.' added to your account.' , 'icon' => 'money']);

            // transactions
            $this->insertTransaction($user[0]->user_id,$amount,"For Winning Ludo Tournament","C");
            History::where(['tournament_id' => $request->tournament_id,'user_id' => $user[0]->user_id,'game' => 'ludo'])->update(["status" => "past"]);

            // for user2
            $user4 = UserInfo::where('user_id',$user1[0]->user_id)->get()->first();
            $user4->withdrawal_amount = $user4->withdrawal_amount + $amount;
            $user4->save();

            $notifi->sendNotification(['id' => $user1[0]->user_id , 'title' => 'Result Update' , 'msg' => 'Your updated result are wrong so we distribute the prize.'.$amount.' added to your account.' , 'icon' => 'money']);

            // transactions
            $this->insertTransaction($user1[0]->user_id,$amount,"For Winning Ludo Tournament","C");
            History::where(['tournament_id' => $request->tournament_id,'user_id' => $user1[0]->user_id,'game' => 'ludo'])->update(["status" => "past"]);
            $tournament->cancel = 1;
            $tournament->save();
            $result->status = 1;
            $result->error1 = null;
            $result->save();

            return response()->json([
                'status' => true,
                'msg' => "Prize Distributed"
            ]);

        }else{
            return response()->json([
                'status' => false,
                'mdg' => "Something Went Wrong"
            ]);
        }
    }

    private function insertTransaction($user_id,$amount,$desc,$action){
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->reciept_id = Str::random(10);
        $transaction->amount = $amount;
        $transaction->description = $desc;
        $transaction->action = $action;
        $transaction->payment_id = Str::random(10);
        $transaction->payment_done = 1;
        $transaction->save();
    }

}
