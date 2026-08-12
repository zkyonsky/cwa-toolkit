<?php

namespace Tests\Feature\AssessmentDetail;

use App\Models\Assessment;
use App\Models\Assessee;
use App\Models\Budget_real;
use App\Models\Financial_indicator;
use App\Models\Gov;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetsUpRolesAndPermissions;

class FinancialConditionTest extends TestCase
{
    use RefreshDatabase, SetsUpRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function makeAssessmentWithGov(): array
    {
        $gov        = Gov::factory()->create();
        $assessee   = Assessee::factory()->create(['gov_id' => $gov->id]);
        $assessment = Assessment::factory()->create(['assessee_id' => $assessee->id]);

        return compact('gov', 'assessee', 'assessment');
    }

    private function seedBudgetReal(Gov $gov, int $year): Budget_real
    {
        return Budget_real::create([
            'gov_code'                => $gov->code,
            'year'                    => $year,
            'bpk_opinion'             => 'WTP',
            'input_status'            => 'Final',
            'income_after_cleansing'  => 1_000_000_000,
            'pad_after_cleansing'     => 200_000_000,
            'transfer_income'         => 600_000_000,
            'other_legitimate_income' => 50_000_000,
            'spending_after_cleansing'=> 900_000_000,
            'operational_spending'    => 600_000_000,
            'capital_spending'        => 150_000_000,
            'employee_spending'       => 300_000_000,
            'total_transfer'          => 50_000_000,
        ]);
    }

    // -----------------------------------------------------------------------
    // Edit (GET)
    // -----------------------------------------------------------------------

    public function test_guest_cannot_access_financial_condition_edit(): void
    {
        $assessment = Assessment::factory()->create();

        $this->get(route('assessment-details.financial-condition.edit', $assessment))
             ->assertRedirect(route('login'));
    }

    public function test_edit_financial_condition_returns_correct_inertia_data(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = (int) date('Y') - 1;
        $this->seedBudgetReal($gov, $year);

        $response = $this->actingAs($admin)
                         ->get(route('assessment-details.financial-condition.edit', $assessment));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessmentDetail/EditFinancialCondition')
                 ->has('assessment')
                 ->has('financialData')
                 ->has('years')
                 ->has('govName')
                 ->has('govLevel')
                 ->has('financialIndicator')
        );
    }

    public function test_edit_financial_condition_uses_query_string_years(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $this->seedBudgetReal($gov, 2022);
        $this->seedBudgetReal($gov, 2023);
        $this->seedBudgetReal($gov, 2024);

        $response = $this->actingAs($admin)
                         ->get(route('assessment-details.financial-condition.edit', $assessment) . '?start_year=2022&end_year=2024');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessmentDetail/EditFinancialCondition')
                 ->where('years', [2022, 2023, 2024])
        );
    }

    // -----------------------------------------------------------------------
    // Update (PUT)
    // -----------------------------------------------------------------------

    public function test_update_financial_condition_creates_new_indicator(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.financial-condition.update', $assessment), [
                             'total_revenue'                   => 1_000_000_000,
                             'total_pad'                       => 200_000_000,
                             'pad_growth'                      => 10.5,
                             'pad_revenue'                     => 20.0,
                             'transfer_revenue'                => 60.0,
                             'other_total_revenue'             => 5.0,
                             'volatil_pad'                     => 3.5,
                             'operation_revenue'               => 70.0,
                             'surplus_deficit_before_financing'=> 5.0,
                             'capital_spending'                => 15.0,
                             'employee_spending'               => 35.0,
                             'pad_last_three_year'             => 8.0,
                             'total_debt'                      => 0,
                             'debt_gdp'                        => 0,
                             'debt_revenue'                    => 0,
                             'ds_revenue'                      => 0,
                             'dscr'                            => 0,
                             'fiscal_capacity'                 => 'Sedang',
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');

        $this->assertDatabaseHas('financial_indicators', [
            'assessment_id'  => $assessment->id,
            'fiscal_capacity'=> 'Sedang',
            'total_revenue'  => 1_000_000_000,
        ]);
    }

    public function test_update_financial_condition_updates_existing_indicator(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        // Pre-create
        Financial_indicator::create([
            'assessment_id'  => $assessment->id,
            'fiscal_capacity'=> 'Rendah',
            'volatil_pad'    => 5.0,
            'pad_last_three_year' => 3.0,
            'total_debt'     => 0,
        ]);

        $this->actingAs($admin)
             ->put(route('assessment-details.financial-condition.update', $assessment), [
                 'fiscal_capacity'  => 'Tinggi',
                 'volatil_pad'      => 8.0,
                 'pad_last_three_year' => 12.0,
                 'total_debt'       => 100_000_000,
             ]);

        $this->assertDatabaseHas('financial_indicators', [
            'assessment_id'  => $assessment->id,
            'fiscal_capacity'=> 'Tinggi',
            'total_debt'     => 100_000_000,
        ]);
        $this->assertDatabaseCount('financial_indicators', 1);
    }

    public function test_update_financial_condition_validates_numeric_fields(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.financial-condition.update', $assessment), [
                             'total_revenue' => 'not-a-number',
                             'total_pad'     => 'abc',
                             'pad_growth'    => 'xyz',
                         ]);

        $response->assertSessionHasErrors(['total_revenue', 'total_pad', 'pad_growth']);
    }

    public function test_update_financial_condition_redirects_to_economy_condition(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.financial-condition.update', $assessment), [
                             'fiscal_capacity' => 'Sedang',
                         ]);

        $response->assertRedirect(
            route('assessment-details.economy-condition.edit', $assessment->id)
        );
    }

    public function test_update_financial_condition_accepts_null_values(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.financial-condition.update', $assessment), [
                             'total_revenue' => null,
                             'fiscal_capacity' => null,
                         ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }
}
