<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Resources\ContractUsageTracker as Resource;
use App\Models\UsageTracker\Tracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class APIContractUsageTrackerController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $items = Tracker::all();

        return $this->sendResponse(Resource::collection($items), 'Contract usage tracker retrieved successfully.');
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
            'name'        => 'string|required',
            'description' => 'string|nullable',
            'vat_type'    => 'string|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $item = Tracker::create($input);

        return $this->sendResponse(new Resource($item), 'Contract usage tracker created successfully.');
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
        $item = Tracker::find($id);

        if (is_null($item)) {
            return $this->sendError('Not found.', [], 404);
        }

        return $this->sendResponse(new Resource($item), 'Contract usage tracker retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Tracker $item
     * @param Tracker $instance
     *
     * @return JsonResponse
     */
    public function update(Request $request, Tracker $item): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name'        => 'string|nullable',
            'description' => 'string|nullable',
            'vat_type'    => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray(), 422);
        }

        $item->update($input);

        return $this->sendResponse(new Resource($item), 'Contract usage tracker updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Tracker $item
     * @param Tracker $instance
     *
     * @return JsonResponse
     */
    public function destroy(Tracker $item): JsonResponse
    {
        $item->delete();

        return $this->sendResponse([], 'Contract usage tracker deleted successfully.');
    }
}
