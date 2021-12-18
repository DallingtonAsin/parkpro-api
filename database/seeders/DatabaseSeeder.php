<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
        RoleTableSeeder::class,
        UserTableSeeder::class,
        ClientTableSeeder::class,
        VehicleCategorySeeder::class,
        ParkingAreaSeeder::class,
        ParkingFeesSeeder::class,
        // ParkingRequestTableSeeder::class,

        ]);
    }
}
