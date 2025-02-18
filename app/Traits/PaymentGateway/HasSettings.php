<?php

namespace App\Traints\PaymentGateway;

use App\Models\PaymentGatewaySetting;
use Illuminate\Support\Collection;

/**
 * Trait HasSettings.
 *
 * This trait defines the method to get settings.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasSettings
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
            static::$settings = PaymentGatewaySetting::where('gateway', '=', $this->technicalName())
                ->get()
                ->toBase();
        }

        return static::$settings;
    }
}
