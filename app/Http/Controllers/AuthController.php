<?php

namespace App\Http\Controllers;

use App\Company;
use App\Staff;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;



class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'string|required',
        ]);

        try {

            if (!$token = Auth::guard('staff')->attempt($request->only('email', 'password'))) {
                return response()->json("login failed", 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }

        return response()->json(compact('token'));
    }


    public function logout()
    {
        Auth::guard('staff')->logout();
        return response(['server message' => 'logged out']);
    }

    public function refresh()
    {
        $token = $this->jwt->refresh();
        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json($this->jwt->user());
    }

    public function getCurrentToken()
    {
        $token = $this->jwt->getToken()->get();
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->jwt->factory()->getTTL() * 60,
        ]);
    }

}