<?php

declare(strict_types=1);

namespace App\Models\AI;

use App\Models\FileManager\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OpenaiMessage.
 *
 * This class is the model for OpenAI message metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int             $id
 * @property int             $openai_chat_id
 * @property int             $user_id
 * @property int             $file_id
 * @property string          $prompt
 * @property string          $answer
 * @property Carbon          $answered_at
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 * @property Carbon          $deleted_at
 * @property OpenaiChat|null $chat
 * @property User|null       $user
 * @property File|null       $file
 */
class OpenaiMessage extends Model
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
        'answered_at' => 'datetime',
    ];

    /**
     * Relation to chat.
     *
     * @return HasOne
     */
    public function chat(): HasOne
    {
        return $this->hasOne(OpenaiChat::class, 'id', 'openai_chat_id');
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
     * Relation to file.
     *
     * @return HasOne
     */
    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
