<?php

declare(strict_types=1);

namespace App\Models\Content;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Page.
 *
 * This class is the model for basic page metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                        $id
 * @property string                     $route
 * @property string                     $title
 * @property bool                       $must_accept
 * @property bool                       $navigation_item
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property Carbon                     $deleted_at
 * @property Collection<PageVersion>    $versions
 * @property Collection<PageAcceptance> $acceptance
 * @property PageVersion                $latest
 *
 * @method static Builder acceptable()
 * @method static Builder navigateable()
 */
class Page extends Model
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'must_accept'     => 'bool',
        'navigation_item' => 'bool',
    ];

    /**
     * Get a list of acceptable texts.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public static function scopeAcceptable(Builder $query): Builder
    {
        return $query->where('must_accept', '=', true);
    }

    /**
     * Get a list of navigateable texts.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public static function scopeNavigateable(Builder $query): Builder
    {
        return $query->where('navigation_item', '=', true);
    }

    /**
     * Relation to page versions.
     *
     * @return HasMany
     */
    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class, 'page_id', 'id');
    }

    /**
     * Relation to page acceptances.
     *
     * @return HasMany
     */
    public function acceptance(): HasMany
    {
        return $this->hasMany(PageAcceptance::class, 'page_id', 'id');
    }

    /**
     * Get latest page version.
     *
     * @return PageVersion|null
     */
    public function getLatestAttribute(): ?PageVersion
    {
        return $this->versions()
            ->orderByDesc('created_at')
            ->first();
    }
}
