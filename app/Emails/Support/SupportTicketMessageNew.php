<?php

declare(strict_types=1);

namespace App\Emails\Support;

use App\Helpers\SMIME;
use App\Models\Support\SupportTicketMessage;
use Closure;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class SupportTicketMessageNew extends Notification
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
     * @param SupportTicketMessage $notifiable
     *
     * @return array
     */
    public function via(SupportTicketMessage $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param SupportTicketMessage $notifiable
     *
     * @return MailMessage
     */
    public function toMail(SupportTicketMessage $notifiable): MailMessage
    {
        $ticketUrl = $this->ticketUrl($notifiable);

        if (! empty(static::$toMailCallback)) {
            return call_user_func(static::$toMailCallback, $notifiable, $ticketUrl);
        }

        return $this->buildMailMessage($ticketUrl, $notifiable);
    }

    /**
     * Get the support ticket message email notification mail message for the given URL.
     *
     * @param string               $url
     * @param SupportTicketMessage $notifiable
     *
     * @return MailMessage
     */
    protected function buildMailMessage(string $url, SupportTicketMessage $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject('[Ticket #' . $notifiable->ticket->id . '] ' . Lang::get('New response'))
            ->line($notifiable->message)
            ->action(Lang::get('View Ticket'), $url)
            ->replyTo($notifiable->ticket->answerEmailAddress, $notifiable->ticket->answerEmailName);

        return SMIME::sign($message);
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param SupportTicketMessage $notifiable
     *
     * @return string
     */
    protected function ticketUrl(SupportTicketMessage $notifiable): string
    {
        if (! empty(static::$createUrlCallback)) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::signedRoute(
            'customer.support.details',
            [
                'id'   => $notifiable->ticket->getKey(),
                'hash' => sha1($notifiable->ticket->email),
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
