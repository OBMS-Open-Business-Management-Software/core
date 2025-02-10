<?php

namespace App\Jobs\TenantJobs;

use App\Helpers\Products;
use App\Jobs\Structure\TenantJob;
use App\Jobs\Structure\UniquelyQueueable;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\Shop\OrderQueue\ShopOrderQueueHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ShopOrderRemovalAction
 *
 * This class is the tenant job for removing expired orders of a specific type.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class ShopOrderRemovalAction extends TenantJob
{
    use UniquelyQueueable;

    public $tries = 1;
    public $timeout = 3600;
    public static $onQueue = 'shop_order_setups';

    private string $type;

    /**
     * ShopOrderRemovalAction constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->type = $data['type'];
    }

    /**
     * Execute job algorithm.
     */
    public function handle()
    {
        if (
            ! empty(
                $handler = Products::list()->filter(function ($handler) {
                    return $handler->technicalName() === $this->type;
                })->first()
            )
        ) {
            ShopOrderQueue::where('setup', '=', true)
                ->whereHas('contract', function (Builder $builder) {
                    return $builder->whereNotNull('cancelled_to')
                        ->where('cancelled_to', '<', Carbon::now());
                })
                ->where('deleted', '=', false)
                ->each(function (ShopOrderQueue $queueItem) use ($handler) {
                    try {
                        if (
                            ! empty($grace = $handler->grace()) &&
                            $grace > 0
                        ) {
                            if (Carbon::now()->gt($queueItem->contract->cancelled_to->addHours($grace))) {
                                $handler->delete($queueItem->id);

                                $queueItem->update([
                                    'deleted' => true,
                                ]);

                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type' => 'success',
                                    'message' => 'Product deletion succeeded.',
                                ]);

                                $queueItem->sendEmailRemovalNotification();
                            } else {
                                $handler->lock($queueItem->id);

                                $queueItem->update([
                                    'locked' => true,
                                ]);

                                ShopOrderQueueHistory::create([
                                    'order_id' => $queueItem->id,
                                    'type' => 'success',
                                    'message' => 'Product locking succeeded.',
                                ]);

                                $queueItem->sendEmailLockNotification();
                            }
                        } else {
                            $handler->delete($queueItem->id);

                            $queueItem->update([
                                'deleted' => true,
                            ]);

                            ShopOrderQueueHistory::create([
                                'order_id' => $queueItem->id,
                                'type' => 'success',
                                'message' => 'Product deletion succeeded.',
                            ]);

                            $queueItem->sendEmailRemovalNotification();
                        }
                    } catch (Exception $exception) {
                        ShopOrderQueueHistory::create([
                            'order_id' => $queueItem->id,
                            'type' => 'warning',
                            'message' => 'Product locking / deletion failed.',
                        ]);

                        $queueItem->sendEmailLockFailedNotification();
                    }
                });

            ShopOrderQueue::where('setup', '=', true)
                ->whereHas('contract', function (Builder $builder) {
                    return $builder->whereNull('cancelled_to');
                })
                ->where('locked', '=', true)
                ->where('deleted', '=', false)
                ->each(function (ShopOrderQueue $queueItem) use ($handler) {
                    try {
                        $handler->unlock($queueItem->id);

                        $queueItem->update([
                            'locked' => false,
                        ]);

                        ShopOrderQueueHistory::create([
                            'order_id' => $queueItem->id,
                            'type' => 'success',
                            'message' => 'Product unlocking succeeded.',
                        ]);

                        $queueItem->sendEmailUnlockNotification();
                    } catch (Exception $exception) {
                        ShopOrderQueueHistory::create([
                            'order_id' => $queueItem->id,
                            'type' => 'warning',
                            'message' => 'Product unlocking failed.',
                        ]);

                        $queueItem->sendEmailUnlockFailedNotification();
                    }
                });
        }
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
            'job:tenant:ShopOrderRemovalAction',
            'job:tenant:ShopOrderRemovalAction:' . $this->type,
        ]);
    }

    /**
     * Set a unique identifier to avoid duplicate queuing of the same task.
     *
     * @return string
     */
    public function getUniqueIdentifier(): string
    {
        return 'shop-order-removals-' . $this->type . '-' . $this->tenant_id;
    }
}
