<?php

declare(strict_types=1);

namespace App\Jobs\Dispatchers;

use App\Jobs\Structure\Job;
use App\Jobs\TenantJobs\AutoMigrations as AutoMigrationsJob;
use App\Models\Tenant;

/**
 * Class AutoMigrations
 *
 * This class is the dispatcher job for running migrations.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class AutoMigrations extends Job
{
    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'dispatchers';

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        // Dispatch job for main instance
        $this->dispatch((new AutoMigrationsJob([
            'tenant_id' => 0,
        ]))->onQueue('auto_migrations'));

        // Dispatch job for tenant instances
        Tenant::query()->each(function (Tenant $tenant) {
            $this->dispatch((new AutoMigrationsJob([
                'tenant_id' => $tenant->id,
            ]))->onQueue('auto_migrations'));
        });
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'job',
            'job:dispatcher',
            'job:dispatcher:AutoMigrations',
        ];
    }
}
