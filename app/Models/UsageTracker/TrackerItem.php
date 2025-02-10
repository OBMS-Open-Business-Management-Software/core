<?php

namespace App\Models\UsageTracker;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TrackerItem
 *
 * This class is the model for tracker item metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $tracker_id
 * @property string $type
 * @property string $process
 * @property string $round
 * @property string $step
 * @property double $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property Tracker|null $tracker
 */
class TrackerItem extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to tracker.
     *
     * @return HasOne
     */
    public function tracker(): HasOne
    {
        return $this->hasOne(Tracker::class, 'id', 'tracker_id');
    }

    /**
     * Relation to data.
     *
     * @return HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(TrackerInstanceItemData::class, 'item_id', 'id');
    }
}
