<?php

declare(strict_types=1);

namespace App\Models\AI;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OpenaiChat.
 *
 * This class is the model for OpenAI chat metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int       $id
 * @property int       $user_id
 * @property string    $external_id
 * @property Carbon    $created_at
 * @property Carbon    $updated_at
 * @property Carbon    $deleted_at
 * @property User|null $user
 */
class OpenaiChat extends Model
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
     * Relation to user.
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
