<?php

namespace App\Models\Support;

use App\Models\FileManager\File;
use App\Models\Support\Category\SupportCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportTicketHistory
 *
 * This class is the model for support ticket history metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $type
 * @property string $action
 * @property string $reference
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property SupportTicket|null $ticket
 * @property User|null $user
 */
class SupportTicketHistory extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticket_history';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
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
     * Get reference attribute.
     */
    public function getreferenceAttributeAttribute()
    {
        $result = null;

        if ($this->type == 'assignment') {
            if (! empty($this->reference)) {
                $result = User::find($this->reference);
            }

            if (empty($result)) {
                $result = User::find($this->user_id);
            }
        } elseif (
            $this->type == 'category' &&
            ! empty($this->reference)
        ) {
            $result = SupportCategory::find($this->reference);
        } elseif (
            $this->type == 'file' &&
            ! empty($this->reference)
        ) {
            $result = File::find($this->reference);
        }

        return $result;
    }
}
