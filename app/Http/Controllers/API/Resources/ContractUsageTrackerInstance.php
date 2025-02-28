<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ContractUsageTrackerInstance",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="contract_id", type="integer", example=1),
 *     @OA\Property(property="contract_position_id", type="integer", example=1),
 *     @OA\Property(property="tracker_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
class ContractUsageTrackerInstance extends JsonResource
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
            'id'                   => $this->id,
            'contract_id'          => $this->contract_id,
            'contract_position_id' => $this->contract_position_id,
            'tracker_id'           => $this->tracker_id,
            'created_at'           => $this->created_at?->toISOString(),
            'updated_at'           => $this->updated_at?->toISOString(),
            'deleted_at'           => $this->deleted_at?->toISOString(),
        ];
    }
}
