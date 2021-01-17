<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Functions\AllFunction;

class CheckMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Here we check membership of user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
            $user = User::where('membership',1)->get();
            $d = 0;
            if($user){
                foreach ($user as  $value) {
                    $date = date("y-m-d");
                    $exp_date = strtotime(".$value->Ex_date_membership.");
                    $this->info(strtotime(".$date."));
                    $this->info($exp_date);
                    if( $exp_date >= strtotime(".$date.")){
                        $value->membership = 0;
                        $value->Ex_date_membership = NULL;
                        if($value->update()){
                            $this->info($value);
                        }else{
                            $this->info("hello");
                        }
                        $d = $d+1;
                        $notifi = new AllFunction();
                        $notifi->sendNotification(array('id' => $value->id ,'title' => 'Membership Expired' , 'msg' => 'Your Membership Expired ','icon'=> 'gamepad'));
                    }
                }
            }
            // $this->info($user);
        // return 0;
    }
}
