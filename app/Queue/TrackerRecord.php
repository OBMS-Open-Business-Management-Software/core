<?php

declare(strict_types=1);

namespace App\Queue;

class TrackerRecord
{
    /**
     * @var int
     */
    protected $jobId;

    /**
     * @var int
     */
    protected $queuedAt;

    /**
     * @var int|null
     */
    protected $supervisorPid;

    /**
     * TrackerRecord constructor.
     *
     * @param string $jobId The associated queued job
     */
    public function __construct(string $jobId)
    {
        $this->jobId    = $jobId;
        $this->queuedAt = time();
    }

    /**
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * @return int
     */
    public function getQueuedAt(): int
    {
        return $this->queuedAt;
    }

    /**
     * @param int $pid
     */
    public function setPid(int $pid): void
    {
        if ($this->supervisorPid !== null) {
            return;
        }

        $this->supervisorPid = $pid;
    }

    /**
     * @return int|null
     */
    public function getPid(): ?int
    {
        return $this->supervisorPid;
    }
}
