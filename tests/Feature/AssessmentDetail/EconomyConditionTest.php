<?php

namespace Tests\Feature\AssessmentDetail;

use App\Models\Assessment;
use App\Models\Assessee;
use App\Models\Economy_indicator;
use App\Models\Gov;
use App\Models\Sectoral_gdp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetsUpRolesAndPermissions;

class EconomyConditionTest extends TestCase
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

    private function sectoralGdpPayload(): array
    {
        return [
            'agriculture_forestry_fishery'    => 1_000_000,
            'mining_quarrying'                => 500_000,
            'processing_industry'             => 2_000_000,
            'electricity_gas'                 => 100_000,
            'water_waste'                     => 50_000,
            'contruction'                     => 800_000,
            'trade_vehicle_repair'            => 1_200_000,
            'transportation_warehousing'      => 300_000,
            'acomodation_food_beverage'       => 200_000,
            'information_communication'       => 400_000,
            'finance_insurance'               => 250_000,
            'real_estate'                     => 150_000,
            'company_service'                 => 75_000,
            'gov_adm_defense_sosial_security' => 600_000,
            'education_service'               => 500_000,
            'health_social_service'           => 350_000,
            'other_service'                   => 125_000,
        ];
    }

    private function economyIndicatorPayload(): array
    {
        return [
            'poverty'      => 5.5,
            'unemployment' => 3.2,
            'hdci'         => 72.0,
            'gdp_perkapita'=> 45_000_000,
            'gdp_growth'   => 4.8,
        ];
    }

    // -----------------------------------------------------------------------
    // Edit (GET)
    // -----------------------------------------------------------------------

    public function test_guest_cannot_access_economy_condition_edit(): void
    {
        $assessment = Assessment::factory()->create();

        $this->get(route('assessment-details.economy-condition.edit', $assessment))
             ->assertRedirect(route('login'));
    }

    public function test_edit_economy_condition_returns_correct_inertia_data(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = (int) date('Y') - 1;
        Economy_indicator::create(array_merge(['gov_code' => $gov->code, 'year' => $year], $this->economyIndicatorPayload()));

        $response = $this->actingAs($admin)
                         ->get(route('assessment-details.economy-condition.edit', $assessment));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessmentDetail/EditEconomyCondition')
                 ->has('assessment')
                 ->has('govName')
                 ->has('govLevel')
                 ->has('year')
                 ->has('economyIndicator')
                 ->has('sectoralGdp')
                 ->has('comparison')
        );
    }

    // -----------------------------------------------------------------------
    // Update (PUT)
    // -----------------------------------------------------------------------

    public function test_update_economy_condition_validates_required_fields(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.economy-condition.update', $assessment), []);

        $response->assertSessionHasErrors(['economyIndicator', 'sectoralGdp']);
    }

    public function test_update_economy_condition_saves_economy_indicator(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = 2024;

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.economy-condition.update', $assessment), [
                             'year'             => $year,
                             'economyIndicator' => $this->economyIndicatorPayload(),
                             'sectoralGdp'      => $this->sectoralGdpPayload(),
                         ]);

        $response->assertRedirect(
            route('assessment-details.dscr.edit', $assessment->id)
        );
        $response->assertSessionHas('message');

        $this->assertDatabaseHas('economy_indicators', [
            'gov_code'   => $gov->code,
            'year'       => $year,
            'poverty'    => 5.5,
            'hdci'       => 72.0,
        ]);
    }

    public function test_update_economy_condition_saves_sectoral_gdp(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year    = 2024;
        $sectors = $this->sectoralGdpPayload();

        $this->actingAs($admin)
             ->put(route('assessment-details.economy-condition.update', $assessment), [
                 'year'             => $year,
                 'economyIndicator' => $this->economyIndicatorPayload(),
                 'sectoralGdp'      => $sectors,
             ]);

        $this->assertDatabaseHas('sectoral_gdps', [
            'gov_code'                    => $gov->code,
            'year'                        => $year,
            'agriculture_forestry_fishery'=> 1_000_000,
            'processing_industry'         => 2_000_000,
        ]);
    }

    public function test_update_economy_condition_stores_calculated_total_gdp(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year    = 2024;
        $sectors = $this->sectoralGdpPayload();
        $expectedGdp = array_sum($sectors); // sum of all sector values

        $this->actingAs($admin)
             ->put(route('assessment-details.economy-condition.update', $assessment), [
                 'year'             => $year,
                 'economyIndicator' => $this->economyIndicatorPayload(),
                 'sectoralGdp'      => $sectors,
             ]);

        $this->assertDatabaseHas('economy_indicators', [
            'gov_code' => $gov->code,
            'year'     => $year,
            'gdp'      => $expectedGdp,
        ]);
    }

    public function test_update_economy_condition_updates_existing_records(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = 2024;

        // Pre-create
        Economy_indicator::create(array_merge(
            ['gov_code' => $gov->code, 'year' => $year, 'gdp' => 0],
            $this->economyIndicatorPayload()
        ));

        $updatedIndicator = array_merge($this->economyIndicatorPayload(), ['poverty' => 9.9]);

        $this->actingAs($admin)
             ->put(route('assessment-details.economy-condition.update', $assessment), [
                 'year'             => $year,
                 'economyIndicator' => $updatedIndicator,
                 'sectoralGdp'      => $this->sectoralGdpPayload(),
             ]);

        $this->assertDatabaseHas('economy_indicators', [
            'gov_code' => $gov->code,
            'year'     => $year,
            'poverty'  => 9.9,
        ]);
        $this->assertDatabaseCount('economy_indicators', 1);
    }

    // -----------------------------------------------------------------------
    // Destroy (DELETE)
    // -----------------------------------------------------------------------

    public function test_destroy_economy_condition_validates_year_is_required(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)
                         ->delete(route('assessment-details.economy-condition.destroy', $assessment), []);

        $response->assertSessionHasErrors('year');
    }

    public function test_destroy_economy_condition_deletes_data_for_given_year(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        $year = 2022;
        Economy_indicator::create(array_merge(
            ['gov_code' => $gov->code, 'year' => $year, 'gdp' => 0],
            $this->economyIndicatorPayload()
        ));

        $response = $this->actingAs($admin)
                         ->delete(route('assessment-details.economy-condition.destroy', $assessment), [
                             'year' => $year,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');

        $this->assertDatabaseMissing('economy_indicators', [
            'gov_code' => $gov->code,
            'year'     => $year,
        ]);
    }

    public function test_destroy_economy_condition_only_deletes_specified_year(): void
    {
        $admin = $this->createAdmin();
        ['gov' => $gov, 'assessment' => $assessment] = $this->makeAssessmentWithGov();

        Economy_indicator::create(array_merge(['gov_code' => $gov->code, 'year' => 2021, 'gdp' => 0], $this->economyIndicatorPayload()));
        Economy_indicator::create(array_merge(['gov_code' => $gov->code, 'year' => 2022, 'gdp' => 0], $this->economyIndicatorPayload()));

        $this->actingAs($admin)
             ->delete(route('assessment-details.economy-condition.destroy', $assessment), ['year' => 2021]);

        $this->assertDatabaseMissing('economy_indicators', ['gov_code' => $gov->code, 'year' => 2021]);
        $this->assertDatabaseHas('economy_indicators', ['gov_code' => $gov->code, 'year' => 2022]);
    }
}
