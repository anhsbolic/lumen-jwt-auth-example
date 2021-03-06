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
            $this->jwtAuth->factory()->setTTL(60); // set JWT time to live in minutes

            if (! $token = $this->jwtAuth->attempt($request->only('email', 'password'))) {
                return $this->myResponse(404, false, [], "user not found");
            }
        } catch (JWTException $e) {
            return $this->myResponse($e->getStatusCode(), false, [], $e->getMessage());
        }

        // $this->jwtAuth->factory()->setTTL(1);
        return $this->myResponse(200, true, ["access_token" => $token], "login success");
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    { 
        return $this->myResponse(200, true, $this->jwtAuth->user(), "me");
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
