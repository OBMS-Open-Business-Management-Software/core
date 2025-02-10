<?php

namespace App\Products;

use App\Models\Accounting\Contract\Contract;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Service.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $contract_id
 * @property int $user_id
 * @property int $order_id
 * @property Carbon $locked_at
 *
 * @property boolean $locked
 */
abstract class Service extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'locked_at' => 'datetime',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to contract.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    /**
     * Relation to order.
     *
     * @return HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(ShopOrderQueue::class, 'id', 'order_id');
    }

    /**
     * Get service lock status.
     *
     * @return bool
     */
    public function getLockedAttribute(): bool
    {
        return ! empty($this->locked_at);
    }
}
