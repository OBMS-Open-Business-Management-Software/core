<?php

namespace App\Jobs\Dispatchers;

use App\Jobs\Structure\Job;
use App\Jobs\TenantJobs\ShopOrderQueue as ShopOrderQueueJob;
use App\Models\Tenant;

/**
 * Class ShopOrderQueue
 *
 * This class is the dispatcher job for processing orders.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ShopOrderQueue extends Job
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
        $this->dispatch((new ShopOrderQueueJob([
            'tenant_id' => 0,
        ]))->onQueue('shop_orders'));

        // Dispatch job for tenant instances
        Tenant::query()->each(function (Tenant $tenant) {
            $this->dispatch((new ShopOrderQueueJob([
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
            'job:dispatcher:ShopOrderQueue',
        ];
    }
}
