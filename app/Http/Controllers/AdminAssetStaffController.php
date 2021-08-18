<?php

namespace App\Http\Controllers;

use App\AssetStaff;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;


class AdminAssetStaffController extends Controller
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

        $assets = AssetStaff::where('company_id', $company->id)->get();
        if (!$assets)
            return response()->json('asset to staff assignments not found', 404);

        return response()->json(compact('assets'), 200);
    }

    public function show(int $id){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $asset = AssetStaff::where('id',$id)->where('company_id',$company->id)->first();
        if(!$asset)
            return response()->json('asset to staff assignment not found', 404);
        
        $asset->staff;

        return response()->json(compact('asset'), 200);
    }

    public function create(Request $request){
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $this->validate($request, [
            'name' => 'string|required|max:255',
            'description' => 'string',
            'staff_id' => "integer|required|exists:staffs,id",
            'serial_num' => 'string',
            'assigned_date' => 'required',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $asset = AssetStaff::create($credentials);

        if(!$asset)
            return response()->json('unable to assign asset to staff', 500);

        return response()->json(compact('asset'));
    }

    public function edit(Request $request){
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $asset = AssetStaff::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$asset)
            return response()->json('asset to staff assignment not found', 404);

        $this->validate($request, [
            'name' => 'string|required|max:255',
            'description' => 'string',
            'staff_id' => "integer|required|exists:staffs,id",
            'serial_num' => 'string',
            'assigned_date' => 'required',
        ]);

        $credentials = $request->all();
        $credentials['company_id'] = $company->id;

        $result = $asset->update($credentials);

        if (!$result)
            return response()->json('unable to update asset to staff assignment', 500);

        return response()->json(compact('asset'));
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

        $asset = AssetStaff::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$asset)
            return response()->json('asset to staff assignment not found', 404);

        $result = $asset->delete();

        if (!$result)
            return response()->json('unable to delete asset to staff assignment', 500);

        return response()->json("asset to staff assignment successfully deleted",200);
    }
}
