<?php

namespace App\Traints\Product;

use Illuminate\Support\Collection;

/**
 * Trait HasCapabilities.
 *
 * This trait defines the method to get capabilities.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasCapabilities
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

        if ($handlerTraints->contains('App\Traints\Product\HasService')) {
            $capabilities[] = 'service';

            $serviceTraints = collect(class_uses($this->model()) ?: []);

            if ($serviceTraints->contains('App\Traints\Product\Service\CanStart')) {
                $capabilities[] = 'service.status';
                $capabilities[] = 'service.start';
                $capabilities[] = 'service.stop';
                $capabilities[] = 'service.restart';
            }

            if ($serviceTraints->contains('App\Traints\Product\Service\HasStatistics')) {
                $capabilities[] = 'service.statistics';
            }
        }

        return collect($capabilities);
    }
}
