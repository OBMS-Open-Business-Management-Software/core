<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Resources\ContractUsageTracker as Resource;
use App\Models\UsageTracker\Tracker;
use App\Traits\API\SendsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class APIContractUsageTrackerController
{
    use SendsResponse;

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/contracts/usage-trackers",
     *     summary="Get all contract usage trackers",
     *     description="Get all contract usage trackers",
     *     tags={"Contract Usage Trackers"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contract usage tracker retrieved successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract usage tracker retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ContractUsageTracker"))
     *         )
     *     ),
     *
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse")
     * )
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
     * @OA\Post(
     *     path="/api/contracts/usage-trackers",
     *     summary="Create a new contract usage tracker",
     *     description="Create a new contract usage tracker",
     *     tags={"Contract Usage Trackers"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string", example="Contract Usage Tracker"),
     *             @OA\Property(property="description", type="string", example="Contract usage tracker description"),
     *             @OA\Property(property="vat_type", type="string", example="VAT type")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Contract usage tracker created  successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract usage tracker created successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ContractUsageTracker"))
     *     ),
     *
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
            'name'        => 'string|required',
            'description' => 'string|nullable',
            'vat_type'    => 'string|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError(422, 'Validation Error', $validator->errors()->toArray());
        }

        $item = Tracker::create($input);

        return $this->sendResponse('Contract usage tracker created successfully.', new Resource($item));
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/contracts/usage-trackers/{id}",
     *     summary="Get a contract usage tracker by ID",
     *     description="Get a contract usage tracker by ID",
     *     tags={"Contract Usage Trackers"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the contract usage tracker"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contract usage tracker retrieved successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract usage tracker retrieved successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ContractUsageTracker"))
     *     ),
     *
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
        $item = Tracker::find($id);

        if (is_null($item)) {
            return $this->sendError(404, 'Not Found');
        }

        return $this->sendResponse('Contract usage tracker retrieved successfully.', new Resource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/contracts/usage-trackers/{id}",
     *     summary="Update a contract usage tracker by ID",
     *     description="Update a contract usage tracker by ID",
     *     tags={"Contract Usage Trackers"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the contract usage tracker"),
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="name", type="string", example="Contract Usage Tracker"),
     *             @OA\Property(property="description", type="string", example="Contract usage tracker description"),
     *             @OA\Property(property="vat_type", type="string", example="VAT type")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contract usage tracker updated successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract usage tracker updated successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/ContractUsageTracker"))
     *     ),
     *
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationErrorResponse")
     * )
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
            return $this->sendError(422, 'Validation Error', $validator->errors()->toArray());
        }

        $item->update($input);

        return $this->sendResponse('Contract usage tracker updated successfully.', new Resource($item));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/contracts/usage-trackers/{id}",
     *     summary="Delete a contract usage tracker by ID",
     *     description="Delete a contract usage tracker by ID",
     *     tags={"Contract Usage Trackers"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the contract usage tracker"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contract usage tracker deleted successfully.",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract usage tracker deleted successfully."),
     *         )
     *     ),
     *
     *     @OA\Response(response=403, ref="#/components/responses/ForbiddenResponse"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFoundResponse")
     * )
     *
     * @param Tracker $item
     * @param Tracker $instance
     *
     * @return JsonResponse
     */
    public function destroy(Tracker $item): JsonResponse
    {
        $item->delete();

        return $this->sendResponse('Contract usage tracker deleted successfully.');
    }
}
