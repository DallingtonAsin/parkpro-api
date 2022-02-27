<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
         return [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'phone_number' => $this->faker->e164phoneNumber,
                'email' => $this->faker->unique()->safeEmail,
                'otp' => $this->faker->numberBetween(1000, 9000),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
  ];
    }
}
