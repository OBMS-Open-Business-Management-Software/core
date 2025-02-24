<?php

declare(strict_types=1);

namespace App\Models\UsageTracker;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tracker.
 *
 * This class is the model for tracker metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                         $id
 * @property string                      $name
 * @property string                      $description
 * @property string                      $vat_type
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 * @property Carbon                      $deleted_at
 * @property Collection<TrackerInstance> $instances
 * @property Collection<TrackerItem>     $items
 */
class Tracker extends Model
{
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
     * Relation to instances.
     *
     * @return HasMany
     */
    public function instances(): HasMany
    {
        return $this->hasMany(TrackerInstance::class, 'tracker_id', 'id');
    }

    /**
     * Relation to instances.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(TrackerItem::class, 'tracker_id', 'id');
    }
}
