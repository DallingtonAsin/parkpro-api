<?php

namespace Database\Factories;

use App\Models\ParkingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ParkingRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
                'ticket_no' => str::random(10),
                'telephone_no' => $this->faker->e164phoneNumber,
                'vehicle_number' => Str::random(6),
                'vehicle_type_id' => $this->faker->randomElement([1,2,3]),
                'client_id' => $this->faker->randomElement([1,2,3]),
                'parking_area_id' => $this->faker->randomElement([1,2,3,4,5,6]),
                'start_time' => '03:00',
                'end_time' => '06:00',
                'parking_hours' => $this->faker->numberBetween($min=1, $max=10),
                'amount' => $this->faker->numberBetween($min=10000, $max=100000),
                'request_date' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];
    }
}
