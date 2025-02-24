<?php

declare(strict_types=1);

namespace App\Models\Shop\OrderQueue;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShopOrderQueueHistory
 *
 * This class is the model for basic shop order history metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                 $id
 * @property int|null            $order_id
 * @property string              $type
 * @property string              $message
 * @property Carbon              $created_at
 * @property Carbon              $updated_at
 * @property Carbon              $deleted_at
 * @property ShopOrderQueue|null $order
 */
class ShopOrderQueueHistory extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shop_order_queue_history';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to order.
     *
     * @return HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(ShopOrderQueue::class, 'id', 'order_id');
    }
}
