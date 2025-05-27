<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data, $url;

    public function __construct($data)
    {
        $this->data = $data;
        $this->url = "http://localhost:4200/forgot-pwd/{$this->data['id']}/{$this->data['hash']}";
    }

    public function build()
    {
        return $this->subject('Password Reset Link')
                    ->view('email.PasswordReset')->with(['url'=>$this->url]); 
    }
}
?>