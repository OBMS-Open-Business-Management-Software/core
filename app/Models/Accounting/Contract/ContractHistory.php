<?php

declare(strict_types=1);

namespace App\Models\Accounting\Contract;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ContractHistory.
 *
 * This class is the model for basic contract history metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int           $id
 * @property int|null      $user_id
 * @property int           $contract_id
 * @property string        $status
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon        $deleted_at
 * @property User|null     $user
 * @property Contract|null $contract
 */
class ContractHistory extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contract_history';

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

    /**
     * Relation to invoice.
     *
     * @return HasOne
     */
    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }
}
