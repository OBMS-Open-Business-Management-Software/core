<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
