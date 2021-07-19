<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

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
                'name' => $this->faker->name,
                'username' => $this->faker->unique()->lastName,
                'gender' => $this->faker->randomElement(['Female', 'Male']),
                'email' => $this->faker->unique()->safeEmail,
                'role' => $this->faker->randomElement([1,2]),
                'mobile_no' => $this->faker->e164phoneNumber,
                'address' => $this->faker->state,
                'national_id_no' => strtoupper(Str::random(14)),
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'remember_token' => Str::random(10),
  ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}

