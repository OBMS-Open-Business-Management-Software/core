<?php

declare(strict_types=1);

namespace App\Products\Sample\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use OBMS\ModuleSDK\Products\Service;
use OBMS\ModuleSDK\Products\Traits\CanStart;
use OBMS\ModuleSDK\Products\Traits\HasStatistics;

/**
 * Class Sample.
 *
 * This class is the model for basic sample service metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int    $id
 * @property int    $contract_id
 * @property int    $user_id
 * @property Carbon $locked_at
 * @property bool   $locked
 */
class Sample extends Service
{
    use CanStart;
    use HasStatistics;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_sample';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Get the product instance status.
     *
     * @return bool
     */
    public function status(): bool
    {
        sleep(2);

        return true;
    }

    /**
     * Start the product instance.
     *
     * @return bool
     */
    public function start(): bool
    {
        sleep(2);

        return true;
    }

    /**
     * Stop the product instance.
     *
     * @return bool
     */
    public function stop(): bool
    {
        sleep(2);

        return true;
    }

    /**
     * Restart the product instance.
     *
     * @return bool
     */
    public function restart(): bool
    {
        sleep(2);

        return true;
    }

    /**
     * Get the service status.
     */
    public function statistics()
    {
        return null;
    }
}
