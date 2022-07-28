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
use App\Helpers\formattedApiResponse;
use App\Repositories\ReportRepository;
use App\Repositories\SystemAuditRepository;
use Carbon\Carbon;
use Globals;
use Helper;

class ReportsController extends Controller
{
    
    
    public function index(ReportRepository $reportRepo){
        try{
            $data = $reportRepo->getIndexData();
            return formattedApiResponse::getJson($data);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    
    public function requestMonthlyReview(ReportRepository $reportRepo){
        try{
            $data = $reportRepo->getRequestMonthlyReviewData();
            return formattedApiResponse::getJson($data);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
        
    }
    
    public function incomeMonthlyReview(ReportRepository $reportRepo){
        try{
            $data = $reportRepo->getMonthlyIncomeReviewData();
            return formattedApiResponse::getJson($data);
            
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    public function GetMonthlyRequestsData(ReportRepository $reportRepo)
    {
        try{
            $data = $reportRepo->getRequestMonthlyData();
            return formattedApiResponse::getJson($data);
            
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    
    public function GetMonthlyIncomeData(ReportRepository $reportRepo)
    {
        try{
            $data = $reportRepo->getIncomeMonthlyData();
            return formattedApiResponse::getJson($data);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
        
    }
    
    public function fetchLogs(SystemAuditRepository $auditRepo)
    {
        try{
            $logs = $auditRepo->getLogs();
            return formattedApiResponse::getJson($logs);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
