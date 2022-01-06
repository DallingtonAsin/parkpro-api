<?php

namespace App\Repositories;

use App\Models\ParkingRequest;

class ParkingRequestRepository{
    
    // property
    
    public $parking_requests, $pending_requests,
     $approved_requests, $rejected_requests;
    
    // Method
    public function getAll(){
        
        $this->parking_requests = ParkingRequest::orderBy('id', 'desc')->get();
        return $this->parking_requests;
        
    }
    
    // pending requests
    public function getPendingRequests(){
        
        $this->pending_requests = ParkingRequest::where('status', '=', 'PENDING')
        ->orderBy('request_date', 'desc')
        ->get();
        return $this->pending_requests;
        
    }

      // approved requests
      public function getApprovedRequests(){
        
        $this->approved_requests = ParkingRequest::where('status', '=', 'APPROVED')
        ->orderBy('approval_date', 'desc')
        ->wherenotNull('approval_date')->get();
        return $this->approved_requests;
        
    }

    // rejected requests
    public function getRejectedRequests(){
        
        $this->rejected_requests = ParkingRequest::where('status', '=', 'REJECTED')
        ->orderBy('reject_date', 'desc')
        ->wherenotNull('reject_date')->get();
        return $this->rejected_requests;
        
    }

    
    
    
    
    
    
    
}