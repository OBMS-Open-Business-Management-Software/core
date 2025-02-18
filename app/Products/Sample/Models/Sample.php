<?php

namespace App\Products\Sample\Models;

use App\Traits\Product\Service\CanStart;
use App\Traits\Product\Service\HasStatistics;
use App\Products\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Sample
 *
 * This class is the model for basic sample service metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $contract_id
 * @property int $user_id
 * @property Carbon $locked_at
 *
 * @property boolean $locked
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
     * @var string[]|bool
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
