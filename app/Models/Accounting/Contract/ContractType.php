<?php

declare(strict_types=1);

namespace App\Models\Accounting\Contract;

use App\Models\Accounting\Invoice\InvoiceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ContractType.
 *
 * This class is the model for basic contract type metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int              $id
 * @property int              $invoice_type_id
 * @property string           $name
 * @property string           $description
 * @property string           $type
 * @property float|null       $invoice_period
 * @property float|null       $cancellation_period
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property Carbon           $deleted_at
 * @property InvoiceType|null $invoiceType
 */
class ContractType extends Model
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
     * Relation to invoice type.
     *
     * @return HasOne
     */
    public function invoiceType(): HasOne
    {
        return $this->hasOne(InvoiceType::class, 'id', 'invoice_type_id');
    }
}
