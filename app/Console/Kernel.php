<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\User;
use App\Functions\AllFunction;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $user = User::get();
            if($value->count() > 0){
                foreach ($user as $key => $value) {
                    if($value->Ex_date_membership >= date('y-m-d')){
                        $value->membership = 0;
                        $value->Ex_date_membership = null;
                        $value->save();
                        $notifi = new AllFunction();
                        $notifi->sendNotification(array('id' => $value->id ,'title' => 'Membership Expired' , 'msg' => 'Your Membership Expired ','icon'=> 'gamepad'));
                    }
                }
            }
            
        })->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
