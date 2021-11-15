<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ParkingRequestController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\ParkingFeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ParkingAreaController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;




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

// USER ACOUNT
Route::post('/login', [UserController::class, 'authenticate']);
Route::post('/customer/login', [CustomerController::class, 'authenticate']);


Route::post('/password/edit', [UserController::class, 'changePassword']);

// ROLES
Route::post('/roles/destroy', [RolesController::class, 'destroy']);


// REQUESTS
Route::get('/requests/pending', [ParkingRequestController::class, 'pendingRequests']);
Route::get('/requests/approved', [ParkingRequestController::class, 'approvedRequests']);
Route::get('/requests/rejected', [ParkingRequestController::class, 'rejectedRequests']);

Route::post('/request/post', [ParkingRequestController::class, 'store']);
Route::post('/request/approve', [ParkingRequestController::class, 'approveRequest']);
Route::post('/request/reject', [ParkingRequestController::class, 'rejectRequest']);

Route::get('/parking/fee/{client_id}/{parking_area_id}/{vehicle_type_id}', [ParkingFeeController::class, 'getParkingFee']);
// Route::get('/parking/fee', [ParkingRequestController::class, 'getParkingFee']);

// REPORTS
Route::get('/reports', [ReportsController::class, 'index']);
Route::get('/reports/requests/review', [ReportsController::class, 'requestMonthlyReview']);   
Route::get('/reports/incomes/review', [ReportsController::class, 'incomeMonthlyReview']);
Route::get('/reports/requests/data', [ReportsController::class, 'GetMonthlyRequestsData']);
Route::get('/reports/incomes/data', [ReportsController::class, 'GetMonthlyIncomeData']);



Route::get('/image/path', [UserController::class, 'getImageStoragePath']);
Route::get('/parking/fees', [ParkingFeeController::class, 'getParkingFees']);
Route::get('/parking/fees', [ParkingFeeController::class, 'getParkingFees']);



// LOGS
Route::get('/transaction/history', [ParkingRequestController::class, 'getTransactionHistory']);

Route::post('/payment/create', [PaymentController::class, 'create']);

Route::get('parking_spots', [ParkingAreaController::class, 'getParkingSpots']);
Route::get('parking_areas/filter', [ParkingAreaController::class, 'filterParkingAreas']);
Route::post('user/account/topup', [PaymentController::class, 'topupUserAccount']);


// Customers 
Route::get('/customer/details', [CustomerController::class, 'findCustomer']);
Route::post('/customer/password/change', [CustomerController::class, 'changePassword']);



// Transaction History
Route::get('/transactions', [PaymentController::class, 'getTransactionHistory']);
Route::get('/notifications', [PaymentController::class, 'getNotifications']);






// RESOURCE ENDPOINTS
Route::resources([
    'users' => UserController::class,
    'requests' => ParkingRequestController::class,
    'vehicle_category' => VehicleCategoryController::class,
    'parking_fees' => ParkingFeeController::class,
    'clients' => ClientController::class,
    'roles' => RolesController::class,
    'parking_areas' => ParkingAreaController::class,
    'company' => CompanySettingsController::class,
    'customer' => CustomerController::class,
    'payment' => PaymentController::class,

]);