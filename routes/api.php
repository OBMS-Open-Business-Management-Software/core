<?php

use App\Http\Controllers\API\APIAuthController;
use App\Http\Controllers\API\APITrackerInstanceController;
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
    Route::resource('contract/usage-tracker/instance', APITrackerInstanceController::class);
});
