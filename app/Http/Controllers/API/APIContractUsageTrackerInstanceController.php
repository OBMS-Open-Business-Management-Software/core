<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Resources\ContractUsageTrackerInstance as Resource;
use App\Models\UsageTracker\TrackerInstance;
use App\Traits\API\SendsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class APIContractUsageTrackerInstanceController
{
    use SendsResponse;

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/contracts/usage-trackers/instances",
     *     summary="Get all contract usage tracker instances",
     *     description="Get all contract usage tracker instances",
     *     tags={"Contract Usage Tracker Instances"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(response=200, description="Contract usage tracker instance retrieved successfully."),
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse")
     * )
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
     * @OA\Post(
     *     path="/api/contracts/usage-trackers/instances",
     *     summary="Create a new contract usage tracker instance",
     *     description="Create a new contract usage tracker instance",
     *     tags={"Contract Usage Tracker Instances"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="contract_id", type="integer", example=1),
     *             @OA\Property(property="tracker_id", type="integer", example=1),
     *             @OA\Property(property="contract_position_id", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Contract usage tracker instance created successfully."),
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
            'contract_id'          => 'integer|required',
            'tracker_id'           => 'integer|required',
            'contract_position_id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError(422, 'Validation Error', $validator->errors()->toArray());
        }

        $item = TrackerInstance::create($input);

        return $this->sendResponse('Contract usage tracker instance created successfully.', new Resource($item));
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/contracts/usage-trackers/instances/{id}",
     *     summary="Get a contract usage tracker instance by ID",
     *     description="Get a contract usage tracker instance by ID",
     *     tags={"Contract Usage Tracker Instances"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the contract usage tracker instance"),
     *
     *     @OA\Response(response=200, description="Contract usage tracker instance retrieved successfully."),
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
        $item = TrackerInstance::find($id);

        if (is_null($item)) {
            return $this->sendError(404, 'Not Found');
        }

        return $this->sendResponse('Contract usage tracker instance retrieved successfully.', new Resource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/contracts/usage-trackers/instances/{id}",
     *     summary="Update a contract usage tracker instance by ID",
     *     description="Update a contract usage tracker instance by ID",
     *     tags={"Contract Usage Tracker Instances"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the contract usage tracker instance"),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="contract_id", type="integer", example=1),
     *             @OA\Property(property="tracker_id", type="integer", example=1),
     *             @OA\Property(property="contract_position_id", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Contract usage tracker instance updated successfully."),
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationErrorResponse")
     * )
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
            return $this->sendError(422, 'Validation Error', $validator->errors()->toArray());
        }

        $item->update($input);

        return $this->sendResponse('Contract usage tracker instance updated successfully.', new Resource($item));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/contracts/usage-trackers/instances/{id}",
     *     summary="Delete a contract usage tracker instance by ID",
     *     description="Delete a contract usage tracker instance by ID",
     *     tags={"Contract Usage Tracker Instances"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the contract usage tracker instance"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contract usage tracker instance deleted successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract usage tracker instance deleted successfully."),
     *         )
     *     ),
     *
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse")
     * )
     *
     * @param TrackerInstance $item
     * @param TrackerInstance $instance
     *
     * @return JsonResponse
     */
    public function destroy(TrackerInstance $item): JsonResponse
    {
        $item->delete();

        return $this->sendResponse('Contract usage tracker instance deleted successfully.');
    }
}
