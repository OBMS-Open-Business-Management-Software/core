<?php

declare(strict_types=1);

namespace App\Models\Content;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PageVersion
 *
 * This class is the model for basic page version metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int       $id
 * @property int       $page_id
 * @property int       $page_version_id
 * @property int       $user_id
 * @property string    $ip
 * @property string    $user_agent
 * @property string    $signature
 * @property Carbon    $signed_at
 * @property Carbon    $created_at
 * @property Carbon    $updated_at
 * @property Carbon    $deleted_at
 * @property Page|null $page
 * @property User|null $user
 */
class PageAcceptance extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'page_acceptance';

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
        'signed_at' => 'datetime',
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
     * Relation to page version.
     *
     * @return HasOne
     */
    public function pageVersion(): HasOne
    {
        return $this->hasOne(PageVersion::class, 'id', 'page_version_id');
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
     * Check if signature matches.
     *
     * @return bool
     */
    public function getSignedAttribute(): bool
    {
        return md5($this->page_id . $this->page_version_id . $this->user_id . $this->user_agent . $this->ip . $this->signed_at) === $this->signature;
    }
}
