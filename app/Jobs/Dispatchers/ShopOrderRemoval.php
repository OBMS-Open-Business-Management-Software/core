<?php

declare(strict_types=1);

namespace App\Jobs\Dispatchers;

use App\Jobs\Structure\Job;
use App\Jobs\TenantJobs\ShopOrderRemoval as ShopOrderRemovalJob;
use App\Models\Tenant;

/**
 * Class ShopOrderRemoval
 *
 * This class is the dispatcher job for removing expired orders.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ShopOrderRemoval extends Job
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
        $this->dispatch((new ShopOrderRemovalJob([
            'tenant_id' => 0,
        ]))->onQueue('shop_orders'));

        // Dispatch job for tenant instances
        Tenant::query()->each(function (Tenant $tenant) {
            $this->dispatch((new ShopOrderRemovalJob([
                'tenant_id' => $tenant->id,
            ]))->onQueue('shop_orders'));
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
            'job:dispatcher:ShopOrderRemoval',
        ];
    }
}
