<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserInfo;
use App\Models\AppVersion;

class CheckVersion
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
        $s = $request->route()->getPrefix();
        $pref = explode('/',$s);

        $currentprefix = UserInfo::where('user_id',auth()->user()->id)->get()->first();
        if($currentprefix->user_current_version != $pref[1]){
            return response()->json([
                'status' => false,
                'msg' => 'You are using old version',
                'app_link' => AppVersion::orderby('id','desc')->get()->first()->app_link
            ]);
        }
        return $next($request);
    }
}
