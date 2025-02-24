<?php

declare(strict_types=1);

namespace App\Jobs\TenantJobs;

use App\Helpers\Products;
use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;

/**
 * Class ShopOrderRemoval
 *
 * This class is the tenant job for removing expired orders.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ShopOrderRemoval extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;

    public $timeout = 3600;

    public static $onQueue = 'shop_orders';

    /**
     * ShopOrderRemoval constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        Products::list()->each(function ($handler) {
            $this->dispatch((new ShopOrderQueueSetup([
                'tenant_id' => $this->tenant_id,
                'type'      => $handler->technicalName(),
            ]))->onQueue('shop_order_removals'));
        });
    }

    /**
     * Define tags which the job can be identified by.
     *
     * @return array
     */
    public function tags(): array
    {
        return $this->injectTenantTags([
            'job',
            'job:tenant',
            'job:tenant:ShopOrderRemoval',
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'shop-order-removals-' . $this->tenant_id;
    }
}
