<?php

namespace Database\Factories;

use App\Models\Gov;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessee>
 */
class AssesseeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gov_id'   => Gov::factory(),
            'user_id'  => User::factory(),
            'position' => $this->faker->jobTitle(),
            'contact'  => $this->faker->phoneNumber(),
            'address'  => $this->faker->address(),
        ];
    }
}
