<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
            return true;
        } catch (\Throwable $e) {
            logger()->error('Error enviando correo personalizado', [
                'email' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
