<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gov>
 */
class GovFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'  => $this->faker->unique()->numberBetween(1000, 99999),
            'name'  => $this->faker->city() . ' City Government',
            'level' => $this->faker->randomElement(['Kabupaten', 'Kota']),
        ];
    }
}
