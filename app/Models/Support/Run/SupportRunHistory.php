<?php

declare(strict_types=1);

namespace App\Models\Support\Run;

use App\Models\Support\SupportTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupportRunHistory
 *
 * This class is the model for linking history with ticket runs metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                $id
 * @property int                $run_id
 * @property int                $user_id
 * @property int                $ticket_id
 * @property string             $type
 * @property string             $action
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon             $deleted_at
 * @property SupportRun|null    $run
 * @property User|null          $user
 * @property SupportTicket|null $ticket
 */
class SupportRunHistory extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_run_history';

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
    public function run(): HasOne
    {
        return $this->hasOne(SupportRun::class, 'id', 'run_id');
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
     * Relation to ticket.
     *
     * @return HasOne
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'ticket_id');
    }
}
