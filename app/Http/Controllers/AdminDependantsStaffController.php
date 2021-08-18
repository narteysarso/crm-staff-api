<?php

namespace App\Http\Controllers;

use App\Dependant;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;


class AdminDependantsStaffController extends Controller
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
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $dependants = Dependant::where('company_id', $company->id)->get();
        if (!$dependants)
            return response()->json('no dependants info found', 404);

        return response()->json(compact('dependants'), 200);
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

        $dependant_collection = Dependant::with('relation')->where('staff_id', $id)->where('company_id', $company->id);
        $dependant_count = $dependant_collection->count();
        $dependants = $dependant_collection->skip($offset)->take(15)->get();

        if (!$dependants)
            return response()->json('dependant not found', 404);


        return response()->json(compact('dependants', 'dependant_count'), 200);
    }

    public function showDetailed(int $id, int $offset = 0)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $dependant_collection = Dependant::with('staff', 'relation')->where('id', $id)->where('company_id', $company->id);
        $dependant_count = $dependant_collection->count();
        $dependants = $dependant_collectioni->skip($offset)->take(6)->get();

        if (!$dependants)
            return response()->json('dependant not found', 404);


        return response()->json(compact('dependants', 'dependant_count'), 200);
    }

    public function create(Request $request)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $this->validate($request, [
            'staff_id' => 'integer|required|max:255',
            'name' => 'string|required',
            'gender' => "string|required|max:1",
            'dob' => 'date_format:Y-m-d|required',
            'relation_id' => 'integer|required|exists:relations,id',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $dependant = Dependant::create($credentials);

        if (!$dependant)
            return response()->json('unable to start dependant', 500);

        $dependant->relation;

        return response()->json(compact('dependant'));
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

        $dependant = Depenedant::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$dependant)
            return response()->json('dependant not found', 404);

        $this->validate($request, [
            'staff_id' => 'integer|required|max:255',
            'name' => 'string|required',
            'gender' => "string|required|max:1",
            'dob' => 'date_format:Y-m-d|required',
            'relation_id' => 'integer|required|exists:relations,id',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $result = $dependant->update($credentials);

        if (!$result)
            return response()->json('unable to start dependant', 500);

        $dependant->staff;

        return response()->json(compact('dependant'));
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

        $dependant = Dependant::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$dependant)
            return response()->json('dependant not found', 404);

        $result = $dependant->delete();

        if (!$result)
            return response()->json('unable to delete dependant assignment', 500);

        return response()->json("dependant successfully deleted", 200);
    }
}
