<?php

namespace Database\Factories;

use App\Models\Gov;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget_real>
 */
class Budget_realFactory extends Factory
{
    public function definition(): array
    {
        $gov = Gov::factory()->create();

        return [
            'gov_code'                  => $gov->code,
            'year'                      => $this->faker->year(),
            'bpk_opinion'               => $this->faker->randomElement(['WTP', 'WDP', 'TMP']),
            'input_status'              => 'Final',
            'income_after_cleansing'    => $this->faker->numberBetween(100_000_000, 1_000_000_000),
            'pad_after_cleansing'       => $this->faker->numberBetween(10_000_000, 200_000_000),
            'tax_income'                => $this->faker->numberBetween(1_000_000, 50_000_000),
            'retribution_income'        => $this->faker->numberBetween(1_000_000, 20_000_000),
            'asset_income'              => $this->faker->numberBetween(1_000_000, 30_000_000),
            'other_pad'                 => $this->faker->numberBetween(1_000_000, 20_000_000),
            'transfer_income'           => $this->faker->numberBetween(50_000_000, 500_000_000),
            'other_legitimate_income'   => $this->faker->numberBetween(1_000_000, 50_000_000),
            'other_income'              => $this->faker->numberBetween(0, 10_000_000),
            'spending_after_cleansing'  => $this->faker->numberBetween(100_000_000, 900_000_000),
            'operational_spending'      => $this->faker->numberBetween(50_000_000, 500_000_000),
            'employee_spending'         => $this->faker->numberBetween(20_000_000, 200_000_000),
            'good_service_spending'     => $this->faker->numberBetween(10_000_000, 100_000_000),
            'interest_spending'         => $this->faker->numberBetween(0, 10_000_000),
            'subsidy_spending'          => $this->faker->numberBetween(0, 10_000_000),
            'grant_spending'            => $this->faker->numberBetween(0, 10_000_000),
            'social_spending'           => $this->faker->numberBetween(0, 10_000_000),
            'capital_spending'          => $this->faker->numberBetween(10_000_000, 100_000_000),
            'land_spending'             => $this->faker->numberBetween(0, 10_000_000),
            'machine_spending'          => $this->faker->numberBetween(0, 10_000_000),
            'building_spending'         => $this->faker->numberBetween(0, 10_000_000),
            'infrastructure_spending'   => $this->faker->numberBetween(0, 10_000_000),
            'other_fix_asset_spending'  => $this->faker->numberBetween(0, 5_000_000),
            'other_asset_spending'      => $this->faker->numberBetween(0, 5_000_000),
            'unexpected_spending'       => $this->faker->numberBetween(0, 5_000_000),
            'total_transfer'            => $this->faker->numberBetween(0, 50_000_000),
        ];
    }
}
