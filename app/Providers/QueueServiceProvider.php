<?php

declare(strict_types=1);

namespace App\Providers;

use App\Queue\HorizonUniqueConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving(QueueManager::class, function ($manager) {
            $manager->addConnector('unique', function () {
                return new HorizonUniqueConnector($this->app['redis']);
            });
        });
    }
}
