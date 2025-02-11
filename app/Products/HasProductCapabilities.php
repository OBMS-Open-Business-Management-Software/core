<?php

namespace App\Products;

use Illuminate\Support\Collection;

/**
 * Trait HasProductCapabilities.
 *
 * This trait defines the method to get capabilities.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasProductCapabilities
{
    /**
     * Get capabilities of an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function capabilities(): Collection
    {
        $capabilities = ['capabilities.reflect'];
        $handlerTraints = collect(class_uses(self::class) ?: []);

        if ($handlerTraints->contains('App\Products\HasProductService')) {
            $capabilities[] = 'service';

            $serviceTraints = collect(class_uses($this->model()) ?: []);

            if ($serviceTraints->contains('App\Products\CanServiceStart')) {
                $capabilities[] = 'service.status';
                $capabilities[] = 'service.start';
                $capabilities[] = 'service.stop';
                $capabilities[] = 'service.restart';
            }

            if ($serviceTraints->contains('App\Products\HasServiceStatistics')) {
                $capabilities[] = 'service.statistics';
            }
        }

        return collect($capabilities);
    }
}
