<?php

namespace App\Http\Middleware;

use Closure;

class JwtRoutesApi
{
    /**
     * Handle an incoming request.
     * 
     * 1. token not refreshed => pass request to controller
     * 2. token refreshed => stop request, send refreshed token to client
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = json_decode($request->token);
        $token_refreshed = $token->refreshed;
        $access_token = $token->access_token;
        
        if ($token_refreshed) {
            $response = response()->json([
                'success' => false,
                'data' => ['access_token' => $access_token],
                'message' => "token refreshed"
            ], 400);
        } else {
            $response = $next($request);
        }

        return $response;
    }
}
