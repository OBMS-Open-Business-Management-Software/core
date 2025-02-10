<?php

declare(strict_types=1);

namespace App\Queue;

use App\Jobs\Structure\UniquelyQueueable;
use Illuminate\Queue\Jobs\RedisJob;
use Laravel\Horizon\RedisQueue;

class HorizonUniqueQueue extends RedisQueue
{
    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param string $queue
     * @param array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $connection = $this->getConnection();
        $tracker = $this->getTrackerName($queue);
        $data = json_decode($payload, true);
        $exists = isset($data['uniqueIdentifier']) && $connection->hexists($tracker, $data['uniqueIdentifier']);

        if ($exists) {
            return null;
        }

        return tap(
            parent::pushRaw($payload, $queue, $options),
            function ($result) use ($connection, $tracker, $data) {
                if ($result && isset($data['uniqueIdentifier'])) {
                    $connection->hset($tracker, $data['uniqueIdentifier'], $data['id']);
                }
            }
        );
    }

    /**
     * @param $queue
     * @return string
     */
    public function getTrackerName($queue): string
    {
        return $this->getQueue($queue) . ':tracker';
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param string $queue
     * @param RedisJob $job
     * @return void
     */
    public function deleteReserved($queue, $job)
    {
        parent::deleteReserved($queue, $job);

        $data = json_decode($job->getRawBody(), true);

        if (isset($data['uniqueIdentifier'])) {
            $this->getConnection()->hdel($this->getTrackerName($queue), $data['uniqueIdentifier']);
        }
    }

    /**
     * Create a payload for an object-based queue handler.
     *
     * @param mixed $job
     * @param string $queue
     *
     * @return array
     */
    protected function createObjectPayload($job, $queue)
    {
        if (in_array(UniquelyQueueable::class, class_uses($job))) {
            return array_merge(parent::createObjectPayload($job, $queue), [
                'uniqueIdentifier' => $job->getUniqueIdentifier(),
            ]);
        }

        return parent::createObjectPayload($job, $queue);
    }
}
