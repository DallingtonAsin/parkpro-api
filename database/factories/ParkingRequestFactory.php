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
                'username' => $this->faker->unique()->username, // lastName
                'telephone' => $this->faker->e164phoneNumber,
                'car_number' => Str::random(6),
                'request_date' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];
    }
}
