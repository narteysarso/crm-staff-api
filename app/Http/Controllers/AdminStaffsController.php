<?php

namespace App\Http\Controllers;

use App\User;
use App\Company;
use App\Staff;
use App\Status;
use App\Job;
use App\Relation;
use App\Permission;
use App\PermissionStaff;
use App\Jobs\StaffInvitationMail;
use App\Mail\IVMailer;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

// use Illuminate\Support\Facades\Mail;


class AdminStaffsController extends Controller
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

    /**
     * Retrieves list of all staff from company staffs table
     * NB:// $user represent both staff and company owner
     * @return json response
     */
    public function index(int $offset = 0)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);


        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $staff_collection = Staff::with(['jobs' => function ($query) {
            $query->with('group', 'role', 'branch')->orderBy('effective_date', 'desc')->take(1);
        }, 'status'])->where('company_id', $company->id);
        $staff_count = $staff_collection->count();
        $staffs = $staff_collection->skip($offset)->take(20)->orderBy('firstname', 'ASC')->orderBy('lastname', 'ASC')->get(['id', 'firstname', 'lastname', 'mobile', 'email', 'profileurl']);

        if (!$staffs) {
            return response()->json('no staff found', 404);
        }

        return response()->json(compact('staffs', 'staff_count'), 200);
    }

    /**
     * Retrieves list of all staff from company staffs table
     * NB:// $user represent both staff and company owner
     * @return json response
     */
    public function birthday(Request $request)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);


        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        //  $ss = Staff::find(8);
        // dd( (Date('m',strtotime($ss->dob)) >= Date('m')) && (Date('m',strtotime($ss->dob)) <= Date('m')+4) );
        $gen_collection = Staff::where('company_id', $company->id)->whereNotNull('dob')->orderBy('dob')->get(['id', 'firstname', 'lastname', 'profileurl', 'dob']);
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

    public function show(int $id)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);


        $staff = Staff::with(['jobs' => function ($query) {
            $query->with('group', 'role', 'branch')->orderBy('effective_date', 'desc')->take(1);
        }, 'status', 'education', 'emergency', 'employmentStatus'])->where('company_id', $company->id)->where('id', $id)->first();

        if (!$staff) {
            return response()->json('no staff found', 404);
        }

        return response()->json(compact('staff'), 200);
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
            'firstname' => 'string|required|max:255',
            'lastname' => 'string|required|max:255',
            'email' => "email|required|unique:staffs",
            'phone' => 'unique:staffs',
            'facebook' => 'unique:staffs',
            'twitter' => 'unique:staffs',
            'linkedin' => 'unique:staffs',
            'ssnt' => 'unique:staffs',
            'tax_number' => 'unique:staffs'
        ]);

        $original_string = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $original_string = implode("", $original_string);
        $pass = substr(str_shuffle($original_string), 0, 10);

        $details = $request->except('password');
        $details['company_id'] = $company->id;
        $details['password'] = Hash::make($pass);


        $path = time() . '.png';
        $imageStr = $request->capturedImageData;

        if (!is_null($imageStr) && !empty($imageStr) && is_string($imageStr)) {
            $details['profileurl'] = $path;
            //send curl post request

            $cUrl = curl_init();
            
            //send base64 image
            curl_setopt($cUrl, CURLOPT_URL, "http://192.168.8.100:8006/store/profile");
            curl_setopt($cUrl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($cUrl, CURLOPT_ENCODING, "");
            curl_setopt($cUrl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cUrl, CURLOPT_POSTFIELDS, json_encode(["image" => $imageStr, "path" => $path]));
            curl_setopt($cUrl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($cUrl, CURLOPT_HTTPHEADER, array(
                "accept: */*",
                "accept-language: en-US, en;q=0.8",
                "content-type: application/json",
            ));

            $response = (curl_exec($cUrl));
            $error = curl_error($cUrl);
            $status_code = curl_getinfo($cUrl, CURLINFO_HTTP_CODE);
            curl_close($cUrl);

            // dd($response);
            if ($status_code !== 200)
                return response()->json('failed to save profile image', 500);
            
            //end curl post request
        }

        
        
        // dd($mail_details);
        $staff = Staff::create($details);

        if (!$staff)
            return response()->json('failed to create staff', 500);


        $mail_details = [];
        $mail_details['companyurl'] = $company->urlname;
        $mail_details['to_email'] = $staff->email;
        $mail_details['username'] = $user->firstname . ' ' . $user->lastname;
        $mail_details['useremail'] = $user->email;
        $mail_details['password'] = $pass;
        $mail_details['companyname'] = $company->name;

        \Queue::push(new StaffInvitationMail(
            $staff->email,
            $pass,
            ($user->firstname . ' ' . $user->lastname),
            $user->email,
            $company->name,
            $company->urlname
        ));

        return response()->json(compact('staff'), 200);

    }

    public function edit(Request $request)
    {
        //
        // return response()->json($_FILES);

        $user = $this->jwt->user();

        // return response()->json($user);
        //
        if (!$user)
            return response()->json('user not found', 404);


        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $staff = Staff::where('id', $request->id)->where('company_id', $company->id)->first();

        if (!$staff)
            return response()->json('staff not found', 404);

        $validator = [];
        if (isset($request->firstname) && $request->firstname !== $staff->firstname)
            $validator['firstname'] = 'string|required';

        if (isset($request->lastname) && $request->lastname !== $staff->lastname)
            $validator['lastname'] = 'string|required';

        if (isset($request->email) && $request->email !== $staff->email)
            $validator['email'] = 'email|required|unique:users';

        if (isset($request->phone) && $request->phone !== $staff->phone)
            $validator['phone'] = 'unique:users';


        $this->validate($request, $validator);

        $details = $request->except('password', 'file');
        if (isset($request->password))
            $details['password'] = app('hash')->make($request->password);

        $path = time() . '.png';
        $imageStr = $request->capturedImageData;

        if (!is_null($imageStr) && !empty($imageStr) && is_string($imageStr)) {
            $details['profileurl'] = $path;
            //send curl post request

            $cUrl = curl_init();
            
            //send base64 image
            curl_setopt($cUrl, CURLOPT_URL, "http://192.168.8.100:8006/store/profile");
            curl_setopt($cUrl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($cUrl, CURLOPT_ENCODING, "");
            curl_setopt($cUrl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cUrl, CURLOPT_POSTFIELDS, json_encode(["image" => $imageStr, "path" => $path]));
            curl_setopt($cUrl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($cUrl, CURLOPT_HTTPHEADER, array(
                "accept: */*",
                "accept-language: en-US, en;q=0.8",
                "content-type: application/json",
            ));

            $response = (curl_exec($cUrl));
            $error = curl_error($cUrl);
            $status_code = curl_getinfo($cUrl, CURLINFO_HTTP_CODE);
            curl_close($cUrl);

            // dd($response);
            if ($status_code !== 200)
                return response()->json('failed to save profile image', 500);
            
            //end curl post request
        }

        // //handle employee file
        // if ($request->hasFile('staff_file')) {
        //     dd('filinging');
        //     $filename = $request->file('staff_file')->getClientOriginalName() . time() . '.' . $request->file('staff_file')->getClientOriginalExtension();
        //     $filetype = $request->file('staff_file')->getMimeType();

        //     $cUrl = curl_init();
            
        //     //send base64 image
        //     curl_setopt($cUrl, CURLOPT_URL, "http://192.168.8.100:8006/store/file");
        //     curl_setopt($cUrl, CURLOPT_CUSTOMREQUEST, "POST");
        //     curl_setopt($cUrl, CURLOPT_ENCODING, "");
        //     curl_setopt($cUrl, CURLOPT_MAXREDIRS, 10);
        //     curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($cUrl, CURLOPT_POSTFIELDS, array(
        //         'file' => '@' . realpath($request->file('staff_file')->getPathName()) . ";filename={$filename};type={$filetype}"
        //     ));
        //     curl_setopt($cUrl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        //     curl_setopt($cUrl, CURLOPT_HTTPHEADER, array(
        //         "accept: */*",
        //         "accept-language: en-US, en;q=0.8",
        //         "content-type: application/json",
        //     ));

        //     $response = (curl_exec($cUrl));
        //     $error = curl_error($cUrl);
        //     $status_code = curl_getinfo($cUrl, CURLINFO_HTTP_CODE);
        //     curl_close($cUrl);

        //     // dd($response);
        //     if ($status_code !== 200)
        //         return response()->json('failed to save staff file', 500);

        // }

        $result = $staff->update($details);
        if (!$result)
            return response()->json('failed to update staff', 500);


        return response()->json(compact('staff'), 200);

    }

    public function delete(Request $request)
    {
        //
        $user = $this->jwt->user();

        $this->validate($request, [
            'id' => 'required|integer'
        ]);


        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);


        $staff = Staff::where('id', $request->id)->where('company_id', $user->id)->first();

        $result = $staff->delete();

        if (!$result)
            return response()->json('failed to delete staff', 500);

        return response()->json('staff deleted successfully', 200);

    }

    /**
     * Updates staff permission
     * @param  \Illuminate\Http\Request  $request
     * @return json response
     */
    public function updateAccessLevel(Request $request)
    {

        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $this->validate($request, [
            'access_levels' => "required",
            'id' => "integer|required",
        ]);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);


        $staff = Staff::where('id', $request->id)->where('company_id', $company->id)->first();
        if (!$staff)
            return response()->json(['staff not found'], 404);

        $staff->access()->detach();

        foreach ($request->access_levels as $access_id) {
            $staff->access()->attach($access_id);
            # code...
        }

    }

    public function search(Request $request)
    {
        //
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        if (!is_null($request->searchword))
            $searchquery = Staff::with(['jobs' => function ($query) {
            $query->orderBy('effective_date', 'asc')->take(1);
        }, 'status'])->where('company_id', $company->id)->where('firstname', 'LIKE', "%{$request->searchword}%")
            ->orWhere('lastname', 'LIKE', "%{$request->searchword}%")
            ->orWhere('email', 'LIKE', "%{$request->searchword}%")
            ->orWhere('mobile', 'LIKE', "%{$request->searchword}%")
            ->orWhere('home_phone', 'LIKE', "%{$request->searchword}%")
            ->get(['id', 'firstname', 'lastname', 'mobile', 'email', 'profileurl']);
        else {
            $searchquery = Staff::with(['jobs' => function ($query) {
                $query->orderBy('effective_date', 'asc')->take(1);
            }, 'status'])->where('company_id', $company->id)->get(['id', 'firstname', 'lastname', 'mobile', 'email', 'profileurl']);
        }

        if (!is_null($request->group) && $request->group > 0)
            $searchquery = $searchquery->where('group_id', $request->group);

        if (!is_null($request->role) && $request->role > 0)
            $searchquery = $searchquery->where('position_id', $request->role);

        if (!is_null($request->branch) && $request->branch > 0)
            $searchquery = $searchquery->where('position_id', $request->branch);

        $staffs = $searchquery;

        if (!$staffs)
            return response()->json('no match found', 404);

        return response()->json(compact('staffs'), 200);
    }

    public function relations()
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);


        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $relations = Relation::all();
        if (!$relations) {
            return response()->json('no relations found', 404);
        }

        return response()->json(compact('relations'), 200);
    }

    public function searchBranchGroupRole(Request $request)
    {
        $user = $this->jwt->user();

        if (!$user)
            return response()->json('user not found', 404);

        $company = $user->company;
        if (!$company)
            return response()->json('company not found', 404);

        $this->validate($request, [
            "branches" => "array",
            "groups" => "array",
            "roles" => "array",
        ]);

        $search = Job::with('staff')->whereIN('branch_id', $request->branches);
        if (isset($request->groups) && count($request->groups) > 0 && !array_has($request->groups, 0))
            $search = $search->whereIN('group_id', $request->groups);
        if (isset($request->roles) && count($request->roles) > 0 && !array_has($request->roles, 0))
            $search = $search->whereIN('role_id', $request->roles);

        $search->where('is_current', true);
        $jobs = $search->get();
        $staffs = array();

        if (!$jobs)
            return response()->json('staffs not found', 404);

        foreach ($jobs as $job)
            array_push($staffs, $job->staff);

        return response()->json(compact('staffs'), 200);
    }




}
