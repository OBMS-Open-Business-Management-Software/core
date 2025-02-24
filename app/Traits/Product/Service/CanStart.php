<?php

declare(strict_types=1);

namespace App\Traits\Product\Service;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait CanStart.
 *
 * This trait allows a model instance to implement service start functionality.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait CanStart
{
    /**
     * Get the service status.
     *
     * @return bool
     */
    abstract public function status(): bool;

    /**
     * Allow the service to be started.
     *
     * @return bool
     */
    abstract public function start(): bool;

    /**
     * Allow the service to be stopped.
     *
     * @return bool
     */
    abstract public function stop(): bool;

    /**
     * Allow the service to be restarted.
     *
     * @return bool
     */
    public function restart(): bool
    {
        if ($this->status($id)) {
            $this->stop($id);
        }

        return $this->start($id);
    }
}
