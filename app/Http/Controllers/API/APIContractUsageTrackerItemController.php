<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Resources\ContractUsageTrackerItem as Resource;
use App\Models\UsageTracker\TrackerItem;
use App\Traits\API\SendsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class APIContractUsageTrackerItemController
{
    use SendsResponse;

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/contracts/usage-trackers/items",
     *     summary="Get all contract usage tracker items",
     *     description="Get all contract usage tracker items",
     *     tags={"Contract Usage Tracker Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Usage tracker item retrieved successfully."),
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse")
     * )
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
     * @OA\Post(
     *     path="/api/contracts/usage-trackers/items",
     *     summary="Create a new usage tracker item",
     *     description="Create a new usage tracker item",
     *     tags={"Contract Usage Tracker Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="tracker_id", type="integer", example=1),
     *             @OA\Property(property="type", type="string", example="type"),
     *             @OA\Property(property="process", type="string", example="equals"),
     *             @OA\Property(property="round", type="string", example="none"),
     *             @OA\Property(property="step", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", example=100)
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Usage tracker item created successfully."),
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationErrorResponse")
     * )
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
            return $this->sendError(422, 'Validation Error', $validator->errors()->toArray());
        }

        $item = TrackerItem::create($input);

        return $this->sendResponse('Usage tracker item created successfully.', new Resource($item));
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/contracts/usage-trackers/items/{id}",
     *     summary="Get a usage tracker item by ID",
     *     description="Get a usage tracker item by ID",
     *     tags={"Contract Usage Tracker Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the usage tracker item"),
     *
     *     @OA\Response(response=200, description="Usage tracker item retrieved successfully."),
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $item = TrackerItem::find($id);

        if (is_null($item)) {
            return $this->sendError(404, 'Not Found');
        }

        return $this->sendResponse('Usage tracker item retrieved successfully.', new Resource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/contracts/usage-trackers/items/{id}",
     *     summary="Update a usage tracker item by ID",
     *     description="Update a usage tracker item by ID",
     *     tags={"Contract Usage Tracker Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the usage tracker item"),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="tracker_id", type="integer", example=1),
     *             @OA\Property(property="type", type="string", example="type"),
     *             @OA\Property(property="process", type="string", example="equals"),
     *             @OA\Property(property="round", type="string", example="none"),
     *             @OA\Property(property="step", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", example=100)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Usage tracker item updated successfully."),
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationErrorResponse")
     * )
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
            return $this->sendError(422, 'Validation Error', $validator->errors()->toArray());
        }

        $item->update($input);

        return $this->sendResponse('Usage tracker item updated successfully.', new Resource($item));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/contracts/usage-trackers/items/{id}",
     *     summary="Delete a usage tracker item by ID",
     *     description="Delete a usage tracker item by ID",
     *     tags={"Contract Usage Tracker Items"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the usage tracker item"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Usage tracker item deleted successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usage tracker item deleted successfully."),
     *         )
     *     ),
     *
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse")
     * )
     *
     * @param TrackerItem $item
     * @param TrackerItem $item
     *
     * @return JsonResponse
     */
    public function destroy(TrackerItem $item): JsonResponse
    {
        $item->delete();

        return $this->sendResponse('Usage tracker item deleted successfully.');
    }
}
