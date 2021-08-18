<?php

namespace App\Jobs;

use App\Mail\IVMailer as IVMail;
use Illuminate\Support\Facades\Mail;

class StaffInvitationMail extends Job
{
    
    protected $to_address;
    protected $password;
    protected $username;
    protected $useremail;
    protected $companyname;
    protected $companyurl;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(String $to_email, String $pass,String $uname,String $uemail,String $cname,String  $curl)
    {
        //
        $this->to_address = $to_email;
        $this->password = $pass;
        $this->username = $uname;
        $this->useremail = $uemail;
        $this->companyname = $cname;
        $this->companyurl = $curl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Mail::send(
            new IVMail(
                $this->to_address,
                $this->password,
                $this->username,
                $this->useremail,
                $this->companyname,
                $this->companyurl
            )
        );;
    }
}
