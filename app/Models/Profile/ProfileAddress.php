<?php

declare(strict_types=1);

namespace App\Models\Profile;

use App\Models\Address\Address;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProfileAddress.
 *
 * This class is the model for linking profile with address metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int          $id
 * @property int          $profile_id
 * @property int          $address_id
 * @property string       $type
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 * @property Carbon       $deleted_at
 * @property Profile|null $profile
 * @property Address|null $address
 */
class ProfileAddress extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profile_addresses';

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
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    /**
     * Relation to address.
     *
     * @return HasOne
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }
}
