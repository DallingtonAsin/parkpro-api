<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;

class VehicleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VehicleCategory::create(['name' => 'Car']);
        VehicleCategory::create(['name' => 'Truck']);
        VehicleCategory::create(['name' => 'Bus']);

    }
}
