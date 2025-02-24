<?php

declare(strict_types=1);

namespace App\Emails\Accounting;

use App\Helpers\SMIME;
use App\Models\Accounting\Invoice\InvoiceReminder;
use App\Models\FileManager\File;
use Closure;
use Endroid\QrCode\ErrorCorrectionLevel;
use Exception;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use SepaQr\Data;

class AccountingReminder extends Notification
{
    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var Closure|null
     */
    public static ?Closure $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var Closure|null
     */
    public static ?Closure $toMailCallback;

    /**
     * Get the notification's channels.
     *
     * @param InvoiceReminder $notifiable
     *
     * @return array
     */
    public function via(InvoiceReminder $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param InvoiceReminder $notifiable
     *
     * @return MailMessage
     */
    public function toMail(InvoiceReminder $notifiable): MailMessage
    {
        $ticketUrl = $this->ticketUrl($notifiable);

        if (! empty(static::$toMailCallback)) {
            return call_user_func(static::$toMailCallback, $notifiable, $ticketUrl);
        }

        return $this->buildMailMessage($ticketUrl, $notifiable);
    }

    /**
     * Get the support ticket creation email notification mail message for the given URL.
     *
     * @param string          $url
     * @param InvoiceReminder $notifiable
     *
     * @return MailMessage
     */
    protected function buildMailMessage(string $url, InvoiceReminder $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject(Lang::get('New Payment Reminder: ') . $notifiable->number)
            ->line(Lang::get('A new invoice reminder has been published to your account.'))
            ->action(Lang::get('View Invoice'), $url)
            ->replyTo(config('mail.support.address'), config('mail.support.name'));

        /* @var File|null $file */
        if (! empty($file = $notifiable->file)) {
            $file = $file->makeVisible('data');

            $message = $message->attachData($file->data, $file->name);
        } else {
            try {
                $sepaQr = ! empty($amount = round($notifiable->invoice->grossSum + ($notifiable->dunning->fixed_amount ?? 0) + (! empty($notifiable->dunning->percentage_amount) ? $notifiable->invoice->netSum * ($notifiable->dunning->percentage_amount / 100) : 0), 2)) && $amount > 0 ? Data::create()
                    ->setName(config('company.bank.owner'))
                    ->setIban(str_replace(' ', '', config('company.bank.iban')))
                    ->setRemittanceText($notifiable->number)
                    ->setAmount($amount) : null;

                if (! empty($sepaQr)) {
                    $sepaQr = \Endroid\QrCode\Builder\Builder::create()
                        ->data($sepaQr)
                        ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
                        ->build()
                        ->getDataUri();
                }
            } catch (Exception $exception) {
                $sepaQr = null;
            }

            $pdf = App::make('dompdf.wrapper')->loadView('pdf.reminder', [
                'reminder' => $notifiable,
                'sepaQr'   => $sepaQr,
            ]);

            $message = $message->attachData($pdf->output(), $notifiable->number . '.pdf');
        }

        return SMIME::sign($message);
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param InvoiceReminder $notifiable
     *
     * @return string
     */
    protected function ticketUrl(InvoiceReminder $notifiable): string
    {
        if (! empty(static::$createUrlCallback)) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::signedRoute(
            'customer.support.details',
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->email),
            ]
        );
    }

    /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param Closure $callback
     */
    public static function createUrlUsing(Closure $callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param Closure $callback
     */
    public static function toMailUsing(Closure $callback)
    {
        static::$toMailCallback = $callback;
    }
}
