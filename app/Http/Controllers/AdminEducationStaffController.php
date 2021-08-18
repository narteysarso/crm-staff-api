<?php

namespace App\Http\Controllers;

use App\Education;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;


class AdminEducationStaffController extends Controller
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

        $educations = Education::where('company_id', $company->id)->get();
        if (!$educations)
            return response()->json('no educations info found', 404);

        return response()->json(compact('educations'), 200);
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

        $education_collection = Education::where('staff_id', $id)->where('company_id', $company->id);
        $education_count = $education_collection->count();
        $educations = $education_collection->skip($offset)->take(10)->get();

        if (!$educations)
            return response()->json('educations not found', 404);


        return response()->json(compact('educations', 'education_count'), 200);
    }

    public function showDetailed(int $id)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $education_collection = Education::with('staff')->where('staff_id', $id)->where('company_id', $company->id);
        $education_count = $education_collection->count();
        $educations = $education_collection->skip($offset)->take(10)->get();

        if (!$educations)
            return response()->json('education not found', 404);


        return response()->json(compact('education', 'education_count'), 200);
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
            'institution' => 'string|required',
            'cert_type' => "string|required",
            'start_date' => 'date_format:Y-m-d|required',
            'end_date' => 'date_format:Y-m-d|required',
            'score' => "string"
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $education = Education::create($credentials);

        if (!$education)
            return response()->json('unable to start education', 500);

        $education->staff;

        return response()->json(compact('education'));
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

        $education = Education::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$education)
            return response()->json('education not found', 404);

        $this->validate($request, [
            'staff_id' => 'integer|required|max:255',
            'institution' => 'string|required',
            'cert_type' => "string|required",
            'start_date' => 'date_format:Y-m-d|required',
            'end_date' => 'date_format:Y-m-d|required',
            'score' => "string"
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $result = $education->update($credentials);

        if (!$result)
            return response()->json('unable to start education', 500);

        $education->staff;

        return response()->json(compact('education'));
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

        $education = Education::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$education)
            return response()->json('education not found', 404);

        $result = $education->delete();

        if (!$result)
            return response()->json('unable to delete education assignment', 500);

        return response()->json("education successfully deleted", 200);
    }
}
