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
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Payments\FlutterwaveController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;



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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
    //     return $request->user();
    // });
    
    
    // USER DASHBOARD ROUTING
    Route::post('/user/login', [UserController::class, 'authenticate'])->name('login'); // done
    
    // USER ACCOUNT 
    Route::group(['prefix' => 'user', 'middleware' => ['auth:api-users']], function(){
        
        Route::post('/password/edit', [UserController::class, 'changePassword']);
        Route::get('/image/path', [UserController::class, 'getImageStoragePath']);
        
    });
    
    
    // PARKING REQUESTS 
    Route::group(['prefix' => 'requests', 'middleware' => ['auth:api-users']], function(){
        
        Route::get('/pending', [ParkingRequestController::class, 'pendingRequests']);
        Route::get('/approved', [ParkingRequestController::class, 'approvedRequests']);
        Route::get('/rejected', [ParkingRequestController::class, 'rejectedRequests']);
        Route::post('/approve', [ParkingRequestController::class, 'approveRequest']);
        Route::post('/reject', [ParkingRequestController::class, 'rejectRequest']);
        
    });
    
    
    Route::group(['middleware' => 'auth:api-users'], function(){
        
        Route::post('/user/change-account/{id}', [UserController::class, 'changeAccountStatus']);
        
        Route::resources([
            'roles' => RolesController::class,
            'clients' => ClientController::class,
            'users' => UserController::class,
            'company' => CompanySettingsController::class,
            'requests' => ParkingRequestController::class,
            'vehicle-category' => VehicleCategoryController::class,
            'parking-fees' => ParkingFeeController::class,
            'parking-areas' => ParkingAreaController::class,
            'payment' => PaymentController::class,
            'customers' => CustomerController::class,
            
        ]);
    });
    
    
    
    
    // REPORTS
    Route::group(['prefix' => 'reports', 'middleware' => ['auth:api-users']], function(){
        Route::get('/', [ReportsController::class, 'index']);
        Route::get('/requests/review', [ReportsController::class, 'requestMonthlyReview']);   
        Route::get('/incomes/review', [ReportsController::class, 'incomeMonthlyReview']);
        Route::get('/requests/data', [ReportsController::class, 'GetMonthlyRequestsData']);
        Route::get('/incomes/data', [ReportsController::class, 'GetMonthlyIncomeData']);
        Route::get('/system-audit', [ReportsController::class, 'fetchLogs']);
        
    });
    
    
    Route::get('/parking/fee/{client_id}/{parking_area_id}/{vehicle_type_id}', [ParkingFeeController::class, 'getParkingFee']);
    Route::get('/rave/momo/collection/callback', [FlutterwaveController::class, 'mobileMoneyCollectionCallback'])->name('momo-collection-callback');
    
    
    
    // Customer Login and Registration (done)
    Route::post('/customer/send-otp', [CustomerController::class, 'InsertOrUpdateCustomerOTP']); // done
    Route::post('/customer/verify-otp', [CustomerController::class, 'verifyOTP']); // done
    Route::post('/customer/resend-otp', [CustomerController::class, 'resendOTP']); //done

    Route::post('/customer/profile/create', [CustomerController::class, 'createProfile']); // done
    
    Route::get('africastkng/account/details', [PaymentController::class, 'africasTkngAccountDetails']);
    
    // Customer Routes
    Route::group(['prefix' => 'customer', 'middleware' => ['auth:api-customers']], function(){
        
        Route::get('/details', [CustomerController::class, 'findCustomer']); //done
        Route::post('/app/details', [CustomerController::class, 'updateAppDetails']); //done
        // Route::post('/customer/resend-otp', [CustomerController::class, 'resendOTP']); //done

        Route::get('/transactions', [PaymentController::class, 'getTransactionHistory']); // done
        Route::get('/transaction/history', [ParkingRequestController::class, 'getTransactionHistory']); //done
        Route::get('/notifications', [PaymentController::class, 'getNotifications']); // done
        Route::post('/account/topup', [PaymentController::class, 'topupUserAccount']); //done
        
        Route::post('/password/change', [CustomerController::class, 'changePassword']); // done
        Route::put('/profile/update', [CustomerController::class, 'updateProfile']); // done
        Route::post('/profile/picture/remove', [CustomerController::class, 'removeProfilePicture']); // done
        Route::post('/change/profile-picture', [CustomerController::class, 'uploadProfilePicture']); // done
        Route::post('/feedback', [MailController::class, 'postFeedback']); // done
        Route::post('/payment/create', [PaymentController::class, 'create']); // done
        Route::post('/send/airtime', [PaymentController::class, 'dispatchAirtime']); // done
        Route::post('/account/topup/money', [PaymentController::class, 'creditCustomerAccount']); // done
        
        // The route that the button calls to initialize payment
        Route::post('/pay', [FlutterwaveController::class, 'initialize'])->name('pay');
        // The callback url after a payment
       
    });
    
    
    Route::group(['prefix' => 'device', 'middleware' => ['auth:api-customers']], function(){
        
        Route::get('parking-area/fees', [ParkingFeeController::class, 'getParkingFees']); // done
        Route::get('parking-spots', [ParkingAreaController::class, 'getParkingSpots']); // done
        Route::get('parking-areas/search', [ParkingAreaController::class, 'searchParkingArea']); // done
        Route::get('parking-request/myrequests', [ParkingRequestController::class, 'getMyParkingRequests']);
        Route::get('parking-request/details', [ParkingRequestController::class, 'getRequestOrderInfo']);
        Route::get('parking-area/find-by-id', [ParkingAreaController::class, 'findParking']);
        Route::get('parking-area/near-by', [ParkingAreaController::class, 'getNearByParkings']);
        Route::get('parking-area/top-rated', [ParkingAreaController::class, 'getTopRatedParkingAreas']);
        Route::post('push-notification/send', [NotificationController::class, 'sendPushNotificationToUser']);
        

        Route::resources([
            'vehicle-category' => VehicleCategoryController::class, // done
            'parking-fees' => ParkingFeeController::class, // done
            'parking-areas' => ParkingAreaController::class, // done
            'parking-request' => ParkingRequestController::class, // done
            
        ]);
    });
    
    
    
    
    
