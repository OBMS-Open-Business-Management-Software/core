<?php

namespace App\Http\Controllers\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackerInstance extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'contract_position_id' => $this->contract_position_id,
            'tracker_id' => $this->tracker_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at->format('Y-m-d H:i:s'),
        ];
    }
}
