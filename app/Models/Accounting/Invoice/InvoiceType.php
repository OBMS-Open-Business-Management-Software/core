<?php

declare(strict_types=1);

namespace App\Models\Accounting\Invoice;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InvoiceType.
 *
 * This class is the model for basic invoice type metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                        $id
 * @property int|null                   $discount_id
 * @property string                     $name
 * @property string                     $description
 * @property string                     $type
 * @property float|null                 $period
 * @property bool                       $dunning
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property Carbon                     $deleted_at
 * @property Collection<InvoiceDunning> $dunnings
 * @property Collection<Invoice>        $invoices
 * @property InvoiceDiscount|null       $discount
 */
class InvoiceType extends Model
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'dunning' => 'bool',
    ];

    /**
     * Relation to dunnings.
     *
     * @return HasMany
     */
    public function dunnings(): HasMany
    {
        return $this->hasMany(InvoiceDunning::class, 'type_id', 'id');
    }

    /**
     * Relation to invoices.
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'type_id', 'id');
    }

    /**
     * Relation to discount.
     *
     * @return HasOne
     */
    public function discount(): HasOne
    {
        return $this->hasOne(InvoiceDiscount::class, 'id', 'discount_id');
    }
}
