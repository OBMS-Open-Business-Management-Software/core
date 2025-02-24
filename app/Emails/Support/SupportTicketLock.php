<?php

declare(strict_types=1);

namespace App\Emails\Support;

use App\Helpers\SMIME;
use App\Models\Support\SupportTicket;
use Closure;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class SupportTicketLock extends Notification
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
     * @param SupportTicket $notifiable
     *
     * @return array
     */
    public function via(SupportTicket $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param SupportTicket $notifiable
     *
     * @return MailMessage
     */
    public function toMail(SupportTicket $notifiable): MailMessage
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
     * @param string        $url
     * @param SupportTicket $notifiable
     *
     * @return MailMessage
     */
    protected function buildMailMessage(string $url, SupportTicket $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject('[Ticket #' . $notifiable->id . '] ' . Lang::get('Locked'))
            ->line(Lang::get('The ticket has been locked.'))
            ->action(Lang::get('View Ticket'), $url)
            ->replyTo($notifiable->answerEmailAddress, $notifiable->answerEmailName);

        return SMIME::sign($message);
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param SupportTicket $notifiable
     *
     * @return string
     */
    protected function ticketUrl(SupportTicket $notifiable): string
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
