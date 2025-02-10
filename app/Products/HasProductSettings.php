<?php

namespace App\Products;

use App\Models\PaymentGatewaySetting;
use App\Models\ProductSetting;
use Illuminate\Support\Collection;

/**
 * Trait HasGatewaySettings.
 *
 * This trait defines the method to get settings.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasProductSettings
{
    public static ?Collection $settings;

    /**
     * Get a list of set settings for the payment method technical name.
     *
     * @return Collection
     */
    public function settings(): Collection
    {
        if (! isset(static::$settings)) {
            static::$settings = ProductSetting::where('product_type', '=', $this->technicalName())
                ->get()
                ->toBase();
        }

        return static::$settings;
    }
}
