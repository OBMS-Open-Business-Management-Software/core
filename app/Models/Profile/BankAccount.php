<?php

namespace App\Models\Profile;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BankAccount
 *
 * This class is the model for basic bank account metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int $profile_id
 * @property string $iban
 * @property string $bic
 * @property string $bank
 * @property string $owner
 * @property bool $primary
 * @property string $sepa_mandate
 * @property Carbon $sepa_mandate_signed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property Profile|null $profile
 */
class BankAccount extends Model
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
        'primary' => 'boolean',
        'sepa_signed_at' => 'datetime',
    ];

    /**
     * Relation to user.
     *
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }
}
