<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
// use Illuminate\Contracts\Queue\ShouldQueue;

class Mailer extends Mailable
{
    use Queueable, SerializesModels;
    protected $to_address;
    protected $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($to_address, $password)
    {
        //
        $this->to_address = $to_address;
        $this->password = $password;
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
            ->subject('Password Reset')
            ->view('emails.resetpassword')
            ->with([
                'password' => $this->password
            ]);
    }
}
