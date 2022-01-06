<?php

namespace App\Repositories;

use App\Models\ParkingRequest;

class ParkingRequestRepository{

   // property

   public $parking_requests;

   // Method
   public function getAll(){

    $this->parking_requests = ParkingRequest::orderBy('id', 'desc')->get();
    return $this->parking_requests;

   }






}