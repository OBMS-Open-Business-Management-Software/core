<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Accounting\Invoice\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Payment
 *
 * This class is the model for basic payment log metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int          $id
 * @property int          $user_id
 * @property int|null     $invoice_id
 * @property string       $method
 * @property float        $amount
 * @property string       $transaction_id
 * @property int          $transaction_status
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property Carbon       $deleted_at
 * @property User|null    $user
 * @property Invoice|null $invoice
 */
class Payment extends Model
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
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to invoice.
     *
     * @return HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }
}
