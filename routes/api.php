<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TenantsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\HousesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\Reports\ReportsController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ParkingRequestController;
use App\Http\Controllers\VehicleCategoryController;
use App\Http\Controllers\ParkingFeeController;


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

Route::post('/login', [UserController::class, 'login']);
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/reports/tenants/defaulters', [ReportsController::class, 'GetDefaulters'])->name('tenants.defaulters');

Route::get('logs/list', [LogsController::class, 'index'])->name('logs.index');
Route::get('logs/ajax', [LogsController::class, 'getActivityLogs'])->name('logs.list');

Route::put('/tenant/register', [TenantsController::class, 'register'])->name('tenant.register');
Route::get('payments/all', [PaymentsController::class, 'getAllPayments'])->name('payments.list');



//TENANT API METHODS
Route::get('tenant', [TenantsController::class, 'index']);
Route::post('tenant', [TenantsController::class, 'store']);
Route::put('tenant', [TenantsController::class, 'update']);
Route::delete('tenant', [TenantsController::class, 'destroy']);

//HOUSE API METHODS

Route::post('house/fetch', [HousesController::class, 'GetHouse']);
Route::post('house', [HousesController::class, 'store']);
Route::put('house', [HousesController::class, 'update']);
Route::delete('house', [HousesController::class, 'destroy']);


//TENANT API METHODS
Route::get('payment', [PaymentsController::class, 'index']);
Route::post('payment', [PaymentsController::class, 'store']);
Route::put('payment', [PaymentsController::class, 'update']);
Route::delete('payment', [PaymentsController::class, 'destroy']);  
Route::get('payment/recent', [PaymentsController::class, 'recentPayments']);



//INVOICES API METHODS
Route::get('invoice', [InvoicesController::class, 'index']);
Route::post('invoice', [InvoicesController::class, 'store']);
Route::put('invoice', [InvoicesController::class, 'update']);
Route::delete('invoice', [InvoicesController::class, 'destroy']);  
Route::get('invoice/recent', [InvoicesController::class, 'recentPayments']);


//REPORTS API METHODS
Route::get('reports/statistics', [ReportsController::class, 'GetStats']);


// USER API METHODS
Route::delete('users', [UserController::class, 'destroy']);  

Route::resources([
	'notifications' => NotificationController::class,
	'settings' => SettingsController::class,
    'users' => UserController::class,
	'map'=> MapController::class,
    'requests' => ParkingRequestController::class,
    'vehicle_category' => VehicleCategoryController::class,
    'parking_fees' => ParkingFeeController::class,
]);