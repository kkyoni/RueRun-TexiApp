<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Models\User;

class JwtMiddleware extends BaseMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $tokens = str_replace('Bearer ',"", $request->header('Authorization'));
            if($user->login_token != $tokens){
                return response()->json(['status' => 'error','message' => 'You are login Someone other device'],402);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'status' => 'token_error',
                    'message' => 'Token is Invalid',
                    ],402);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'status' => 'token_error',
                    'message' => 'Token is Invalid',
                    ],402);
            }else{
                return response()->json([
                    'status'  => 'token_error',
                    'message' => 'Authorization Token not found'
                    ],402);
            }
        }
        return $next($request);
    }
}
