<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportTicketAssignment.
 *
 * This class is the model for linking users with tickets metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                $id
 * @property int                $ticket_id
 * @property int                $user_id
 * @property string             $role
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon             $deleted_at
 * @property SupportTicket|null $ticket
 * @property User|null          $user
 */
class SupportTicketAssignment extends Model
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
}
