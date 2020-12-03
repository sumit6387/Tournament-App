<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('id-password.{id}', function ($user, $id) {
    $tournament = App\Models\Tournament::where('tournament_id',$id)->get()->first();
    $users = explode(',',$tournament->joined_user);
    foreach ($users as $key => $value) {
        if($value == $user->id){
            return true;
        }
    }
});
