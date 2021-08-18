<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
// use Illuminate\Contracts\Queue\ShouldQueue;

class IVMailer extends Mailable
{
    use Queueable, SerializesModels;
    protected $to_address;
    protected $password;
    protected $username;
    protected $useremail;
    protected $companyname;
    protected $companyurl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(String $to_address, String $password,String $username,String $useremail,String $companyname,String  $companyurl)
    {
        //
        $this->to_address = $to_address;
        $this->password = $password;
        $this->companyurl = $companyurl;
        $this->companyname = $companyname;
        $this->username = $username;
        $this->useremail = $useremail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this
            ->from('testitlaravel@gmail.com')
            ->to($this->to_address)
            ->subject("{$this->username} invites you to join a company")
            ->view('emails.staffregisted')
            ->with([
                'password' => $this->password,
                'companyurl' => $this->companyurl,
                'companyname' => $this->companyname,
                'username' => $this->username,
                'useremail' => $this->useremail,
            ]);
    }
}
