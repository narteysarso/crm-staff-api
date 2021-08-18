<?php

namespace App\Jobs;

use App\Mail\Mailer as PassMail;
use Illuminate\Support\Facades\Mail;

class ProcessPasswordResetMail extends Job
{
    /**
     * Takes String
     * @var String
     */
    protected $to_email;

    /**
     * Takes String
     * @var String
     */
    protected $new_password;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(String $to_email, String $newpassword)
    {
        //
        $this->to_email = $to_email;
        $this->new_password = $newpassword;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Mail::send(
            new PassMail(
                $this->to_email,
                $this->new_password
            )
        );
    }
}
