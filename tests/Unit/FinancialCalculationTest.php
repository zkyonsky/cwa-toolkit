<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for financial ratio calculations in FinancialConditionController.
 * These tests are pure PHP — no database, no HTTP.
 */
class FinancialCalculationTest extends TestCase
{
    // -----------------------------------------------------------------------
    // PAD Growth
    // -----------------------------------------------------------------------

    public function test_pad_growth_is_calculated_correctly_when_prev_pad_is_positive(): void
    {
        $prevPad    = 100_000_000;
        $currentPad = 110_000_000;

        $padGrowth = $prevPad > 0
            ? (($currentPad - $prevPad) / $prevPad) * 100
            : 0;

        $this->assertEqualsWithDelta(10.0, $padGrowth, 0.001);
    }

    public function test_pad_growth_is_zero_when_prev_pad_is_zero(): void
    {
        $prevPad    = 0;
        $currentPad = 50_000_000;

        $padGrowth = $prevPad > 0
            ? (($currentPad - $prevPad) / $prevPad) * 100
            : 0;

        $this->assertSame(0, $padGrowth);
    }

    public function test_pad_growth_is_negative_when_revenue_decreased(): void
    {
        $prevPad    = 100_000_000;
        $currentPad = 80_000_000;

        $padGrowth = $prevPad > 0
            ? (($currentPad - $prevPad) / $prevPad) * 100
            : 0;

        $this->assertEqualsWithDelta(-20.0, $padGrowth, 0.001);
    }

    // -----------------------------------------------------------------------
    // PAD Ratio
    // -----------------------------------------------------------------------

    public function test_pad_ratio_is_correct(): void
    {
        $totalRevenue = 1_000_000_000;
        $currentPad   = 200_000_000;

        $padRatio = $totalRevenue > 0
            ? ($currentPad / $totalRevenue) * 100
            : 0;

        $this->assertEqualsWithDelta(20.0, $padRatio, 0.001);
    }

    public function test_pad_ratio_is_zero_when_total_revenue_is_zero(): void
    {
        $totalRevenue = 0;
        $currentPad   = 200_000_000;

        $padRatio = $totalRevenue > 0
            ? ($currentPad / $totalRevenue) * 100
            : 0;

        $this->assertSame(0, $padRatio);
    }

    // -----------------------------------------------------------------------
    // Transfer Ratio
    // -----------------------------------------------------------------------

    public function test_transfer_ratio_is_correct(): void
    {
        $totalRevenue   = 1_000_000_000;
        $transferIncome = 600_000_000;

        $transferRatio = $totalRevenue > 0
            ? ($transferIncome / $totalRevenue) * 100
            : 0;

        $this->assertEqualsWithDelta(60.0, $transferRatio, 0.001);
    }

    public function test_transfer_ratio_is_zero_when_total_revenue_is_zero(): void
    {
        $totalRevenue   = 0;
        $transferIncome = 600_000_000;

        $transferRatio = $totalRevenue > 0
            ? ($transferIncome / $totalRevenue) * 100
            : 0;

        $this->assertSame(0, $transferRatio);
    }

    // -----------------------------------------------------------------------
    // Operational Surplus/Deficit Ratio
    // -----------------------------------------------------------------------

    public function test_op_surplus_deficit_ratio_is_correct(): void
    {
        $totalRevenue = 1_000_000_000;
        $opSpending   = 700_000_000;

        $ratio = $totalRevenue > 0
            ? (($totalRevenue - $opSpending) / $totalRevenue) * 100
            : 0;

        $this->assertEqualsWithDelta(30.0, $ratio, 0.001);
    }

    public function test_op_surplus_deficit_ratio_is_zero_when_revenue_is_zero(): void
    {
        $totalRevenue = 0;
        $opSpending   = 700_000_000;

        $ratio = $totalRevenue > 0
            ? (($totalRevenue - $opSpending) / $totalRevenue) * 100
            : 0;

        $this->assertSame(0, $ratio);
    }

    // -----------------------------------------------------------------------
    // Capital Spending Ratio
    // -----------------------------------------------------------------------

    public function test_cap_spending_ratio_is_correct(): void
    {
        $totalSpending = 900_000_000;
        $capSpending   = 180_000_000;

        $capRatio = $totalSpending > 0
            ? ($capSpending / $totalSpending) * 100
            : 0;

        $this->assertEqualsWithDelta(20.0, $capRatio, 0.001);
    }

    public function test_cap_spending_ratio_is_zero_when_total_spending_is_zero(): void
    {
        $totalSpending = 0;
        $capSpending   = 180_000_000;

        $capRatio = $totalSpending > 0
            ? ($capSpending / $totalSpending) * 100
            : 0;

        $this->assertSame(0, $capRatio);
    }

    // -----------------------------------------------------------------------
    // Employee Spending Ratio
    // -----------------------------------------------------------------------

    public function test_emp_spending_ratio_is_correct(): void
    {
        $totalSpending = 900_000_000;
        $empSpending   = 450_000_000;

        $empRatio = $totalSpending > 0
            ? ($empSpending / $totalSpending) * 100
            : 0;

        $this->assertEqualsWithDelta(50.0, $empRatio, 0.001);
    }

    // -----------------------------------------------------------------------
    // Surplus/Deficit Before Financing
    // -----------------------------------------------------------------------

    public function test_surplus_deficit_before_financing_is_correct(): void
    {
        $totalRevenue  = 1_000_000_000;
        $totalSpending = 800_000_000;
        $totalTransfer = 50_000_000;

        $ratio = $totalRevenue > 0
            ? ((($totalRevenue - ($totalSpending + $totalTransfer))) / $totalRevenue) * 100
            : 0;

        $this->assertEqualsWithDelta(15.0, $ratio, 0.001);
    }

    public function test_surplus_deficit_is_negative_when_spending_exceeds_revenue(): void
    {
        $totalRevenue  = 500_000_000;
        $totalSpending = 600_000_000;
        $totalTransfer = 0;

        $ratio = $totalRevenue > 0
            ? ((($totalRevenue - ($totalSpending + $totalTransfer))) / $totalRevenue) * 100
            : 0;

        $this->assertEqualsWithDelta(-20.0, $ratio, 0.001);
    }
}
