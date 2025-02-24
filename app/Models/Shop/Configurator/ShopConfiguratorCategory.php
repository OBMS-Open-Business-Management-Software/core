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
 * Class ShopConfiguratorCategory
 *
 * This class is the model for basic shop configurator category metadata.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 *
 * @property int                                  $id
 * @property int|null                             $category_id
 * @property string                               $route
 * @property string                               $name
 * @property string                               $description
 * @property bool                                 $public
 * @property Carbon                               $created_at
 * @property Carbon                               $updated_at
 * @property Carbon                               $deleted_at
 * @property ShopConfiguratorCategory|null        $category
 * @property Collection<ShopConfiguratorForm>     $forms
 * @property Collection<ShopConfiguratorCategory> $categories
 * @property string                               $fullRoute
 */
class ShopConfiguratorCategory extends Model
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
        'public' => 'bool',
    ];

    /**
     * Relation to category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(ShopConfiguratorCategory::class, 'id', 'category_id');
    }

    /**
     * Relation to forms.
     *
     * @return HasMany
     */
    public function forms(): HasMany
    {
        return $this->hasMany(ShopConfiguratorForm::class, 'category_id', 'id');
    }

    /**
     * Relation to forms.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ShopConfiguratorCategory::class, 'category_id', 'id');
    }

    /**
     * Get full route.
     *
     * @return string
     */
    public function getFullRouteAttribute(): string
    {
        if (! empty($category = $this->category)) {
            return $category->fullRoute . __($this->route) . '/';
        }

        return '/shop/' . __($this->route) . '/';
    }
}
