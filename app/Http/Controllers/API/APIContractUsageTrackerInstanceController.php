<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Resources\ContractUsageTrackerInstance as Resource;
use App\Models\UsageTracker\TrackerInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class APIContractUsageTrackerInstanceController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $items = TrackerInstance::all();

        return $this->sendResponse(Resource::collection($items), 'Contract usage tracker instance retrieved successfully.');
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
            'contract_id'          => 'integer|required',
            'tracker_id'           => 'integer|required',
            'contract_position_id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $item = TrackerInstance::create($input);

        return $this->sendResponse(new Resource($item), 'Contract usage tracker instance created successfully.');
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
        $item = TrackerInstance::find($id);

        if (is_null($item)) {
            return $this->sendError('Not found.', [], 404);
        }

        return $this->sendResponse(new Resource($item), 'Contract usage tracker instance retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request         $request
     * @param TrackerInstance $item
     * @param TrackerInstance $instance
     *
     * @return JsonResponse
     */
    public function update(Request $request, TrackerInstance $item): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'contract_id'          => 'integer|nullable',
            'tracker_id'           => 'integer|nullable',
            'contract_position_id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $item->update($input);

        return $this->sendResponse(new Resource($item), 'Contract usage tracker instance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TrackerInstance $item
     * @param TrackerInstance $instance
     *
     * @return JsonResponse
     */
    public function destroy(TrackerInstance $item): JsonResponse
    {
        $item->delete();

        return $this->sendResponse([], 'Contract usage tracker instance deleted successfully.');
    }
}
