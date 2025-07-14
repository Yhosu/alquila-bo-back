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

    public function sendEmail(string $to, string $subject, string $body, ?string $cc = null, ?string $bcc = null): bool
    {
        try {
            $toList = array_filter(array_map('trim', explode(';', $to)));
            $ccList = $cc ? array_filter(array_map('trim', explode(';', $cc))) : [];
            $bccList = $bcc ? array_filter(array_map('trim', explode(';', $bcc))) : [];
            Mail::html($body, function ($message) use ($toList, $subject, $ccList, $bccList) {
                $message->to($toList)->subject($subject);

                if (!empty($ccList)) {
                    $message->cc($ccList);
                }

                if (!empty($bccList)) {
                    $message->bcc($bccList);
                }
            });
            return true;
        } catch (\Throwable $e) {
            logger()->error('Error enviando correo personalizado', [
                'email' => $to,
                'cc' => $cc,
                'bcc' => $bcc,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
