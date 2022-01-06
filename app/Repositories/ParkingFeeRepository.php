<?php

namespace App\Repositories;

use App\Models\ParkingFee;
use App\Models\ParkingArea;
use App\Models\Client;


class ParkingFeeRepository{

   // property

   public $parking_fees;

   // Method
   public function getParkingFees(){

    $this->parking_fees = ParkingFee::orderBy('id', 'desc')->get();
    if(count((array)$this->parking_fees) > 0){
        foreach($this->parking_fees as $fee){
            $parking = ParkingArea::find($fee->parking_area_id);
            $client = Client::find($parking->client_id);
            $fee->client_name = $client->name;
        }
    }
    return $this->parking_fees;

   }






}