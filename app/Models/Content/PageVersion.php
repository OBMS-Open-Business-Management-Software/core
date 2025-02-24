<?php

declare(strict_types=1);

namespace App\Models\Content;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;

/**
 * Class PageVersion.
 *
 * This class is the model for basic page version metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                        $id
 * @property int                        $page_id
 * @property int                        $user_id
 * @property string                     $content
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property Carbon                     $deleted_at
 * @property Page|null                  $page
 * @property User|null                  $user
 * @property Collection<PageAcceptance> $acceptance
 * @property string                     $compiled
 */
class PageVersion extends Model
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
     * Relation to page.
     *
     * @return HasOne
     */
    public function page(): HasOne
    {
        return $this->hasOne(Page::class, 'id', 'page_id');
    }

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Relation to page acceptances.
     *
     * @return HasMany
     */
    public function acceptance(): HasMany
    {
        return $this->hasMany(PageAcceptance::class, 'page_version_id', 'id');
    }

    /**
     * Get compiled page content.
     *
     * @return string
     */
    public function getCompiledAttribute(): string
    {
        return Blade::compileString($this->content);
    }
}
