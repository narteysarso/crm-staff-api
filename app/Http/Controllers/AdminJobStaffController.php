<?php

namespace App\Http\Controllers;

use App\Job;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;

class AdminJobStaffController extends Controller
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

        $jobs = Job::where('company_id', $company->id)->first();

        if (!$jobs)
            return response()->json('unable to find job posting', 404);

        return response()->json(compact('jobs'), 200);
    }

    public function show(int $id)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $job = Job::where('id', $id)->where('company_id', $company->id)->first();

        if (!$job)
            return response()->json('unable to find job posting', 404);

        return response()->json(compact('job'), 200);


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
            'staff_id' => "integer|required|exists:staffs,id",
            'branch_id' => "integer|required|exists:branches,id",
            'group_id' => "integer|required|exists:groups,id",
            'role_id' => "integer|required|exists:roles,id",
            'reports_to' => "exists:staffs,id",
            'effective_date' => "required",
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $job = Job::create($credentials);

        if (!$job)
            return response()->json('unable to assign job posting', 500);

        //update current job
        $job_collection = Job::where('staff_id', $request->staff_id)->where('company_id', $company->id)->orderBy('effective_date', 'desc');
        $job_collection->update(['is_current' => 0]);
        $job = $job_collection->first();
        $job->is_current = 1;
        $job->save();

        $job->branch;
        $job->group;
        $job->role;
        $job->report_to;

        return response()->json(compact('job'), 200);
    }

    public function edit(Reqeust $request)
    {
        //

        // dd($request);
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $job = Job::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$job)
            return response()->json('unable to find job posting', 404);

        $this->validate($request, [
            'id' => "integer|required|exists:jobs,id",
            'staff_id' => "integer|required|exists:staffs,id",
            'branch_id' => "integer|required|exists:branches,id",
            'group_id' => "integer|required|exists:groups,id",
            'role_id' => "integer|required|exists:roles,id",
            'reports_to' => "exists:staffs,id",
            'effective_date' => "required",
        ]);

        //send files to resource manager

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;
        $result = $job->update($credentials);

        if (!$result)
            return response()->json('unable to update job posting', 500);

        return response()->json(compact('job'), 200);
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

        $job = Job::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$job)
            return response()->json('unable to find job posting', 404);

        $result = $job->delete();
        if (!$result)
            return response()->json('unable to delete job posting', 500);

        return response()->json("job posting successfully deleted", 200);
    }

    public function staffJob(int $id)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $jobs = Job::with('branch', 'group', 'role', 'report_to')->where('staff_id', $id)->where('company_id', $company->id)->orderBy('effective_date', 'desc')->get();

        if (!$jobs) {
            return response()->json('no jobs found', 404);
        }
        return response()->json(compact('jobs'), 200);
    }

}
