<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\PaymentGateways;
use App\Helpers\Products;
use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $folders = [];

        PaymentGateways::list()->each(function ($gateway) use (&$folders) {
            $folders[] = $gateway->folderName() . '/Migrations';
        });

        Products::list()->each(function ($product) use (&$folders) {
            $folders[] = $product->folderName() . '/Migrations';
        });

        $this->loadMigrationsFrom([
            database_path('migrations'),
            ...$folders,
        ]);
    }
}
