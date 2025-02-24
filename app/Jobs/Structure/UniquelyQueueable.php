<?php

declare(strict_types=1);

namespace App\Jobs\Structure;

use Illuminate\Queue\QueueManager;

/**
 * Trait UniquelyQueueable.
 *
 * Makes a job uniquely queueable. Uniquely queueable jobs can't only not run
 * at the same time, but they cannot be even queued twice simultaneously.
 */
trait UniquelyQueueable
{
    /**
     * Provides a unique identifier for existence check
     * This should be implemented in each job.
     *
     * @return string
     */
    abstract public function getUniqueIdentifier(): string;

    public function getTrackerName(): string
    {
        return app()->get(QueueManager::class)->connection()->getTrackerName($this->queue);
    }
}
