<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;

class LogFailedJob
{
    /**
     * Handle the event.
     *
     * @param JobFailed $event
     */
    public function handle(JobFailed $event): void
    {
        // Prevent requeueing by deleting the job
        $event->job->delete();
    }
}
