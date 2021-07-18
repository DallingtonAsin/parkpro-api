<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Models\ParkingArea;
use App\Models\ParkingFee;
use App\Models\ParkingRequest;
use App\Models\MonthlyRequest;
use App\Models\MonthlyIncome;
use App\Models\ActivityLog;
use App\Helpers\ApiResponse;
use Carbon\Carbon;
use TokenAuth;
use Globals;
use Helper;

class ReportsController extends Controller
{

    public $apiResponse;
    public function __construct(){
      $this->apiResponse = new ApiResponse();
    }
    
    public function index(){
        try{
         
            $totl_clients = Client::count();
            $totl_users = User::count();
            $totl_parking_areas = ParkingArea::count();
            $totl_parking_fees = ParkingFee::count();
            $totl_approved_requests = ParkingRequest::where('status', '=', 'APPROVED')->wherenotNull('approval_date')->count();
            $totl_pending_requests = ParkingRequest::where('status', '=', 'PENDING')->count();

            $current_month_income = ParkingRequest::where('status', '=', 'APPROVED')->wherenotNull('approval_date')
                                   ->whereMonth('approval_date', '=', Carbon::now()->subMonth()->month+1)
                                   ->sum('amount');

            $last_month_income = ParkingRequest::where('status', '=', 'APPROVED')->wherenotNull('approval_date')
                                   ->whereMonth('approval_date', '=', Carbon::now()->subMonth()->month)
                                   ->sum('amount');

            $resultSet = array(
                     "total_clients" => $totl_clients,
                     "total_users" => $totl_users,
                     "total_parking_areas" => $totl_parking_areas,
                     "total_parkig_fees" => $totl_parking_fees,
                     "total_pending_requests" => $totl_pending_requests,
                     "total_approved_requests" => $totl_approved_requests,
                     "current_month_income" => $current_month_income,
                     "last_month_income" => $last_month_income,
            );

            $this->apiResponse->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $this->apiResponse->message = "OK";
            $this->apiResponse->data = $resultSet;
             
        }catch(\Exception $ex){
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->apiResponse->message = $ex->getMessage();
        }
        return response()->json($this->apiResponse);
        
    }


    public function requestMonthlyReview(){
        try {
            $requests = MonthlyRequest::orderBy('month_int', 'asc')->get();
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $this->apiResponse->message  = Globals::$STATUS_DESC_SUCCESS;
            $this->apiResponse->data = $requests;
        } catch (\Exception $ex) {
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->apiResponse->message = Globals::$STATUS_DESC_ERROR;
            $this->apiResponse->data = $ex->getMessage();
        }
        
        return response()->json($this->apiResponse);

    }

    public function incomeMonthlyReview(){
        try {
            $incomes = MonthlyIncome::orderBy('month_int', 'asc')->get();
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $this->apiResponse->message  = Globals::$STATUS_DESC_SUCCESS;
            $this->apiResponse->data = $incomes;
        } catch (\Exception $ex) {
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->apiResponse->message = Globals::$STATUS_DESC_ERROR;
            $this->apiResponse->data = $ex->getMessage();
        }
        
        return response()->json($this->apiResponse);

    }

    public function GetMonthlyRequestsData()
    {

    try{

      $year = date('Y');
      $result = MonthlyRequest::where('year', $year)
                 ->orderBy('month_int','asc')
                ->get();
      $max_request_num = MonthlyRequest::max('total_requests');
      $data = $months = $years = $requests = array();
      foreach($result as $row){
        array_push($months, date("F", mktime(0, 0, 0, $row->month_int, 10)));
        array_push($years, $row->year);
        array_push($requests, $row->total_requests);
      }
      $data = array('months' => $months,'years' => $years,
                    'requests' => $requests,'max' => $max_request_num);
      $data = !empty($data) ? $data : [];
      $this->apiResponse->statusCode = Globals::$STATUS_CODE_SUCCESS;
      $this->apiResponse->message  = Globals::$STATUS_DESC_SUCCESS;
      $this->apiResponse->data = $data;

    }catch(\Exception $ex){
        $this->apiResponse->statusCode = Globals::$STATUS_CODE_ERROR;
        $this->apiResponse->message = $ex->getMessage();
        $this->apiResponse->data = null;
    }

    return response()->json($this->apiResponse);

    }


    public function GetMonthlyIncomeData()
    {

    try{

      $year = date('Y');
      $result = MonthlyIncome::where('year', $year)
                 ->orderBy('month_int','asc')
                ->get();
      $max_income_value = MonthlyIncome::max('total_income');
      $data = $months = $years = $incomes = array();
      foreach($result as $row){
        array_push($months, date("F", mktime(0, 0, 0, $row->month_int, 10)));
        array_push($years, $row->year);
        array_push($incomes, $row->total_income);
      }
      $data = array('months' => $months,'years' => $years,
                    'income' => $incomes,'max' => $max_income_value);
      $data = !empty($data) ? $data : [];
      $this->apiResponse->statusCode = Globals::$STATUS_CODE_SUCCESS;
      $this->apiResponse->message  = Globals::$STATUS_DESC_SUCCESS;
      $this->apiResponse->data = $data;

    }catch(\Exception $ex){
        $this->apiResponse->statusCode = Globals::$STATUS_CODE_ERROR;
        $this->apiResponse->message = $ex->getMessage();
        $this->apiResponse->data = null;
    }

    return response()->json($this->apiResponse);

    }

    public function fetchLogs(Request $request)
    {
        $resp = new ApiResponse();
        try {
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                $logs = ActivityLog::orderBy('id', 'desc')->get();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $logs;
                
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                $resp->data = "Unauthorized access";
                
            }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
