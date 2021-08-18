<?php

namespace App\Http\Controllers;

use App\User;
use App\Company;
use App\Staff;
use App\Status;
use App\Permission;
use App\PermissionStaff;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class StaffsController extends Controller
{

    protected $jwt;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->jwt = Auth::guard('staff');
    }

    /**
     * Retrieves list of all staff from company staffs table
     * NB:// $user represent both staff and company owner
     * @return json response
     */
    public function index()
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
        return response()->json('company not found', 404);
        $staffs = $company->staffs;

        //find staff according to allowed branches, departments/groups, and role
        $branches = [];
        foreach ($this->getBranchAccess($user) as $branch) {
            array_push($branches, $branch->id); //append to list of allowed branches
        }
        $departments = [];
        foreach ($this->getDepartmentAccess($user, $branches) as $department) {
            array_push($departments, $department->id); //append to list of allowed departments
        }

        $staffs = $staffs->whereIn('branch_id', $branches);

        return response()->json(compact('staffs'), 200);
    }

    public function create(Request $request)
    {
        $user = Auth::guard('staff')->user();

        if (!$user->hasPermission('add staff'))
        return response()->json('Unauthorized', 401);

        $company = $user->company;

        $this->validate($request, [
            'name' => 'string|required|max:255',
            'email' => "email|required|unique:staffs",
            'phone' => 'unique:staffs'
        ]);

        $details = $request->except('password');
        $details['company_id'] = $company->id;
        $original_string = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $original_string = implode("", $original_string);
        $pass = substr(str_shuffle($original_string), 0, 10);

        $details['password'] = Hash::make($pass);

        $staff = Staff::create($details);

        if (!$staff)
        return response()->json(null);

        // \Queue::push(new ProcessNewStaffMail(array("companyname" => $company->name, "companyurl" => $company->urlname, "username" => $user->firstname . " " . $user->lastname, "useremail" => $user->email, "pass" => $pass, "to_email" => $staff->email)));

        return response()->json(compact('staff'), 200);
    }

    public function edit(Request $request)
    {
        //
        $user = Auth::guard('staff')->user();

        //
        if (!$user->hasPermission('edit staff'))
        return response()->json('Unauthorized', 401);

        $company = $user->company;

        $staff = Staff::where('id', $request->id)->where('company_id', $company->id)->where('branch_id', $user->branch_id)->first();
        if (!$staff)
        return response()->json(['staff not found'], 404);
        $validator = [];
        if (isset($request->name) && $request->name !== $staff->name)
        $validator['name'] = 'string|required';

        if (isset($request->email) && $request->email !== $staff->email)
        $validator['email'] = 'email|required|unique:users';

        if (isset($request->phone) && $request->phone !== $staff->phone)
        $validator['phone'] = 'unique:users';


        $this->validate($request, $validator);

        if (isset($request->name))
        $staff->name = $request->name;
        if (isset($request->email))
        $staff->email = $request->email;
        if (isset($request->phone))
        $staff->phone = $request->phone;
        if (isset($request->ice))
        $staff->ice = $request->ice;
        if (isset($request->residence))
        $staff->residence = $request->residence;
        if (isset($request->password))
        $staff->password = app('hash')->make($request->password);


        $result = $staff->save();
        if (!$result)
        return response()->json(['failed to edit staff'], 500);

        return response()->json(compact('staff'), 200);
    }


    public function delete(Request $request)
    {
        //
        $user = Auth::guard('staff')->user();

        $this->validate($request, [
            'id' => 'required|integer'
        ]);

        if (!$user->hasPermission('remove staff') || $user->id === $request->id)
        return response()->json('Unauthorized', 401);


        $company = $user->company;

        $staff = Staff::where('id', $id)->where('company_id', $request->id)->where('branch_id', $user->branch_id)->first();

        $result = $staff->delete();

        if (!$result)
        return response()->json(['failed to delete staff'], 500);

        return response()->json(['staff deleted successfully'], 204);
    }

    public function updatepassword(Request $request)
    {
        $user = $this->jwt->user();
        if (!$user)
        return response()->json('user not found', 404);

        $this->validate($request, [
            'oldpassword' => 'required',
            'confpassword' => 'required',
            'newpassword' => 'required',
        ]);

        $valid = $this->jwt->attempt(array('email' => $user->email, 'password' => $request->oldpassword));

        if (!$valid)
        return response()->json('invalid old password', 401);

        $user->password = Hash::make($request->newpassword);

        $result = $user->save();

        if (!$result)
        return response()->json('unable to change user password', 404);

        return response()->json(compact('user'), 200);
    }

    public function getBranchAccess($staff)
    {
        return DB::table('access_staff')
            ->join('access_levels', 'access_levels.id', '=', 'access_staff.access_id')
            ->join('access_branch', 'access_levels.id', '=', 'access_branch.access_id')
            ->join('branches', 'access_branch.branch_id', '=', 'branches.id')
            ->select('branches.id')->where('access_staff.staff_id', $staff->id)->get();
    }

    public function getDepartmentAccess($staff, $branchesArray)
    {
        return DB::table('access_staff')
            ->join('access_levels', 'access_levels.id', '=', 'access_staff.access_id')
            ->join('access_branch', 'access_levels.id', '=', 'access_branch.access_id')
            ->join('branches', 'access_branch.branch_id', '=', 'branches.id')
            ->join('groups', 'access_group.group_id', '=', 'groups.id')
            ->select('groups.id')->where('access_staff.staff_id', $staff->id)->whereIn("branches.id", $branchesArray)->get();
    }

    public function profile()
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $user->profileurl = env('RESCSERV') . "get/profile?image=" . $user->profileurl;
        return response()->json(compact('user'), 200);
    }

    public function currentBranch()
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $branch = $user->branch();

        if (!$branch)
        return response()->json('staff not posted to any branch', 404);

        return response()->json(compact('branch'), 200);
    }

    public function currentGroup()
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $group = $user->group();

        if (!$group)
        return response()->json('staff not posted to any group', 404);

        return response()->json(compact('group'), 200);
    }

    public function currentRole()
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $role = $user->Role();

        if (!$role)
        return response()->json('staff not posted to any role', 404);

        return response()->json(compact('role'), 200);
    }

    public function branchCoWorkers(int $offset = 0)
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $coworkers_collection = $user->branchCoWorkers();
        $coworker_count = $coworkers_collection->count();
        $coworkers = $coworkers_collection->skip($offset)->take(20)->get(['firstname', 'lastname', 'email', 'mobile', 'staffs.id', 'profileurl']);

        if (!$coworkers)
        return response()->json('coworkers not found', 404);

        foreach ($coworkers as $coworker) {
            # code...
            $coworker['model'] = 'App\Staff';
        }
        return response()->json(compact('coworkers', 'coworker_count'), 200);
    }

    public function groupCoWorkers(int $offset = 0)
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $coworkers_collection = $user->groupCoWorkers();
        $coworker_count = $coworkers_collection->count();
        $coworkers = $coworkers_collection->skip($offset)->take(20)->get(['firstname', 'lastname', 'email', 'mobile', 'staffs.id']);

        if (!$coworkers)
        return response()->json('coworkers not found', 404);

        return response()->json(compact('coworkers', 'coworker_count'), 200);
    }

    public function roleCoWorkers(int $offset = 0)
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $coworkers_collection = $user->roleCoWorkers();
        $coworker_count = $coworkers_collection->count();
        $coworkers = $coworkers_collection->skip($offset)->take(20);

        if (!$coworkers)
        return response()->json('coworkers not found', 404);

        return response()->json(compact('coworkers', 'coworker_count'), 200);
    }

    /**
     * Retrieves list of all staff from company staffs table
     * NB:// $user represent both staff and company owner
     * @return json response
     */
    public function birthday(Request $request)
    {
        $user = Auth::guard('staff')->user();

        if (!$user)
        return response()->json('staff not found', 404);

        $gen_collection = $user->branchCoWorkers()->get(['firstname', 'lastname', 'email', 'mobile', 'staffs.id', 'profileurl']);

        $staff_collection = $gen_collection->filter(function ($staff) {
            $date = Date('m', strtotime($staff->dob));
            $now = Date('m');
            return ((($date >= $now) && ($date <= ($now + 4))));
        });

        $staff_count = $staff_collection->count();
        $staffs = ($staff_count == 0) ? $staff_collection->chunk(10) : $staff_collection->chunk(10)[0];

        if (!$staffs) {
            return response()->json('no staff found', 404);
        }


        return response()->json(compact('staffs', 'staff_count'), 200);
    }

    public function auth(Request $request)
    {
        $user = $this->jwt->user();

        if (!$user)
        return response()->json('user not found', 404);
        $user = $user->first(['id', 'firstname', 'lastname', 'email', 'mobile']);
        $user->branch = $user->branch()->first(['id', 'company_id']);
        $user->expr = env('JWT_TTL');

        return response()->json(compact('user'), 200);
    }
}
