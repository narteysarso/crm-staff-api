<?php

namespace App\Http\Controllers;

use App\Emergency;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;


class AdminEmergencyStaffController extends Controller
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

    public function index()
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $emergencies = Emergency::where('company_id', $company->id)->get();

        if (!$emergencies)
            return response()->json('no emergency contacts found', 404);

        return response()->json(compact($emergencies), 200);
    }

    public function show(int $id, int $offset = 0)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $emergency_collection = Emergency::with('relation')->where('staff_id', $id)->where('company_id', $company->id);
        $emergency_count = $emergency_collection->count();
        $emergencies = $emergency_collection->skip($offset)->take(15)->get();

        if (!$emergencies)
            return response()->json('no emergency contact found', 404);

        return response()->json(compact('emergencies', 'emergency_count'), 200);
    }

    public function create(Request $request)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $this->validate($request, [
            'name' => 'string|required|max:255',
            'relation_id' => 'integer|required|exists:relations,id',
            'staff_id' => "integer|required|exists:staffs,id",
            'workplace' => 'string',
            'home' => 'string',
            'mobile' => 'string',
            'email' => 'email|string',
            'address' => 'string',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $emergency = Emergency::create($credentials);

        if (!$emergency)
            return respones()->json('unable to create emergency contact', 500);

        $emergency->relation;

        return response()->json(compact('emergency'), 200);

    }

    public function edit(Request $request)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $emergency = Emergency::where('id', $request->id)->where('company_id', $company->id) > first();

        if (!$emergency)
            return response()->json('emergency contact not found', 404);

        $this->validate($request, [
            'name' => 'string|required|max:255',
            'relation' => 'string',
            'staff_id' => "integer|required|exists:staffs,id",
            'workplace' => 'string',
            'home' => 'string',
            'mobile' => 'string',
            'email' => 'email|string',
            'address' => 'string',
        ]);


        $result = $emergency->update($request->all());

        if (!$result)
            return respones()->json('unable to update emergency contact', 500);

        return response()->json(compact('emergency'), 200);
    }

    public function delete(Request $request)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);



        $emergency = Emergency::where('id', $request->id)->where('company_id', $company->id)->first();

        if (!$emergency)
            return response()->json('contact not found');

        $result = $emergency->delete();

        if (!$result)
            return respones()->json('unable to delete contact', 500);

        return response()->json("contact deleted successfully", 200);
    }
}
