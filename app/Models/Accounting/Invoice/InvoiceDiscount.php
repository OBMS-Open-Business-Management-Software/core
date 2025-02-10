<?php

namespace App\Models\Accounting\Invoice;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InvoiceDunning
 *
 * This class is the model for basic invoice discount metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property double|null $period
 * @property double|null $percentage_amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property Collection<InvoiceType> $types
 */
class InvoiceDiscount extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_discounts';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to types.
     *
     * @return HasMany
     */
    public function types(): HasMany
    {
        return $this->hasMany(InvoiceType::class, 'discount_id', 'id');
    }
}
