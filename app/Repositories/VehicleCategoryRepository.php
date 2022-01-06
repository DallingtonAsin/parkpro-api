<?php

namespace App\Repositories;

use App\Models\VehicleCategory;

class VehicleCategoryRepository{
    
    // property
    public $vehicle_categories;
    
    # Methods
    public function getVehicleCategories(){
        
        $this->vehicle_categories = VehicleCategory::all();
        foreach($this->vehicle_categories as $cat){
            $cat->name = strtolower($cat->name);
        }
    
        return $this->vehicle_categories;
        
    }
    
    
    
    
    
    
}