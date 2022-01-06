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

        $data = $reportRepo->getIndexData();
        return formattedApiResponse::getJson($data);
    }


    public function requestMonthlyReview(ReportRepository $reportRepo){

        $data = $reportRepo->getRequestMonthlyReviewData();
        return formattedApiResponse::getJson($data);

    }

    public function incomeMonthlyReview(ReportRepository $reportRepo){

        $data = $reportRepo->getMonthlyIncomeReviewData();
        return formattedApiResponse::getJson($data);

    }

    public function GetMonthlyRequestsData(ReportRepository $reportRepo)
    {

        $data = $reportRepo->getRequestMonthlyData();
        return formattedApiResponse::getJson($data);
    }


    public function GetMonthlyIncomeData(ReportRepository $reportRepo)
    {

        $data = $reportRepo->getIncomeMonthlyData();
        return formattedApiResponse::getJson($data);

    }

    public function fetchLogs(SystemAuditRepository $auditRepo)
    {
        $logs = $auditRepo->getLogs();
        return formattedApiResponse::getJson($logs);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
