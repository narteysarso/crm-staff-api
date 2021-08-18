<?php

namespace App\Http\Controllers\auth;

use App\Staff;
use App\Jobs\ProcessPasswordResetMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ResetStaffPassword extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:staffs,email',
        ]);

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff)
            return response()->json('staff not found', 404);

        $original_string = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $original_string = implode("", $original_string);
        $pass = substr(str_shuffle($original_string), 0, 10);
        $staff->password = app('hash')->make($pass);
        $result = $staff->save();

        if (!$result)
            return response()->json('unable to reset password', 500);

        // try {

        // $this->dispatch(new ProcessPasswordResetMail($staff->email, $pass));
        \Queue::push(new ProcessPasswordResetMail($staff->email, $pass));

        // } catch (\Throwable $th) {
        //         //throw $th;
        // }

        return response()->json("password reset. Please check your email for your new password.{$pass}", 200);
    }

}
