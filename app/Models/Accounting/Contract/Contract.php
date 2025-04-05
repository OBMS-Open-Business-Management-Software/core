<?php

declare(strict_types=1);

namespace App\Models\Accounting\Contract;

use App\Helpers\NumberRanges;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Contract.
 *
 * This class is the model for basic contract metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                            $id
 * @property int                            $user_id
 * @property int                            $type_id
 * @property float                          $reserved_prepaid_amount
 * @property Carbon|null                    $started_at
 * @property Carbon|null                    $last_invoice_at
 * @property Carbon|null                    $cancelled_at
 * @property Carbon|null                    $cancellation_revoked_at
 * @property Carbon|null                    $cancelled_to
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 * @property Carbon                         $deleted_at
 * @property User|null                      $user
 * @property ContractType|null              $type
 * @property Collection<ContractPosition>   $positionLinks
 * @property Collection<Invoice>            $invoices
 * @property Collection<ContractHistory>    $history
 * @property string                         $number
 * @property bool                           $started
 * @property bool                           $cancelled
 * @property bool                           $expired
 * @property bool                           $cancellationRevoked
 * @property string                         $status
 * @property float                          $netSum
 * @property float                          $grossSum
 * @property \Illuminate\Support\Collection $vatPositions
 */
class Contract extends Model
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
        'started_at'              => 'datetime',
        'last_invoice_at'         => 'datetime',
        'cancelled_at'            => 'datetime',
        'cancellation_revoked_at' => 'datetime',
        'cancelled_to'            => 'datetime',
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
     * Relation to type.
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(ContractType::class, 'id', 'type_id');
    }

    /**
     * Relation to position links.
     *
     * @return HasMany
     */
    public function positionLinks(): HasMany
    {
        return $this->hasMany(ContractPosition::class, 'contract_id', 'id');
    }

    /**
     * Relation to invoices.
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'contract_id', 'id');
    }

    /**
     * Relation to history.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(ContractHistory::class, 'contract_id', 'id');
    }

    /**
     * Get processed contract number.
     *
     * @return string
     */
    public function getNumberAttribute(): string
    {
        return NumberRanges::getNumber(self::class, $this);
    }

    /**
     * Identify if the contract has been started.
     *
     * @return bool
     */
    public function getStartedAttribute(): bool
    {
        return isset($this->started_at);
    }

    /**
     * Identify if the contract has been cancelled.
     *
     * @return bool
     */
    public function getCancelledAttribute(): bool
    {
        return isset($this->cancelled_at);
    }

    /**
     * Identify if the contract has expired.
     *
     * @return bool
     */
    public function getExpiredAttribute(): bool
    {
        return $this->cancelled &&
            $this->cancelled_to->lte(Carbon::now());
    }

    /**
     * Identify if the contract has been cancelled.
     *
     * @return bool
     */
    public function getCancellationRevokedAttribute(): bool
    {
        return isset($this->cancellation_revoked_at);
    }

    /**
     * Identify the contract status.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if (! $this->started) {
            return 'template';
        } else {
            if (! empty($this->cancelled_to)) {
                if ($this->cancelled_to->lte(Carbon::now())) {
                    return 'cancelled';
                }

                return 'expires';
            }

            return 'started';
        }
    }

    /**
     * Get net sum for invoice.
     *
     * @return float
     */
    public function getNetSumAttribute(): float
    {
        $netSum = 0;

        $this->positionLinks->each(function (ContractPosition $link) use (&$netSum) {
            $netSum += $link->position->netSum - $link->position->discountNetSum;
        });

        return (float) $netSum;
    }

    /**
     * Get gross sum for position.
     *
     * @return float
     */
    public function getGrossSumAttribute(): float
    {
        $grossSum = 0;

        $this->positionLinks->each(function (ContractPosition $link) use (&$grossSum) {
            if (! $this->user->reverseCharge) {
                $grossSum += $link->position->grossSum - $link->position->discountGrossSum;
            } else {
                $grossSum += $link->position->netSum - $link->position->discountNetSum;
            }
        });

        return (float) $grossSum;
    }

    public function getVatPositionsAttribute(): \Illuminate\Support\Collection
    {
        $vat = collect();

        if (! $this->user->reverseCharge) {
            $this->positionLinks->each(function (ContractPosition $link) use (&$vat) {
                if (! empty($position = $vat->pull($link->position->vat_percentage))) {
                    $vat->put($link->position->vat_percentage, $position + $link->position->vatSum - $link->position->discountVatSum);
                } else {
                    $vat->put($link->position->vat_percentage, $link->position->vatSum - $link->position->discountVatSum);
                }
            });
        }

        return $vat;
    }

    /**
     * Start a contract.
     *
     * @return bool
     */
    public function start(): bool
    {
        if ($this->status === 'template') {
            $this->update([
                'started_at'              => Carbon::now(),
                'last_invoice_at'         => null,
                'cancelled_at'            => null,
                'cancellation_revoked_at' => null,
                'cancelled_to'            => null,
            ]);
        }

        return false;
    }
}
