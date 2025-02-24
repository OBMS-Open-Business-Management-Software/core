<?php

declare(strict_types=1);

namespace App\Models\Shop\OrderQueue;

use App\Models\Shop\Configurator\ShopConfiguratorField;
use App\Models\Shop\Configurator\ShopConfiguratorFieldOption;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShopOrderQueueField
 *
 * This class is the model for basic shop order field metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                              $id
 * @property int                              $order_id
 * @property int|null                         $field_id
 * @property int|null                         $option_id
 * @property string                           $key
 * @property string                           $value
 * @property Carbon                           $created_at
 * @property Carbon                           $updated_at
 * @property Carbon                           $deleted_at
 * @property ShopOrderQueue|null              $order
 * @property ShopConfiguratorField|null       $field
 * @property ShopConfiguratorFieldOption|null $option
 */
class ShopOrderQueueField extends Model
{
    use SoftDeletes;

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

    /**
     * Relation to field.
     *
     * @return HasOne
     */
    public function field(): HasOne
    {
        return $this->hasOne(ShopConfiguratorField::class, 'id', 'field_id');
    }

    /**
     * Relation to option.
     *
     * @return HasOne
     */
    public function option(): HasOne
    {
        return $this->hasOne(ShopConfiguratorFieldOption::class, 'id', 'option_id');
    }
}
