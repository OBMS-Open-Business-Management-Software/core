<?php

declare(strict_types=1);

namespace App\Products\Sample;

use App\Products\Sample\Models\Sample;
use Illuminate\Support\Collection;
use OBMS\ModuleSDK\Products\Product;
use OBMS\ModuleSDK\Products\Traits\HasCapabilities;
use OBMS\ModuleSDK\Products\Traits\HasService;
use OBMS\ModuleSDK\Products\Traits\HasSettings;

/**
 * Class Handler.
 *
 * This class is a product handler sample.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Handler implements Product
{
    use HasCapabilities;
    use HasSettings;
    use HasService;

    /**
     * Register the parameters which are being used by the product
     * (e.g. to authenticate against the API).
     *
     * @return Collection
     */
    public function parameters(): Collection
    {
        return collect([
            'test' => __('sample.test'),
        ]);
    }

    /**
     * Get product technical name.
     *
     * @return string
     */
    public function technicalName(): string
    {
        return 'sample';
    }

    /**
     * Get product path.
     *
     * @return string
     */
    public function folderName(): string
    {
        return dirname(__FILE__);
    }

    /**
     * Get product name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('sample.test');
    }

    /**
     * Get product icon src.
     *
     * @return string
     */
    public function icon(): ?string
    {
        return 'bi bi-app';
    }

    /**
     * Get product shop status.
     *
     * @return bool
     */
    public function status(): bool
    {
        return true;
    }

    /**
     * Enable user interface capabilities.
     *
     * @return object
     */
    public function ui(): object
    {
        return (object) [
            'admin'    => true,
            'customer' => true,
        ];
    }

    /**
     * Create a new product instance.
     *
     * @param Collection $data
     *
     * @return bool
     */
    public function create(Collection $data): bool
    {
        return false;
    }

    /**
     * Update an existing product instance.
     *
     * @param int        $id
     * @param Collection $data
     *
     * @return bool
     */
    public function update(int $id, Collection $data): bool
    {
        return false;
    }

    /**
     * Delete an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        return false;
    }

    /**
     * Lock an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function lock(int $id): bool
    {
        return false;
    }

    /**
     * Unlock an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function unlock(int $id): bool
    {
        return false;
    }

    /**
     * Get the model attached to the service. The model is at least required
     * to carry a locked_at, contract_id and user_id attribute to identify the contractual status.
     *
     * @return string
     */
    public function model(): string
    {
        return Sample::class;
    }

    /**
     * Grace period in hours.
     *
     * @return int
     */
    public function grace(): ?int
    {
        return null;
    }
}
