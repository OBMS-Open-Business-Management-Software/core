<?php

namespace App\Products;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasServiceStatistics.
 *
 * This trait allows a model instance to implement statistics functionality.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasServiceStatistics
{
    /**
     * Get the service status.
     */
    abstract public function statistics();
}
