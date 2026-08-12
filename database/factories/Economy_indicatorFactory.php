<?php

namespace Database\Factories;

use App\Models\Gov;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Economy_indicator>
 */
class Economy_indicatorFactory extends Factory
{
    public function definition(): array
    {
        $gov = Gov::factory()->create();

        return [
            'gov_code'       => $gov->code,
            'year'           => $this->faker->year(),
            'poverty'        => $this->faker->randomFloat(2, 1, 20),
            'unemployment'   => $this->faker->randomFloat(2, 1, 15),
            'hdci'           => $this->faker->randomFloat(2, 50, 90),
            'gdp_perkapita'  => $this->faker->numberBetween(10_000_000, 100_000_000),
            'gdp_growth'     => $this->faker->randomFloat(2, -2, 10),
            'gdp'            => $this->faker->numberBetween(1_000_000_000, 50_000_000_000),
            'infras_real'    => $this->faker->randomFloat(2, 0, 100),
            'fiscal_ratio'   => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
