<?php

namespace App\Http\Controllers;

use App\EmployeeStatus;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;

class AdminEmployeeStatusController extends Controller
{

    protected $jwt;
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct(JWTAuth $jwt){
        //
        $this->jwt = $jwt;
    }

    public function index(){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $employeestatuses = EmployeeStatus::where('company_id', $company->id)->get();

        if (!$employeestatuses)
            return response()->json('no employee statuses found', 404);

        return response()->json(compact('employeestatuses'), 200);
    }

    public function show(int $id){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $employeestatus = EmployeeStatus::where('id',$id)->where('company_id', $company->id)->first();

        if (!$employeestatus)
            return response()->json('employee status not found', 404);

        return response()->json(compact('employeestatus'), 200);
    }

    public function create(Request $request){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $this->validate($request,[
            'staff_id' => 'integer|exists:staffs,id',
            'employee_type_id' => 'integer|exists:employmenttypes,id',
            'comment' => 'string',
            'effective_date' => 'required',
        ]);

        $credentials = $request->only(['staff_id','employee_type_id','comment','effective_date']);
        $credentials['company_id'] = $company->id;

        $employeestatus = EmployeeStatus::create($credentials);

        if (!$employeestatus)
            return response()->json('unable to create employee status', 404);

        return response()->json(compact('employeestatus'), 200);
    }

    public function edit(Request $request){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $employeestatus = EmployeeStatus::where('id', $request->id)->where('company_id', $company->id)->first();

        $this->validate($request, [
            'staff_id' => 'integer|required|exists:staffs,id',
            'employee_type_id' => 'integer|required|exists:employmenttypes,id',
            'comment' => 'string',
            'effective_date' => 'required',
        ]);

        $credentials = $request->only(['staff_id', 'employee_type_id', 'comment', 'effective_date']);
        $credentials['company_id'] = $company->id;

        $result = $employeestatus->update($credentials);

        if (!$result)
            return response()->json('unable to update employee status', 404);

        return response()->json(compact('employeestatus'), 200);

    }

    public function delete(Request $request){
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $employeestatus = EmployeeStatus::where('id', $request->id)->where('company_id', $company->id)->first();

        $result = $employeestatus->delete();

        if (!$result)
            return response()->json('employee status', 404);

        return response()->json(compact('employeestatus'), 200);
    }
    
}
