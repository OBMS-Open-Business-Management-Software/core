<?php

namespace App\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Trait HasProductService.
 *
 * This trait defines the method to get model instance.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait HasProductService
{
    public static ?Collection $settings;

    /**
     * Get service model instance.
     *
     * @return Collection
     */
    public function get(int $id)
    {
        /* @var Model $modelName */
        $modelName = $this->model();

        return $modelName::find($id);
    }
}
