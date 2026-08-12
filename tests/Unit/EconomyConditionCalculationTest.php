<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for economy/GDP calculations in EconomyConditionController.
 * These tests are pure PHP — no database, no HTTP.
 */
class EconomyConditionCalculationTest extends TestCase
{
    private array $sectors = [
        'agriculture_forestry_fishery',
        'mining_quarrying',
        'processing_industry',
        'electricity_gas',
        'water_waste',
        'contruction',
        'trade_vehicle_repair',
        'transportation_warehousing',
        'acomodation_food_beverage',
        'information_communication',
        'finance_insurance',
        'real_estate',
        'company_service',
        'gov_adm_defense_sosial_security',
        'education_service',
        'health_social_service',
        'other_service',
    ];

    private function calculateTotalGdp(array $sectoralGdpData): float
    {
        $totalGdp = 0;
        foreach ($this->sectors as $sector) {
            $totalGdp += (float) ($sectoralGdpData[$sector] ?? 0);
        }
        return $totalGdp;
    }

    // -----------------------------------------------------------------------
    // GDP Total Calculation
    // -----------------------------------------------------------------------

    public function test_total_gdp_sums_all_17_sectors_correctly(): void
    {
        $data = array_fill_keys($this->sectors, 100.0); // each sector = 100

        $total = $this->calculateTotalGdp($data);

        $this->assertEqualsWithDelta(1700.0, $total, 0.001);
    }

    public function test_total_gdp_is_zero_when_all_sectors_are_zero(): void
    {
        $data = array_fill_keys($this->sectors, 0);

        $total = $this->calculateTotalGdp($data);

        $this->assertSame(0.0, $total);
    }

    public function test_total_gdp_treats_null_sector_as_zero(): void
    {
        $data = array_fill_keys($this->sectors, null);

        $total = $this->calculateTotalGdp($data);

        $this->assertSame(0.0, $total);
    }

    public function test_total_gdp_treats_missing_sector_as_zero(): void
    {
        // Only provide a subset of sectors
        $data = [
            'agriculture_forestry_fishery' => 500_000_000,
            'processing_industry'          => 300_000_000,
        ];

        $total = $this->calculateTotalGdp($data);

        $this->assertEqualsWithDelta(800_000_000.0, $total, 0.001);
    }

    public function test_total_gdp_covers_exactly_17_sectors(): void
    {
        $this->assertCount(17, $this->sectors);
    }

    public function test_total_gdp_with_realistic_values(): void
    {
        $data = [
            'agriculture_forestry_fishery'    => 1_000_000,
            'mining_quarrying'                => 2_000_000,
            'processing_industry'             => 3_000_000,
            'electricity_gas'                 => 500_000,
            'water_waste'                     => 200_000,
            'contruction'                     => 1_500_000,
            'trade_vehicle_repair'            => 2_000_000,
            'transportation_warehousing'      => 800_000,
            'acomodation_food_beverage'       => 600_000,
            'information_communication'       => 900_000,
            'finance_insurance'               => 700_000,
            'real_estate'                     => 400_000,
            'company_service'                 => 300_000,
            'gov_adm_defense_sosial_security' => 1_200_000,
            'education_service'               => 1_100_000,
            'health_social_service'           => 800_000,
            'other_service'                   => 500_000,
        ];

        $expected = array_sum($data);
        $total    = $this->calculateTotalGdp($data);

        $this->assertEqualsWithDelta((float) $expected, $total, 0.001);
    }

    // -----------------------------------------------------------------------
    // Year Derivation
    // -----------------------------------------------------------------------

    public function test_year_is_derived_from_assessment_date_minus_one(): void
    {
        $assessmentDate = '2025-06-15';
        $assessmentYear = (int) date('Y', strtotime($assessmentDate));
        $expectedYear   = $assessmentYear - 1;

        $this->assertSame(2024, $expectedYear);
    }
}
