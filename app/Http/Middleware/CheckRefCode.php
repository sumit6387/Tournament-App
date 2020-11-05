<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserInfo;
use App\Models\User;

class CheckRefCode
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
        $ref = UserInfo::where('refferal_code',$request->ref_code)->get()->first();
        $valid = UserInfo::where('user_id',auth()->user()->id)->get()->first();
        if(!$ref){
            return response()->json([
                'status' => false,
                'msg' => 'Enter Correct Refferal Code'
            ]);
        }
        if($valid->refferal_code == $request->ref_code){
            return response()->json([
                'status' => false,
                'msg' => 'You used own Refferal Code'
            ]);
        }
        if($valid->ref_by != null){
            return response()->json([
                'status' => false,
                'msg' => 'You Already used Referal Code'
            ]);
        }
        return $next($request);
    }
}
