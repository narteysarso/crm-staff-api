<?php

namespace App\Http\Controllers;

use App\Wage;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;

class AdminWageStaffController extends Controller
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
    public function index(){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);
        
        $wages = Wage::where('company_id',$company->id)->get();

        if(!$wages)
            return response()->json('no wages found', 404);

        return response()->json(compact('wages'),200);
    }

    public function show(int $id){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $wage = Wage::where('id',$id)->where('company_id', $company->id)->first();

        if (!$wage)
            return response()->json('wage not found', 404);
        
        $wage->staff;
        $wage->frequency;
        $wage->paytype;
        
        return response()->json(compact('wage'), 200);
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
            'staff_id' => 'integer|required|exists:staffs,id',
            'frequency_id' => 'required|exists:frequencies,id',
            'paytype_id' => 'required|exists:paytypes,id',
            'amount' => 'required',
            'effective_date' => 'required',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $wage = Wage::create($credentials);

        if(!$wage)
            return response()->json('unable to create wage', 500);

        return response()->json(compact('wage'), 200);
    }

    public function edit(Request $request){
        //

        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $this->validate($request, [
            'id' => 'integer|required:exists:wages,id',
            'staff_id' => 'integer|required|exists:staffs,id',
            'frequency_id' => 'required|exists:frequencies,id',
            'paytype_id' => 'required|exists:paytypes,id',
            'amount' => 'required',
            'effective_date' => 'required',
        ]);

        $wage = Wage::where('id', $id)->where('company_id', $company->id)->first();

        if (!$wage)
            return response()->json('no wage found', 404);

        $result = $wage->update($credentials);

        if (!$result)
            return response()->json('unable to update wage', 500);

        return response()->json(compact('wage'), 200);
    }

    public function delete(Request $request){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $wage = Wage::where('id', $id)->where('company_id', $company->id)->first();

        if (!$wage)
            return response()->json('no wage found', 404);

        $result = $wage->update($credentials);

        if (!$result)
            return response()->json('unable to delete wage', 500);

        return response()->json("wage successfully deleted", 200);
    }
}
