<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ParkingRequestController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\ParkingFeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ParkingAreaController;


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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [UserController::class, 'authenticate']);

Route::get('/reports', [ReportsController::class, 'index']);
Route::get('/requests/pending', [ParkingRequestController::class, 'pendingRequests']);
Route::get('/requests/approved', [ParkingRequestController::class, 'approvedRequests']);
Route::get('/requests/rejected', [ParkingRequestController::class, 'rejectedRequests']);

Route::resources([
    'users' => UserController::class,
    'requests' => ParkingRequestController::class,
    'vehicle_category' => VehicleCategoryController::class,
    'parking_fees' => ParkingFeeController::class,
    'clients' => ClientController::class,
    'parking_areas' => ParkingAreaController::class,
]);