<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Models\ParkingArea;
use App\Models\ParkingFee;
use App\Models\ParkingRequest;
use App\Models\MonthlyReview;
use App\Models\ActivityLog;
use App\Helpers\ApiResponse;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Globals;
use Helper;

class ReportsController extends Controller
{

    public $apiResponse;
    public function __construct(){
      $this->apiResponse = new ApiResponse();
    }
    
    public function index(ReportRepository $reportRepo){
        try{
         
            $resultSet = $reportRepo->getIndexData();
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $this->apiResponse->message = "OK";
            $this->apiResponse->data = $resultSet;
             
        }catch(\Exception $ex){
            $this->apiResponse->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->apiResponse->message = $ex->getMessage();
        }
        return response()->json($this->apiResponse);
        
    }


    public function requestMonthlyReview(ReportRepository $reportRepo){
        try {
            $requests = $reportRepo->getRequestMonthlyReviewData();
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

    public function incomeMonthlyReview(ReportRepository $reportRepo){
        try {
            $incomes = $reportRepo->getMonthlyIncomeReviewData();
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

    public function GetMonthlyRequestsData(ReportRepository $reportRepo)
    {

    try{
      $data = $reportRepo->getRequestMonthlyData();
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


    public function GetMonthlyIncomeData(ReportRepository $reportRepo)
    {
    try{
      $data = $reportRepo->getIncomeMonthlyData();
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

    public function fetchLogs(ReportRepository $reportRepo)
    {
        $resp = new ApiResponse();
        try {
                $logs = ActivityLog::orderBy('id', 'desc')->get();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $logs; 
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
