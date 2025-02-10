<?php

namespace App\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Interface Product.
 *
 * This interface defines the structure for product implementations.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
interface Product
{
    /**
     * Register the parameters which are being used by the product
     * (e.g. to authenticate against the API).
     *
     * @return Collection
     */
    public function parameters(): Collection;

    /**
     * Get a list of set settings for the product technical name.
     *
     * @return Collection
     */
    public function settings(): Collection;

    /**
     * Get product technical name.
     *
     * @return string
     */
    public function technicalName(): string;

    /**
     * Get product folder name.
     *
     * @return string
     */
    public function folderName(): string;

    /**
     * Get product name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get product icon src.
     *
     * @return string
     */
    public function icon(): ?string;

    /**
     * Get product shop status.
     *
     * @return bool
     */
    public function status(): bool;

    /**
     * Enable user interface capabilities.
     *
     * @return object
     */
    public function ui(): object;

    /**
     * Get an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function get(int $id);

    /**
     * Create a new product instance.
     *
     * @param Collection $data
     *
     * @return bool
     */
    public function create(Collection $data): bool;

    /**
     * Update an existing product instance.
     *
     * @param int $id
     * @param Collection $data
     *
     * @return bool
     */
    public function update(int $id, Collection $data): bool;

    /**
     * Delete an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lock an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function lock(int $id): bool;

    /**
     * Unlock an existing product instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function unlock(int $id): bool;

    /**
     * Get the model attached to the service. The model is at least required
     * to carry a locked_at, contract_id and user_id attribute to identify the contractual status.
     *
     * @return string
     */
    public function model(): string;

    /**
     * Grace period in hours.
     *
     * @return int
     */
    public function grace(): ?int;

    /**
     * Get an existing product instance capabilities.
     *
     * @return Collection
     */
    public function capabilities(): Collection;
}
