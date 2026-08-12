<?php

namespace Tests\Feature\AssessmentDetail;

use App\Models\Assessment;
use App\Models\Assessee;
use App\Models\Budget_real;
use App\Models\Gov;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetsUpRolesAndPermissions;

class BudgetRealTest extends TestCase
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

    private function budgetRealPayload(string $govCode, int $year): array
    {
        return [
            'bpk_opinion'               => 'WTP',
            'input_status'              => 'Final',
            'income_after_cleansing'    => 1_000_000_000,
            'pad_after_cleansing'       => 200_000_000,
            'tax_income'                => 50_000_000,
            'retribution_income'        => 30_000_000,
            'asset_income'              => 20_000_000,
            'other_pad'                 => 10_000_000,
            'transfer_income'           => 600_000_000,
            'other_legitimate_income'   => 50_000_000,
            'other_income'              => 0,
            'spending_after_cleansing'  => 900_000_000,
            'operational_spending'      => 600_000_000,
            'employee_spending'         => 300_000_000,
            'good_service_spending'     => 200_000_000,
            'interest_spending'         => 0,
            'subsidy_spending'          => 0,
            'grant_spending'            => 0,
            'social_spending'           => 0,
            'capital_spending'          => 150_000_000,
            'land_spending'             => 0,
            'machine_spending'          => 0,
            'building_spending'         => 0,
            'infrastructure_spending'   => 0,
            'other_fix_asset_spending'  => 0,
            'other_asset_spending'      => 0,
            'unexpected_spending'       => 0,
            'total_transfer'            => 50_000_000,
        ];
    }

    // -----------------------------------------------------------------------
    // Edit (GET)
    // -----------------------------------------------------------------------

    public function test_guest_cannot_access_budget_real_edit(): void
    {
        $assessment = Assessment::factory()->create();

        $this->get(route('assessment-details.budget-real.edit', $assessment))
             ->assertRedirect(route('login'));
    }

    public function test_edit_budget_real_returns_correct_inertia_data(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        // Seed a budget real for the gov
        Budget_real::create([
            'gov_code'     => $gov->code,
            'year'         => 2024,
            'bpk_opinion'  => 'WTP',
            'input_status' => 'Final',
        ]);

        $response = $this->actingAs($admin)
                         ->get(route('assessment-details.budget-real.edit', $assessment));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessmentDetail/EditBudgetReal')
                 ->has('assessment')
                 ->has('budgetReals')
                 ->has('years')
                 ->has('latestYear')
        );
    }

    // -----------------------------------------------------------------------
    // Update (PUT)
    // -----------------------------------------------------------------------

    public function test_update_budget_real_validates_required_budget_reals(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.budget-real.update', $assessment), []);

        $response->assertSessionHasErrors('budgetReals');
    }

    public function test_update_budget_real_saves_data_for_given_years(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = 2024;

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.budget-real.update', $assessment), [
                             'budgetReals' => [
                                 $year => $this->budgetRealPayload($gov->code, $year),
                             ],
                         ]);

        $response->assertRedirect(
            route('assessment-details.financial-condition.edit', $assessment->id)
        );
        $response->assertSessionHas('message');

        $this->assertDatabaseHas('budget_reals', [
            'gov_code'             => $gov->code,
            'year'                 => $year,
            'income_after_cleansing' => 1_000_000_000,
            'capital_spending'     => 150_000_000,
        ]);
    }

    public function test_update_budget_real_updates_existing_record(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = 2023;

        // Pre-create a record
        Budget_real::create([
            'gov_code'                => $gov->code,
            'year'                    => $year,
            'bpk_opinion'             => 'WDP',
            'input_status'            => 'Final',
            'income_after_cleansing'  => 500_000_000,
        ]);

        $payload = $this->budgetRealPayload($gov->code, $year);
        $payload['bpk_opinion'] = 'WTP';
        $payload['income_after_cleansing'] = 999_000_000;

        $this->actingAs($admin)
             ->put(route('assessment-details.budget-real.update', $assessment), [
                 'budgetReals' => [$year => $payload],
             ]);

        $this->assertDatabaseHas('budget_reals', [
            'gov_code'                => $gov->code,
            'year'                    => $year,
            'bpk_opinion'             => 'WTP',
            'income_after_cleansing'  => 999_000_000,
        ]);
    }

    // -----------------------------------------------------------------------
    // Destroy (DELETE)
    // -----------------------------------------------------------------------

    public function test_destroy_budget_real_validates_year_is_required(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)
                         ->delete(route('assessment-details.budget-real.destroy', $assessment), []);

        $response->assertSessionHasErrors('year');
    }

    public function test_destroy_budget_real_deletes_data_for_given_year(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = 2022;
        Budget_real::create([
            'gov_code'     => $gov->code,
            'year'         => $year,
            'bpk_opinion'  => 'WTP',
            'input_status' => 'Final',
        ]);

        $response = $this->actingAs($admin)
                         ->delete(route('assessment-details.budget-real.destroy', $assessment), [
                             'year' => $year,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');
        $this->assertDatabaseMissing('budget_reals', [
            'gov_code' => $gov->code,
            'year'     => $year,
        ]);
    }

    public function test_destroy_budget_real_only_deletes_specified_year(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        Budget_real::create(['gov_code' => $gov->code, 'year' => 2022, 'bpk_opinion' => 'WTP', 'input_status' => 'Final']);
        Budget_real::create(['gov_code' => $gov->code, 'year' => 2023, 'bpk_opinion' => 'WDP', 'input_status' => 'Final']);

        $this->actingAs($admin)
             ->delete(route('assessment-details.budget-real.destroy', $assessment), ['year' => 2022]);

        $this->assertDatabaseMissing('budget_reals', ['gov_code' => $gov->code, 'year' => 2022]);
        $this->assertDatabaseHas('budget_reals', ['gov_code' => $gov->code, 'year' => 2023]);
    }
}
