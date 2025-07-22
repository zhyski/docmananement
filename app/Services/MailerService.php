<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailerService
{
    /**
     * Send login credentials email.
     *
     * @param string $email
     * @param string $plainPassword
     * @return void
     */
    public function sendLoginCredentials(string $email, string $plainPassword): void
    {
        Mail::raw(
            "Your account has been created.\nEmail: {$email}\nPassword: {$plainPassword}",
            function ($message) use ($email) {
                $message->to($email)
                        ->subject('Your Login Credentials');
            }
        );
    }

    /**
     * Send OTP email.
     *
     * @param string $email
     * @param string $otp
     * @return void
     */
    public function sendOtpEmail(string $email, string $otp): void
    {
        Mail::raw(
            "Your OTP for password change is: {$otp}",
            function ($message) use ($email) {
                $message->to($email)
                        ->subject('Password Change OTP');
            }
        );
    }
}
