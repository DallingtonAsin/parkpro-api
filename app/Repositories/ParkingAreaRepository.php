<?php

namespace App\Repositories;

use App\Models\ParkingArea;
use App\Models\VehicleCategory;
use App\Models\ParkingFee;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class ParkingAreaRepository{
    
    // property
    public $parking_areas, $parking_area;
    
    # Methods
    public function getParkingAreas(){
        
        $this->parking_areas = ParkingArea::where("is_deleted", "=", 0)->orderBy('id', 'desc')->get();
        $num = 1000;
        foreach($this->parking_areas as $parking){
            $parking->spots = $parking->total_space;
            $parking->free = $parking->current_free_space;
            $parking->client = Client::where('id', $parking->client_id)->value('name');
            if(!empty($parking->photo)){
                $parking->photo =  Storage::disk('public')->url($parking->photo);
            }else{
                $parking->photo = "https://picsum.photos/".$num++."";
            }
            $parking->coordinate = array("latitude" => $parking->latitude, "longitude" => $parking->longitude);
            $parking->distance = 35;
            $parking->fees = $this->getParkingFees($parking->id);
            $parking->is_open = date('H') < date('H', strtotime($parking->closes_at)) ? true : false;
            $parking->opens_at = date('H:i', strtotime($parking->opens_at));
            $parking->closes_at = date('H:i', strtotime($parking->closes_at));
        }

        return $this->parking_areas;
        
    }

    public function searchParking($id){
        try{
                $num = 1000;
                $this->parking_area = ParkingArea::where("id", "=", $id)->get();
                foreach($this->parking_area as $parking){
                    $parking->spots = $parking->total_space;
                    $parking->free = $parking->current_free_space;
                    $parking->client = Client::where('id', $parking->client_id)->value('name');
                    if(!empty($parking->photo)){
                        $parking->photo =  Storage::disk('public')->url($parking->photo);
                    }else{
                        $parking->photo = "https://picsum.photos/".$num++."";
                    }
                    $parking->coordinate = array("latitude" => $parking->latitude, "longitude" => $parking->longitude);
                    $parking->distance = 35;
                    $parking->fees = $this->getParkingFees($parking->id);
                    $parking->is_open = date('H') < date('H', strtotime($parking->closes_at)) ? true : false;
                    $parking->opens_at = date('H:i', strtotime($parking->opens_at));
                    $parking->closes_at = date('H:i', strtotime($parking->closes_at));
                }
                return $this->parking_area;

        }catch(Exception $e){
            throw $e;
        }
    }

    private function getParkingFees($parking_area_id){
        try{
         $vehicleCats = VehicleCategory::all();
         $fees = [];
         foreach($vehicleCats as $cat){
          $fee_per_hour = ParkingFee::where('parking_area_id', $parking_area_id)
                 ->where('vehicle_cat_id', $cat->id)->value('fee_per_hour');
         $fees[strtolower($cat->name)] = $fee_per_hour; 
         }
        return $fees;
        }catch(Exception $e){
            throw $e;
        }
     }
    
    
    
    
    
    
}