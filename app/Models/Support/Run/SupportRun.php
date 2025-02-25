<?php

declare(strict_types=1);

namespace App\Models\Support\Run;

use App\Models\Support\Category\SupportCategory;
use App\Models\Support\SupportTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportTicket.
 *
 * This class is the model for basic ticket metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                           $id
 * @property int                           $category_id
 * @property int                           $ticket_id
 * @property int                           $user_id
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property Carbon                        $deleted_at
 * @property SupportCategory|null          $category
 * @property SupportTicket|null            $ticket
 * @property User|null                     $user
 * @property Collection<SupportRunHistory> $history
 */
class SupportRun extends Model
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
     * Relation to ticket category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(SupportCategory::class, 'id', 'category_id');
    }

    /**
     * Relation to ticket.
     *
     * @return HasOne
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(SupportTicket::class, 'id', 'ticket_id');
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
     * Relation to history.
     *
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(SupportRunHistory::class, 'run_id', 'id');
    }
}
