<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Models\ParkingArea;
use App\Models\ParkingFee;
use App\Models\ParkingRequest;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;


class ReportsController extends Controller
{
    
    
    public function index(){
        try{
            $resp = new ApiResponse();
            $totl_clients = Client::count();
            $totl_users = User::count();
            $totl_parking_areas = ParkingArea::count();
            $totl_parking_fees = ParkingFee::count();
            $totl_approved_requests = ParkingRequest::where('status', '=', 'APPROVED')->wherenotNull('approval_date')->count();
            $totl_pending_requests = ParkingRequest::where('status', '=', 'PENDING')->count();


            $resultSet = array(
                     "total_clients" => $totl_clients,
                     "total_users" => $totl_users,
                     "total_parking_areas" => $totl_parking_areas,
                     "total_parkig_fees" => $totl_parking_fees,
                     "total_pending_requests" => $totl_pending_requests,
                     "total_approved_requests" => $totl_approved_requests,
            );

            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message = "OK";
            $resp->data = $resultSet;
             
        }catch(\Exception $ex){
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
