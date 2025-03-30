<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\PaymentGateways;
use App\Helpers\Products;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Class MigrationServiceProvider.
 *
 * This class handles loading migrations from various sources including
 * payment gateways and products.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class MigrationServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * Loads migrations from the main database migrations directory
     * and from all registered payment gateways and products.
     */
    public function boot(): void
    {
        try {
            $migrationPaths = [];

            // Load payment gateway migrations
            PaymentGateways::list()->each(function ($gateway) use (&$migrationPaths) {
                $migrationPaths[] = $gateway->folderName() . '/Migrations';
            });

            // Load product migrations
            Products::list()->each(function ($product) use (&$migrationPaths) {
                $migrationPaths[] = $product->folderName() . '/Migrations';
            });

            // Load migrations from all paths
            $this->loadMigrationsFrom([
                database_path('migrations'),
                ...$migrationPaths,
            ]);
        } catch (Exception $e) {
            // Log the error but don't crash the application
            Log::error('Failed to load migrations: ' . $e->getMessage());
        }
    }
}
