<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
           \App\Models\Customer::factory()->count(5)->create();
    }
}
