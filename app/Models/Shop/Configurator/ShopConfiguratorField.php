<?php

declare(strict_types=1);

namespace App\Models\Shop\Configurator;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShopConfiguratorField
 *
 * This class is the model for basic shop configurator field metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                                     $id
 * @property int|null                                $form_id
 * @property string                                  $type
 * @property string                                  $label
 * @property string                                  $key
 * @property string|null                             $value
 * @property string|null                             $value_prefix
 * @property string|null                             $value_suffix
 * @property float|null                              $amount
 * @property float|null                              $min
 * @property float|null                              $max
 * @property float|null                              $step
 * @property bool                                    $required
 * @property Carbon                                  $created_at
 * @property Carbon                                  $updated_at
 * @property Carbon                                  $deleted_at
 * @property ShopConfiguratorForm|null               $form
 * @property Collection<ShopConfiguratorFieldOption> $options
 * @property ShopConfiguratorFieldOption|null        $defaultOption
 */
class ShopConfiguratorField extends Model
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'required' => 'bool',
    ];

    /**
     * Relation to form.
     *
     * @return HasOne
     */
    public function form(): HasOne
    {
        return $this->hasOne(ShopConfiguratorForm::class, 'id', 'form_id');
    }

    /**
     * Relation to options.
     *
     * @return HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(ShopConfiguratorFieldOption::class, 'field_id', 'id');
    }

    /**
     * Get default option.
     *
     * @return ShopConfiguratorFieldOption|null
     */
    public function getDefaultOptionAttribute(): ?ShopConfiguratorFieldOption
    {
        if (! empty($defaultOption = $this->options->where('default', '=', true)->first())) {
            return $defaultOption;
        }

        if (! empty($defaultOption = $this->options->first())) {
            return $defaultOption;
        }

        return null;
    }
}
