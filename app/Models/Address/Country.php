<?php

namespace App\Models\Address;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Address
 *
 * This class is the model for basic address metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property string $name
 * @property string $iso2
 * @property bool $eu
 * @property bool $reverse_charge
 * @property double $vat_basic
 * @property double|null $vat_reduced
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property Collection<Address>|null $addresses
 */
class Country extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'address_countries';

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
        'reverse_charge' => 'boolean',
        'eu' => 'boolean',
    ];

    /**
     * Relation to profile links.
     *
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'country_id', 'id');
    }
}
