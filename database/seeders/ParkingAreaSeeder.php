<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingArea;


class ParkingAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

           ParkingArea::create(['client_id' => 1,'name' => 'MTN Wandegeya','address' => 'Wandegeya',
                                'description' => 'Near Wandegeya police station', 
                                'opens_at' => date('H:i:s', strtotime('08:00')),
                                'closes_at' => date('H:i:s', strtotime('17:00')),
                                'latitude' => 0.3527513, 'longitude' => 32.418591,
                                'total_space' => 120, 'current_free_space' => 45,
            ]);

          
            ParkingArea::create(['client_id' => 2,'name' => 'Ntinda Complex','address' => 'Ntinda',
                                'description' => 'At Ntinda complex building, near UBA bank', 
                                'opens_at' => date('H:i:s', strtotime('08:00')),
                                'closes_at' => date('H:i:s', strtotime('17:00')),
                                'latitude' => 0.3527513, 'longitude' => 32.61467, 
                                'total_space' => 180, 'current_free_space' => 105,
            ]);

            ParkingArea::create(['client_id' => 3,'name' => 'Mutesa III Station','address' => 'Kireka',
                                'description' => 'Opposite Kireka service station',
                                'opens_at' => date('H:i:s', strtotime('08:00')),
                                'closes_at' => date('H:i:s', strtotime('17:00')),
                                'latitude' => 0.358550, 'longitude' => 32.618591,
                                'total_space' => 155, 'current_free_space' => 35,
           ]);

            ParkingArea::create(['client_id' => 4,'name' => 'Ntinda Vocational Training Institute','address' => 'Ntinda',
                                'description' => 'Ntinda along Mukulu curve road', 
                                'opens_at' => date('H:i:s', strtotime('08:00')),
                                'closes_at' => date('H:i:s', strtotime('17:00')),
                                'latitude' => 0.354428, 'longitude' => 32.613638, 
                                'total_space' => 265, 'current_free_space' => 124,
           ]);


    }
}
