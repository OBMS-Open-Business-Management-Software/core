<?php

namespace App\Helpers;

use App\Models\Support\Category\SupportCategory;
use App\Models\Support\SupportTicket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mime\Crypto\SMimeSigner;

/**
 * Class SMIME.
 *
 * This class is the helper for signing emails via. S/MIME certificates.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class SMIME
{
    /**
     * Sign a message (if applicable).
     *
     * @param MailMessage $message
     *
     * @return MailMessage
     */
    public static function sign(MailMessage $message): MailMessage
    {
        if (config('mail.signing.enabled', false)) {
            $message = $message->withSymfonyMessage(function (MailMessage $message) {
                if (! empty($passphrase = config('mail.signing.passphrase'))) {
                    $smimeSigner = new SMimeSigner(config('mail.signing.certificate'), config('mail.signing.key'), $passphrase);
                } else {
                    $smimeSigner = new SMimeSigner(config('mail.signing.certificate'), config('mail.signing.key'));
                }

                $smimeSigner->sign($message);
            });
        }

        return $message;
    }
}
