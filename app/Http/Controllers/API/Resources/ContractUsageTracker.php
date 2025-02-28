<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ContractUsageTracker",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Usage Tracker"),
 *     @OA\Property(property="description", type="string", example="Usage Tracker Description"),
 *     @OA\Property(property="vat_type", type="string", example="VAT Type"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
class ContractUsageTracker extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'vat_type'    => $this->vat_type,
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
            'deleted_at'  => $this->deleted_at?->toISOString(),
        ];
    }
}
