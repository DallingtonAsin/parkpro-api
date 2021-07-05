<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ParkingRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\ParkingRequest::factory()->count(80)->create();
    }
}
