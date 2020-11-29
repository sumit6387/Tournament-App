<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tournament;
class CheckTournament
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $membership = User::where('id',auth()->user()->id)->get()->first();
        if($membership->membership != 1){
            $tournament = Tournament::where(['created_by'=>'User','id'=>auth()->user()->id,'completed' => 0])->get();
            if($tournament->count() >= 1){
                return response()->json([
                    'status' => false,
                    'msg' => 'You can not make more than one tournament if you want then take membership'
                ]);
            }
        }
        return $next($request);
    }
}
