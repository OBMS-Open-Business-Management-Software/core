<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Resources\ContractUsageTrackerItem as Resource;
use App\Models\UsageTracker\TrackerItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class APIContractUsageTrackerItemController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $items = TrackerItem::all();

        return $this->sendResponse(Resource::collection($items), 'Usage tracker item retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'tracker_id' => 'integer|required',
            'type'       => 'string|required',
            'process'    => 'string|required',
            'round'      => 'string|required',
            'step'       => 'numeric|required',
            'amount'     => 'numeric|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $item = TrackerItem::create($input);

        return $this->sendResponse(new Resource($item), 'Usage tracker item created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $item = TrackerItem::find($id);

        if (is_null($item)) {
            return $this->sendError('Not found.', [], 404);
        }

        return $this->sendResponse(new Resource($item), 'Usage tracker item retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request     $request
     * @param TrackerItem $item
     * @param TrackerItem $item
     *
     * @return JsonResponse
     */
    public function update(Request $request, TrackerItem $item): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'tracker_id' => 'integer|nullable',
            'type'       => 'string|nullable',
            'process'    => 'string|nullable',
            'round'      => 'string|nullable',
            'step'       => 'numeric|nullable',
            'amount'     => 'numeric|nullable',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $item->update($input);

        return $this->sendResponse(new Resource($item), 'Usage tracker item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TrackerItem $item
     * @param TrackerItem $item
     *
     * @return JsonResponse
     */
    public function destroy(TrackerItem $item): JsonResponse
    {
        $item->delete();

        return $this->sendResponse([], 'Usage tracker item deleted successfully.');
    }
}
