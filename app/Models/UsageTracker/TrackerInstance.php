<?php

declare(strict_types=1);

namespace App\Models\UsageTracker;

use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Contract\ContractPosition;
use Awobaz\Compoships\Compoships;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TrackerInstance
 *
 * This class is the model for tracker instance metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                   $id
 * @property int                   $contract_id
 * @property int|null              $contract_position_id
 * @property int                   $tracker_id
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 * @property Carbon                $deleted_at
 * @property Contract|null         $contract
 * @property ContractPosition|null $contractPosition
 * @property Tracker|null          $tracker
 * @property float|null            $vat_percentage
 */
class TrackerInstance extends Model
{
    use Compoships;
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
     * Relation to contract.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    /**
     * Relation to contract position link.
     *
     * @return HasOne
     */
    public function contractPosition(): HasOne
    {
        return $this->hasOne(ContractPosition::class, 'id', 'contract_position_id');
    }

    /**
     * Relation to tracker.
     *
     * @return HasOne
     */
    public function tracker(): HasOne
    {
        return $this->hasOne(Tracker::class, 'id', 'tracker_id');
    }

    /**
     * Relation to data.
     *
     * @return HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(TrackerInstanceItemData::class, 'instance_id', 'id');
    }

    /**
     * Get vat_percentage attribute.
     *
     * @return float|null
     */
    public function getvat_percentageAttribute(): ?float
    {
        if (
            ! empty($contract = $this->contract) &&
            ! empty($user = $contract->user) &&
            ! empty($address = $user->billingPostalAddress) &&
            ! empty($country = $address->country)
        ) {
            return $country->{'vat_' . $this->tracker->vat_type};
        }

        if (
            ! empty($positionLink = $this->contractPosition) &&
            ! empty($position = $positionLink->position)
        ) {
            return $position->vat_percentage;
        }

        return null;
    }

    /**
     * Calculate billable net amount.
     *
     * @param Carbon      $from
     * @param Carbon      $to
     * @param object|null $position
     *
     * @return float
     */
    public function calculate(Carbon $from, Carbon $to, ?object &$position = null): float
    {
        $amount      = 0;
        $description = __($this->tracker->description);

        if ($this->tracker->items->isNotEmpty()) {
            $description .= '<br><br><ul>';

            $this->tracker->items->each(function (TrackerItem $item) use ($from, $to, &$amount, &$description) {
                $data = $item->data()
                    ->where('created_at', '>=', $from)
                    ->where('created_at', '<=', $to)
                    ->where('instance_id', '=', $this->id)
                    ->get()
                    ->pluck('data');

                switch ($item->type) {
                    case 'string':
                        if (
                            $item->process == 'equals' &&
                            $data->last() == $item->step
                        ) {
                            $amount += $item->amount;
                        }

                        break;
                    case 'integer':
                    case 'double':
                        switch ($item->process) {
                            case 'min':
                                $value = $data->min();

                                break;
                            case 'median':
                                $value = $data->median();

                                break;
                            case 'average':
                                $value = $data->average();

                                break;
                            case 'max':
                                $value = $data->max();

                                break;
                            default:
                                $value = $data->last();
                        }

                        $diff = $value % $item->step;

                        switch ($item->round) {
                            case 'up':
                                if ($diff > 0) {
                                    $value = $value - $diff + (float) $item->step;
                                }

                                break;
                            case 'down':
                                if ($diff > 0) {
                                    $value = $value - $diff;
                                }

                                break;
                            case 'regular':
                                $roundUpFrom = (float) $item->step / 2;

                                if ($diff > $roundUpFrom) {
                                    $value = $value - $diff + (float) $item->step;
                                } else {
                                    $value = $value - $diff;
                                }

                                break;
                            case 'none':
                            default:
                                // Do nothing.
                                break;
                        }

                        $factor = $value / (float) $item->step;

                        $amount += $factor * $item->amount;

                        $description .= '<li>' . __('interface.data.tracker_item_id', ['id' => $item->id]) . ' (' . __('interface.data.value_value', ['value' => $value]) . ' | ' . __('interface.data.price_price', ['price' => number_format($amount, 2)]) . ')</li>';

                        break;
                }
            });

            $description .= '<br><br><ul>';

            $position = (object) [
                'name'           => __($this->tracker->name),
                'description'    => $description,
                'amount'         => $amount,
                'vat_percentage' => $this->vat_percentage,
                'quantity'       => 1,
            ];
        }

        return $amount;
    }
}
