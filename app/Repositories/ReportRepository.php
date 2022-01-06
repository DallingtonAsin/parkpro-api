<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\User;
use App\Models\ParkingArea;
use App\Models\ParkingFee;
use App\Models\ParkingRequest;
use App\Models\MonthlyReview;
use App\Models\ActivityLog;
use Carbon\Carbon;


class ReportRepository{
    
    // property
    
    public $data;
    public $monthlyRequestReviewData, $monthlyIncomeReviewData;
    public $monthlyRequestData, $monthlyIncomeData;
    
    
    // Method
    public function getIndexData(){
        
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
            
            $this->data = array(
                "total_clients" => $totl_clients,
                "total_users" => $totl_users,
                "total_parking_areas" => $totl_parking_areas,
                "total_parkig_fees" => $totl_parking_fees,
                "total_pending_requests" => $totl_pending_requests,
                "total_approved_requests" => $totl_approved_requests,
                "current_month_income" => $current_month_income,
                "last_month_income" => $last_month_income,
            );
            
            return $this->data;
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    public function getRequestMonthlyReviewData(){
        try{
            $this->monthlyRequestReviewData = MonthlyReview::orderBy('month_int', 'asc')->get();
            return $this->monthlyRequestReviewData;
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    public function getMonthlyIncomeReviewData(){
        try{
            $this->monthlyIncomeReviewData = MonthlyReview::orderBy('month_int', 'asc')->get();
            return $this->monthlyIncomeReviewData;
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    public function getRequestMonthlyData(){
        try{
            
            $year = date('Y');
            $result = MonthlyReview::where('year', $year)
            ->orderBy('month_int','asc')
            ->get();
            
            $max_request_num = MonthlyReview::max('total_requests');
            $data = $months = $years = $requests = array();
            
            foreach($result as $row){
                array_push($months, date("F", mktime(0, 0, 0, $row->month_int, 10)));
                array_push($years, $row->year);
                array_push($requests, $row->total_requests);
            }
            $data = array('months' => $months,'years' => $years,
            'requests' => $requests,'max' => $max_request_num);
            $this->monthlyRequestData  = !empty($data) ? $data : [];
            
            return $this->monthlyRequestData;
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    public function getIncomeMonthlyData(){
        try{
            $year = date('Y');
            $result = MonthlyReview::where('year', $year)
            ->orderBy('month_int','asc')
            ->get();
            $max_income_value = MonthlyReview::max('total_income');
            $data = $months = $years = $incomes = array();
            foreach($result as $row){
                array_push($months, date("F", mktime(0, 0, 0, $row->month_int, 10)));
                array_push($years, $row->year);
                array_push($incomes, $row->total_income);
            }
            $data = array('months' => $months,'years' => $years,
            'income' => $incomes,'max' => $max_income_value);
            $this->monthlyIncomeData = !empty($data) ? $data : [];
            
            return $this->monthlyIncomeData;
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    
    
    
    
    
}