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
    public string $email;
    public string $requestedAt;
    public ?string $requestIp;

    public function __construct(string $username, string $resetUrl, string $email, string $requestedAt, ?string $requestIp = null)
    {
        $this->username = $username;
        $this->resetUrl = $resetUrl;
        $this->email = $email;
        $this->requestedAt = $requestedAt;
        $this->requestIp = $requestIp;
    }

    public function build()
    {
        return $this->subject('Inventory Management with Asset Tracking System - Password Reset Request')
            ->view('emails.password-reset')
            ->with([
                'username' => $this->username,
                'resetUrl' => $this->resetUrl,
                'email' => $this->email,
                'requestedAt' => $this->requestedAt,
                'requestIp' => $this->requestIp,
            ]);
    }
}
