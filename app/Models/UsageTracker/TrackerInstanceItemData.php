<?php

namespace App\Models\UsageTracker;

use App\Models\Accounting\Contract\Contract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TrackerInstanceItemData
 *
 * This class is the model for tracker instance data metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $instance_id
 * @property int $item_id
 * @property string $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property TrackerInstance|null $instance
 * @property TrackerItem|null $item
 */
class TrackerInstanceItemData extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tracker_instance_item_data';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relation to instance.
     *
     * @return HasOne
     */
    public function instance(): HasOne
    {
        return $this->hasOne(TrackerInstance::class, 'id', 'instance_id');
    }

    /**
     * Relation to item.
     *
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(TrackerItem::class, 'id', 'item_id');
    }
}
