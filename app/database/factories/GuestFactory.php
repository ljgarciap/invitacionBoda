<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Familia '.fake()->lastName(),
            'phone' => '57300'.fake()->numerify('#######'),
            'seats' => fake()->numberBetween(1, 4),
        ];
    }
}
