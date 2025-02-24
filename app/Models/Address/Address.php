<?php

declare(strict_types=1);

namespace App\Models\Address;

use App\Models\Profile\ProfileAddress;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Address
 *
 * This class is the model for basic address metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                             $id
 * @property int                             $country_id
 * @property string                          $street
 * @property string                          $housenumber
 * @property string                          $addition
 * @property string                          $postalcode
 * @property string                          $city
 * @property string                          $state
 * @property Carbon                          $created_at
 * @property Carbon                          $updated_at
 * @property Carbon                          $deleted_at
 * @property Country|null                    $country
 * @property Collection<ProfileAddress>|null $profileLinks
 */
class Address extends Model
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
     * Relation to country.
     *
     * @return HasOne
     */
    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    /**
     * Relation to profile links.
     *
     * @return HasMany
     */
    public function profileLinks(): HasMany
    {
        return $this->hasMany(ProfileAddress::class, 'address_id', 'id');
    }
}
