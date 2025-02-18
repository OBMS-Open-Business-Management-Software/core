<?php

namespace App\Traits\Product\Service;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasStatistics.
 *
 * This trait allows a model instance to implement statistics functionality.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasStatistics
{
    /**
     * Get the service status.
     */
    abstract public function statistics();
}
