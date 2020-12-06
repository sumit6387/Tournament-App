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
        $s = $request->fullurl();
        $pref = explode('/',$s);

        $currentprefix = AppVersion::orderby('id','desc')->get()->first();
        if($currentprefix->short_version != $pref[4]){
            return response()->json([
                'status' => false,
                'update' => false,
                'msg' => 'You are using old version',
                'app_link' => $currentprefix->app_link
            ]);
        }
        return $next($request);
    }
}
