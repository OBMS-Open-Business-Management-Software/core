<?php

namespace App\Models\Shop\Configurator;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShopConfiguratorFieldOption
 *
 * This class is the model for basic shop configurator field option metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int $id
 * @property int|null $field_id
 * @property string $label
 * @property string $value
 * @property double|null $amount
 * @property bool $default
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @property ShopConfiguratorField|null $field
 */
class ShopConfiguratorFieldOption extends Model
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
        'default' => 'bool',
    ];

    /**
     * Relation to field.
     *
     * @return HasOne
     */
    public function field(): HasOne
    {
        return $this->hasOne(ShopConfiguratorField::class, 'id', 'field_id');
    }
}
