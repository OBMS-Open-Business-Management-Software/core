<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ContractUsageTrackerItem",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="tracker_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="process", type="string", example="equals"),
 *     @OA\Property(property="round", type="string", example="none"),
 *     @OA\Property(property="step", type="number", example=1),
 *     @OA\Property(property="amount", type="number", example=100),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
class ContractUsageTrackerItem extends JsonResource
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
            'id'         => $this->id,
            'tracker_id' => $this->tracker_id,
            'type'       => $this->type,
            'process'    => $this->process,
            'round'      => $this->round,
            'step'       => $this->step,
            'amount'     => $this->amount,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
