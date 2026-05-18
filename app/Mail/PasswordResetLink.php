<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetLink extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $username;
    public string $resetUrl;

    public function __construct(string $username, string $resetUrl)
    {
        $this->username = $username;
        $this->resetUrl = $resetUrl;
    }

    public function build()
    {
        return $this->subject('Password Reset Request')
            ->view('emails.password-reset')
            ->with([
                'username' => $this->username,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
