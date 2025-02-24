<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Models\Accounting\Contract\ContractPosition;
use App\Models\Accounting\Invoice\InvoicePosition;
use App\Models\Shop\Configurator\ShopConfiguratorForm;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Position
 *
 * This class is the model for basic position metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                          $id
 * @property int|null                     $order_id
 * @property int|null                     $product_id
 * @property int|null                     $discount_id
 * @property string                       $name
 * @property string                       $description
 * @property float                        $amount
 * @property int                          $vat_percentage
 * @property float                        $quantity
 * @property Carbon                       $created_at
 * @property Carbon                       $updated_at
 * @property Carbon                       $deleted_at
 * @property PositionDiscount|null        $discount
 * @property Product|null                 $product
 * @property Order|null                   $order
 * @property Collection<InvoicePosition>  $invoicePositions
 * @property Collection<ContractPosition> $contractPositions
 * @property float                        $netSum
 * @property float                        $grossSum
 * @property float                        $vatSum
 * @property float                        $discountNetSum
 * @property float                        $discountGrossSum
 * @property float                        $discountVatSum
 */
class Position extends Model
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
     * Relation to discount.
     *
     * @return HasOne
     */
    public function discount(): HasOne
    {
        return $this->hasOne(PositionDiscount::class, 'id', 'discount_id');
    }

    /**
     * Relation to product.
     *
     * @return HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(ShopConfiguratorForm::class, 'id', 'product_id');
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
     * Relation to invoice position links.
     *
     * @return HasMany
     */
    public function invoicePositions(): HasMany
    {
        return $this->hasMany(InvoicePosition::class, 'position_id', 'id');
    }

    /**
     * Relation to invoice position links.
     *
     * @return HasMany
     */
    public function contractPositions(): HasMany
    {
        return $this->hasMany(ContractPosition::class, 'position_id', 'id');
    }

    /**
     * Get net sum for position.
     *
     * @return float
     */
    public function getNetSumAttribute(): float
    {
        return (float) ($this->amount * $this->quantity);
    }

    /**
     * Get gross sum for position.
     *
     * @return float
     */
    public function getGrossSumAttribute(): float
    {
        return (float) ($this->netSum * (($this->vat_percentage / 100) + 1));
    }

    /**
     * Get vat sum for position. In this case net sum subtracted from
     * gross sum.
     *
     * @return float
     */
    public function getVatSumAttribute(): float
    {
        return $this->grossSum - $this->netSum;
    }

    /**
     * Get vat sum for position. In this case net sum subtracted from
     * gross sum.
     *
     * @return float
     */
    public function getDiscountNetSumAttribute(): float
    {
        $value = 0;

        if (! empty($discount = $this->discount)) {
            switch ($discount->type) {
                case 'percentage':
                    $value = $this->netSum * ($this->discount->amount / 100);

                    break;
                case 'fixed':
                default:
                    $value = $this->discount->amount;

                    break;
            }
        }

        return $value;
    }

    /**
     * Get vat sum for position. In this case net sum subtracted from
     * gross sum.
     *
     * @return float
     */
    public function getDiscountGrossSumAttribute(): float
    {
        $value = 0;

        if (! empty($discount = $this->discount)) {
            switch ($discount->type) {
                case 'percentage':
                    $value = $this->grossSum * ($this->discount->amount / 100);

                    break;
                case 'fixed':
                default:
                    $value = $this->discount->amount * (1 + ($this->vat_percentage / 100));

                    break;
            }
        }

        return $value;
    }

    /**
     * Get vat sum for position. In this case net sum subtracted from
     * gross sum.
     *
     * @return float
     */
    public function getDiscountVatSumAttribute(): float
    {
        return $this->discountGrossSum - $this->discountNetSum;
    }
}
