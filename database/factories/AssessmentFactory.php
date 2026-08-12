<?php

namespace Database\Factories;

use App\Models\Assessee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assessee_id' => Assessee::factory(),
            'date'        => $this->faker->date('Y-m-d', '-1 year'),
            'info'        => $this->faker->sentence(),
            'result'      => $this->faker->randomElement(['Pending', 'Draft', '']),
        ];
    }
}
