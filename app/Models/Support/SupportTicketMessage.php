<?php

namespace App\Models\Support;

use App\Emails\Support\SupportTicketMessageNew;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class SupportTicketMessage
 *
 * This class is the model for basic ticket message metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $message
 * @property bool $note
 * @property bool $external
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property SupportTicket|null $ticket
 * @property User|null $user
 * @property string|null $email
 */
class SupportTicketMessage extends Model
{
    use Notifiable;
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
        'note' => 'bool',
        'external' => 'bool',
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
     * Send the email creation notification.
     *
     * @return void
     */
    public function sendEmailCreationNotification(): void
    {
        try {
            $this->notify(new SupportTicketMessageNew());
        } catch (Exception|Error $exception) {
        }
    }

    /**
     * Get the associated email address to send notifications to.
     *
     * @return string|null
     */
    public function getEmailAttribute(): ?string
    {
        return $this->ticket->email;
    }
}
