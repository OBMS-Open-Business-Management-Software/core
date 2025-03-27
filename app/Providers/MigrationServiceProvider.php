<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $folders = [];

        collect(scandir(__DIR__ . '/../PaymentGateways'))->reject(function (string $path) {
            return $path == '.' || $path == '..' || $path == '.gitignore' || str_contains($path, '.php');
        })->each(function (string $folder) use (&$folders) {
            $fullPath = __DIR__ . '/../PaymentGateways/' . $folder . '/src/Migrations';

            if (is_dir($fullPath)) {
                $folders[] = $fullPath;
            }
        });

        collect(scandir(__DIR__ . '/../Products'))->reject(function (string $path) {
            return $path == '.' || $path == '..' || $path == '.gitignore' || str_contains($path, '.php');
        })->each(function (string $folder) use (&$folders) {
            $fullPath = __DIR__ . '/../Products/' . $folder . '/src/Migrations';

            if (is_dir($fullPath)) {
                $folders[] = $fullPath;
            }
        });

        $this->loadMigrationsFrom([
            database_path('migrations'),
            ...$folders,
        ]);
    }
}
