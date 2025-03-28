<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\LogFailedJob;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        JobFailed::class => [
            LogFailedJob::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        //
    }
}
