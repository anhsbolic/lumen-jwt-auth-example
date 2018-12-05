<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{
    /**
     * Global Variable
     * 
     * @var Tymon\JWTAuth\JWTAuth
     * @var Tymon\JWTAuth\JWT
     */
    protected $jwtAuth;
    protected $jwt;

    /**
     * Constructor.
     *
     * @param  Tymon\JWTAuth\JWTAuth  $jwtAuth
     * @param  Tymon\JWTAuth\JWT $jwt
     *
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth, JWT $jwt)
    {
        $this->jwtAuth = $jwtAuth;
        $this->jwt = $jwt;
    }

    /**
     * Attempt to authenticate the user and return the token.
     *
     * @param  array  $credentials
     *
     * @return false|string
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (! $token = $this->jwtAuth->attempt($request->only('email', 'password'))) {
                return $this->myResponse(404, false, [], "user not found");
            }
        } catch (JWTException $e) {
            return $this->myResponse($e->getStatusCode(), false, [], $e->getMessage());
        }

        // $this->jwtAuth->factory()->setTTL(1);
        return $this->myResponse(200, true, ["access_token" => $token, "expire_in" => $this->jwtAuth->factory()->getTTL()], "login success");
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    { 
        $token = json_decode($request->token);
        $token_refreshed = $token->refreshed;
        $access_token = $token->access_token;
        
        if ($token_refreshed) {
            $code = 400;
            $status = false;
            $data = $access_token;
            $msg = "token refreshed";
        } else {
            $code = 200;
            $status = true;
            $data = $this->jwtAuth->user();
            $msg = "me";
        }

        return $this->myResponse($code, $status, $data, $msg);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout($forceForever = false)
    {
        $this->jwtAuth->invalidate($forceForever);

        $this->jwtAuth->user = null;
        $this->jwtAuth->unsetToken();

        return $this->myResponse(200, true, [], "logout success");
    }

    /**
     * Response Template.
     *
     * @param  string $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function myResponse($statusCode = 500, $success = false, $data = [], $message = "")
    {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }
}
