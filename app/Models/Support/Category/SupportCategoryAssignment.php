<?php

declare(strict_types=1);

namespace App\Models\Support\Category;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportCategoryAssignment.
 *
 * This class is the model for linking users with ticket category metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                  $id
 * @property int                  $category_id
 * @property int                  $user_id
 * @property string               $role
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 * @property SupportCategory|null $category
 * @property User|null            $user
 */
class SupportCategoryAssignment extends Model
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
     * Relation to ticket.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(SupportCategory::class, 'id', 'category_id');
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
}
