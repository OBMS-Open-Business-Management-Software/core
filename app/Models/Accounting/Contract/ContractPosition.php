<?php

declare(strict_types=1);

namespace App\Models\Accounting\Contract;

use App\Models\Accounting\Position;
use App\Models\UsageTracker\TrackerInstance;
use Awobaz\Compoships\Compoships;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ContractPosition.
 *
 * This class is the model for linking contract with position metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                  $id
 * @property int                  $contract_id
 * @property int                  $position_id
 * @property Carbon               $started_at
 * @property Carbon               $ended_at
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property Contract|null        $contract
 * @property Position|null        $position
 * @property TrackerInstance|null $trackerInstance
 */
class ContractPosition extends Model
{
    use Compoships;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /**
     * Relation to contract.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    /**
     * Relation to position.
     *
     * @return HasOne
     */
    public function position(): HasOne
    {
        return $this->hasOne(Position::class, 'id', 'position_id');
    }

    /**
     * Relation to tracker instance.
     *
     * @return HasOne
     */
    public function trackerInstance(): HasOne
    {
        return $this->hasOne(TrackerInstance::class, ['contract_position_id', 'contract_id'], ['id', 'contract_id']);
    }
}
