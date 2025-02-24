<?php

declare(strict_types=1);

namespace App\Models\Accounting\Invoice;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InvoiceDunning
 *
 * This class is the model for basic invoice dunning metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int        $id
 * @property int        $type_id
 * @property int        $after
 * @property float|null $period
 * @property float|null $fixed_amount
 * @property float|null $percentage_amount
 * @property bool       $cancel_contract_regular
 * @property bool       $cancel_contract_instant
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 * @property Carbon     $deleted_at
 */
class InvoiceDunning extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_dunning';

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
        'cancel_contract_regular' => 'bool',
        'cancel_contract_instant' => 'bool',
    ];

    /**
     * Relation to type.
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(InvoiceType::class, 'id', 'type_id');
    }
}
