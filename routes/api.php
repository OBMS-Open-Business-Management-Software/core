<?php

declare(strict_types=1);

use App\Http\Controllers\API\APIAuthController;
use App\Http\Controllers\API\APIContractUsageTrackerController;
use App\Http\Controllers\API\APIContractUsageTrackerInstanceController;
use App\Http\Controllers\API\APIContractUsageTrackerItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [APIAuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::resource('contracts/usage-trackers/instances', APIContractUsageTrackerInstanceController::class);
    Route::resource('contracts/usage-trackers/items', APIContractUsageTrackerItemController::class);
    Route::resource('contracts/usage-trackers', APIContractUsageTrackerController::class);
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized.',
    ], 403);
});
