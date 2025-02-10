<?php

declare(strict_types=1);

namespace App\Queue;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Arr;
use Laravel\Horizon\Connectors\RedisConnector;

class HorizonUniqueConnector extends RedisConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return Queue
     */
    public function connect(array $config)
    {
        return new HorizonUniqueQueue(
            $this->redis,
            $config['queue'],
            Arr::get($config, 'connection', $this->connection),
            Arr::get($config, 'retry_after', 60)
        );
    }
}
