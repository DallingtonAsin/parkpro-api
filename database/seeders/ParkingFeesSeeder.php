<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingFee;

class ParkingFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ParkingFee::create(['parking_area_id' => 1,'vehicle_cat_id' => 1, 'fee_per_hour' => 3500]);
        ParkingFee::create(['parking_area_id' => 1,'vehicle_cat_id' => 2, 'fee_per_hour' => 4500]);
        ParkingFee::create(['parking_area_id' => 1,'vehicle_cat_id' => 3, 'fee_per_hour' => 5500]);

        ParkingFee::create(['parking_area_id' => 2,'vehicle_cat_id' => 1, 'fee_per_hour' => 6500]);
        ParkingFee::create(['parking_area_id' => 2,'vehicle_cat_id' => 2, 'fee_per_hour' => 6500]);
        ParkingFee::create(['parking_area_id' => 2,'vehicle_cat_id' => 3, 'fee_per_hour' => 7500]);

        ParkingFee::create(['parking_area_id' => 3,'vehicle_cat_id' => 1, 'fee_per_hour' => 7500]);
        ParkingFee::create(['parking_area_id' => 3,'vehicle_cat_id' => 2, 'fee_per_hour' => 8500]);
        ParkingFee::create(['parking_area_id' => 3,'vehicle_cat_id' => 3, 'fee_per_hour' => 9500]);

        ParkingFee::create(['parking_area_id' => 4,'vehicle_cat_id' => 1, 'fee_per_hour' => 9500]);
        ParkingFee::create(['parking_area_id' => 4,'vehicle_cat_id' => 2, 'fee_per_hour' => 10500]);
        ParkingFee::create(['parking_area_id' => 4,'vehicle_cat_id' => 3, 'fee_per_hour' => 11500]);



        
    }
}
