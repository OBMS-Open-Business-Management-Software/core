<?php

declare(strict_types=1);

namespace App\Models\Shop\Configurator;

use App\Models\Accounting\Contract\ContractType;
use App\Models\Address\Country;
use App\Models\UsageTracker\Tracker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class ShopConfiguratorForm
 *
 * This class is the model for basic shop configurator form metadata.
 *
 * A form consists of different fields which can hold different types
 * of data. The selectors available are:
 * - input type="text"
 * - input type="number"
 * - input type="range"
 * - input type="radio"
 * - input type="radio" content="image"
 * - input type="checkbox"
 * - select
 * - hidden
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                               $id
 * @property int|null                          $category_id
 * @property int|null                          $contract_type_id
 * @property int|null                          $tracker_id
 * @property string                            $type
 * @property string                            $route
 * @property string                            $name
 * @property string                            $description
 * @property string                            $product_type
 * @property bool                              $approval
 * @property bool                              $public
 * @property string                            $vat_type
 * @property Carbon                            $created_at
 * @property Carbon                            $updated_at
 * @property Carbon                            $deleted_at
 * @property ShopConfiguratorCategory|null     $category
 * @property ContractType|null                 $contractType
 * @property Tracker|null                      $tracker
 * @property Collection<ShopConfiguratorField> $fields
 * @property string                            $fullRoute
 * @property float                             $minAmount
 * @property float                             $baseAmount
 * @property float                             $defaultAmount
 * @property float                             $vatRate
 * @property bool                              $reverseCharge
 */
class ShopConfiguratorForm extends Model
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
        'approval' => 'bool',
        'public'   => 'bool',
    ];

    /**
     * Relation to category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(ShopConfiguratorCategory::class, 'id', 'category_id');
    }

    /**
     * Relation to contract type.
     *
     * @return HasOne
     */
    public function contractType(): HasOne
    {
        return $this->hasOne(ContractType::class, 'id', 'contract_type_id');
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
     * Relation to fields.
     *
     * @return HasMany
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ShopConfiguratorField::class, 'form_id', 'id');
    }

    /**
     * Get full route.
     *
     * @return string
     */
    public function getFullRouteAttribute(): string
    {
        if (! empty($category = $this->category)) {
            return $category->fullRoute . __($this->route) . '/';
        }

        return '/shop/' . __($this->route) . '/';
    }

    /**
     * Get minimum amount of product configuration.
     *
     * @return float
     */
    public function getMinAmountAttribute(): float
    {
        $price = 0;

        $this->fields->each(function (ShopConfiguratorField $field) use (&$price) {
            if (! in_array($field->type, [
                'input_number',
                'input_range',
                'input_checkbox',
            ])) {
                $minFieldPrice = $field->amount;
            } else {
                $minFieldPrice = 0;
            }

            if (in_array($field->type, [
                'input_number',
                'input_range',
            ])) {
                $minFieldPrice = $field->min / $field->step * $field->amount;
            }

            if (
                $field->type == 'input_checkbox' &&
                $field->required
            ) {
                $minFieldPrice = $minFieldPrice + $field->amount;
            }

            $minOptionPrice = null;

            $field->options->each(function (ShopConfiguratorFieldOption $option) use (&$minOptionPrice) {
                if (! isset($minOptionPrice) || $minOptionPrice > $option->amount) {
                    $minOptionPrice = $option->amount;
                }
            });

            if (! isset($minOptionPrice)) {
                $minOptionPrice = 0;
            }

            $price = $price + $minFieldPrice + $minOptionPrice;
        });

        return $price;
    }

    /**
     * Get base amount of product configuration.
     *
     * @return float
     */
    public function getBaseAmountAttribute(): float
    {
        $price = 0;

        $this->fields->each(function (ShopConfiguratorField $field) use (&$price) {
            if (! in_array($field->type, [
                'input_number',
                'input_range',
                'input_checkbox',
            ])) {
                $minFieldPrice = $field->amount;

                if (! isset($minFieldPrice)) {
                    $minFieldPrice = 0;
                }

                $price += $minFieldPrice;
            }
        });

        return $price;
    }

    /**
     * Get default amount of product configuration.
     *
     * @return float
     */
    public function getDefaultAmountAttribute(): float
    {
        $price = 0;

        $this->fields->each(function (ShopConfiguratorField $field) use (&$price) {
            if (! in_array($field->type, [
                'input_number',
                'input_range',
                'input_checkbox',
            ])) {
                $minFieldPrice = $field->amount;
            } else {
                $minFieldPrice = 0;
            }

            if (in_array($field->type, [
                'input_number',
                'input_range',
            ])) {
                $minFieldPrice = $minFieldPrice + $field->value / $field->step * $field->amount;
            }

            $defaultOptionPrice = null;

            if (! empty($defaultOption = $field->options->where('default', '=', true)->first())) {
                $defaultOptionPrice = $defaultOption->amount;
            }

            if (! isset($defaultOptionPrice) && ! empty($defaultOption = $field->options->first())) {
                $defaultOptionPrice = $defaultOption->amount;
            }

            if (! isset($defaultOptionPrice)) {
                $defaultOptionPrice = 0;
            }

            $price = $price + $minFieldPrice + $defaultOptionPrice;
        });

        return $price;
    }

    /**
     * Get applicable VAT rate.
     *
     * @return float
     */
    public function getVatRateAttribute(): float
    {
        $defaultCountryVat = Country::find(config('company.default_country'))->{'vat_' . $this->vat_type} ?? null;

        if (
            ! empty($user = Auth::user()) &&
            ! empty($address = $user->billingPostalAddress) &&
            ! empty($country = $address->country)
        ) {
            return $country->{'vat_' . $this->vat_type} ?? $defaultCountryVat ?? 0;
        }

        return $defaultCountryVat ?? config('company.default_vat_rate');
    }

    /**
     * Get reverse-charge applicability.
     *
     * @return bool
     */
    public function getReverseChargeAttribute(): bool
    {
        if (
            ! empty($user = Auth::user()) &&
            ! empty($address = $user->billingPostalAddress) &&
            ! empty($country = $address->country)
        ) {
            return $country->reverse_charge;
        }

        return false;
    }
}
