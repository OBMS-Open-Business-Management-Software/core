<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app['events']->forget(SocialiteWasCalled::class);

        if (!empty(config('sso.provider'))) {
            switch (config('sso.provider')) {
                case 'microsoft':
                    $this->app['events']->listen(
                        SocialiteWasCalled::class,
                        MicrosoftExtendSocialite::class . '@handle'
                    );

                    break;
                case 'google':
                    $this->app['events']->listen(
                        SocialiteWasCalled::class,
                        GoogleExtendSocialite::class . '@handle'
                    );

                    break;
            }
        }
    }
}
