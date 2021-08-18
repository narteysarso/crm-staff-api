<?php

namespace App\Http\Controllers;

use App\Role;
use Tymon\JWTAuth\JWTAuth;


class AdminRoleController extends Controller
{
    protected $jwt;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $jwt)
    {
        //
        $this->jwt = $jwt;
    }
    //

    public function index()
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $roles = Role::all();

        if (!$roles)
            return response()->json("roles not found", 404);

        return response()->json(compact('roles'), 200);
    }
}
