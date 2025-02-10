<?php

namespace App\Products\Sample\Models;

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
}
