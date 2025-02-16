<?php

namespace App\Http\Controllers\API;

use App\Models\UsageTracker\TrackerInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\API\Resources\TrackerInstance as TrackerInstanceResource;
use Illuminate\Support\Facades\Validator;

class APITrackerInstanceController extends APIBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $products = TrackerInstance::all();

        return $this->sendResponse(TrackerInstanceResource::collection($products), 'Usage tracker instance retrieved successfully.');
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
            'name' => 'required',
            'detail' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }

        $product = TrackerInstance::create($input);

        return $this->sendResponse(new TrackerInstanceResource($product), 'Usage tracker instance created successfully.');
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
        $instance = TrackerInstance::find($id);

        if (is_null($instance)) {
            return $this->sendError('Product not found.');
        }

        return $this->sendResponse(new TrackerInstanceResource($instance), 'Usage tracker instance retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param TrackerInstance $instance
     *
     * @return JsonResponse
     */
    public function update(Request $request, TrackerInstance $instance): JsonResponse
    {
        return $this->sendResponse(new TrackerInstanceResource($instance), 'Usage tracker instance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TrackerInstance $instance
     *
     * @return JsonResponse
     */
    public function destroy(TrackerInstance $instance): JsonResponse
    {
        $instance->delete();

        return $this->sendResponse([], 'Usage tracker instance deleted successfully.');
    }
}
