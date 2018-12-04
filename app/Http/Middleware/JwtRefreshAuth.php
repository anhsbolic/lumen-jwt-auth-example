<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JwtRefreshAuth
{
    /**
     *
     * @var Tymon\JWTAuth\JWTAuth $jwtAuth
     */
    protected $jwtAuth;

    /**
     * Create a new middleware instance.
     *
     * @param  Tymon\JWTAuth\JWTAuth $jwtAuth
     * 
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // get token
        try {
            $this->jwtAuth->parseToken();
            $token = $this->jwtAuth->getToken();
        } catch (JWTException $e) {
            // Token missing or badly formatted
            abort(401, "Unauthorized");
        }

        // verify token
        try {
            // save user on request object
            $request->user = $this->jwtAuth->authenticate($token);
        } catch (TokenBlacklistedException $e) {
            // token blacklisted
            abort(401, "Unauthorized");
        } catch (TokenExpiredException $e) {
            // token expired : should check if expired token was blacklisted or not
            try {
                // refresh token
                try {
                    $token = $this->jwtAuth->refresh($token);
                    $this->jwtAuth->setToken($token);    
    
                    // Authenticate with new token, save user on request
                    $request->user = $this->jwtAuth->authenticate($token);

                } catch(TokenExpiredException $e) {
                    // failed refresh token (token still expired)
                    abort(401, "Unauthorized");
                }
            } catch (TokenBlacklistedException $e) {
                // token blacklisted 
                abort(401, "Unauthorized");
            }
        }

        // next to request handler when token verified (with new refresh token when available)
        return $next($request);
    }
}