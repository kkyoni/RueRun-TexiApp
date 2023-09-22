<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Http\RedirectResponse;

class activeUserCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
     public function handle($request, Closure $next)
    {
        $headers = apache_request_headers();
        // $request->headers->set('Authorization', $headers['Authorization']);
        $user = JWTAuth::parseToken()->authenticate();
        if($user->status == "active"){
            return $next($request);
        }else{
            return response()->json(['status' => 'success','message' => 'User is blocked by admin'],402);
            // return response()->json([
            //         'status' => 'user_inactive',
            //         'code' => 402,
            //         'message' => 'User is blocked by admin',
            //     ]);            
        }
        
        
    }
}
