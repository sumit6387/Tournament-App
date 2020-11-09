<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
class CheckAdmin
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
        $email = auth()->user()->email;
        $user = Admin::where('email',$email)->get()->first();
        if(!$user){
            return response()->json([
                'status' => false,
                'msg' => 'Something went wrong'
            ]);
        }
        return $next($request);
    }
}
