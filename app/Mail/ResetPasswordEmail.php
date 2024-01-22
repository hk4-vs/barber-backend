<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->view('emails.reset_password')
            ->subject('Reset Your Password')
            ->with([
                'resetLink' => $this->generateResetPasswordLink(),
            ]);
    }

    private function generateResetPasswordLink()
    {
        // Generate the reset password link using the token
        return config('app.url') . '/reset-password?token=' . $this->token;
    }
}
