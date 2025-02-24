<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Notifications\Messages\MailMessage;
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
