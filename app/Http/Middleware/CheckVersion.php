<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        $currentprefix = AppVersion::orderBy('id','desc')->get()->first();
        if($currentprefix->short_version != $pref[1]){
            return response()->json([
                'status' => false,
                'msg' => 'You are using old version',
                'app_link' => $currentprefix->app_link
            ]);
        }
        return $next($request);
    }
}
