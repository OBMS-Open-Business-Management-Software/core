<?php

namespace App\Models\Support;

use App\Models\FileManager\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportTicketFile
 *
 * This class is the model for linking files with tickets metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property int $file_id
 * @property bool $external
 * @property bool $internal
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property SupportTicket|null $ticket
 * @property User|null $user
 * @property File|null $file
 */
class SupportTicketFile extends Model
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'external' => 'bool',
        'internal' => 'bool',
    ];

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
     * Relation to file.
     *
     * @return HasOne
     */
    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
