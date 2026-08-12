<?php

namespace Tests\Feature\AssessmentDetail;

use App\Models\Assessment;
use App\Models\Assessee;
use App\Models\Gov;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetsUpRolesAndPermissions;

class InfrastructureTest extends TestCase
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

    // -----------------------------------------------------------------------
    // Edit (GET)
    // -----------------------------------------------------------------------

    public function test_guest_cannot_access_infrastructure_edit(): void
    {
        $assessment = Assessment::factory()->create();

        $this->get(route('assessment-details.infrastructure.edit', $assessment))
             ->assertRedirect(route('login'));
    }

    public function test_edit_infrastructure_returns_correct_inertia_data(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $response = $this->actingAs($admin)
                         ->get(route('assessment-details.infrastructure.edit', $assessment));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessmentDetail/EditInfrastructure')
                 ->has('assessment')
                 ->has('infrasService')
                 ->has('infrasPriorities')
                 ->has('infrasConclusion')
        );
    }



    // -----------------------------------------------------------------------
    // Update (PUT)
    // -----------------------------------------------------------------------

    public function test_update_infrastructure_validates_required_fields(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.infrastructure.update', $assessment), []);

        $response->assertSessionHasErrors(['services', 'conclusion']);
    }

    public function test_update_infrastructure_saves_services(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.infrastructure.update', $assessment), [
                             'services'   => [
                                 'education'   => 3,
                                 'health'      => 4,
                                 'water'       => 2,
                                 'waste'       => 1,
                                 'it'          => 3,
                                 'agriculture' => 2,
                                 'transport'   => 3,
                                 'electricity' => 4,
                                 'sport_art_culture' => 2,
                                 'tourism'     => 1,
                                 'food'        => 3,
                                 'commerce'    => 2,
                                 'road'        => 4,
                             ],
                             'priorities' => [],
                             'conclusion' => [
                                 'advantage' => 'Good roads',
                                 'challenge' => 'Limited water supply',
                             ],
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');

        $this->assertDatabaseHas('infras_services', [
            'assessment_id' => $assessment->id,
            'education'     => 3,
            'health'        => 4,
        ]);
    }

    public function test_update_infrastructure_syncs_priorities(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $this->actingAs($admin)
             ->put(route('assessment-details.infrastructure.update', $assessment), [
                 'services'   => ['education' => 3, 'health' => 2, 'water' => 1, 'waste' => 1,
                                  'it' => 1, 'agriculture' => 1, 'transport' => 1, 'electricity' => 1,
                                  'sport_art_culture' => 1, 'tourism' => 1, 'food' => 1, 'commerce' => 1, 'road' => 1],
                 'priorities' => [
                     [
                         'plan'            => 'Build new bridge',
                         'exp_outcome'     => 'Better connectivity',
                         'rank'            => 1,
                         'estimated_cost'  => 5_000_000_000,
                         'fund_source'     => 'APBD',
                         'alt_fund_need'   => 0,
                         'alt_fund_source' => '',
                     ],
                     [
                         'plan'            => 'Road widening',
                         'exp_outcome'     => 'Reduced traffic',
                         'rank'            => 2,
                         'estimated_cost'  => 2_000_000_000,
                         'fund_source'     => 'APBN',
                         'alt_fund_need'   => 1_000_000_000,
                         'alt_fund_source' => 'Obligasi',
                     ],
                 ],
                 'conclusion' => ['advantage' => 'Good', 'challenge' => 'Budget'],
             ]);

        $this->assertDatabaseHas('infras_priorities', [
            'assessment_id' => $assessment->id,
            'plan'          => 'Build new bridge',
            'rank'          => 1,
        ]);
        $this->assertDatabaseHas('infras_priorities', [
            'assessment_id' => $assessment->id,
            'plan'          => 'Road widening',
            'rank'          => 2,
        ]);
    }

    public function test_update_infrastructure_saves_conclusion(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $this->actingAs($admin)
             ->put(route('assessment-details.infrastructure.update', $assessment), [
                 'services'   => ['education' => 3, 'health' => 2, 'water' => 1, 'waste' => 1,
                                  'it' => 1, 'agriculture' => 1, 'transport' => 1, 'electricity' => 1,
                                  'sport_art_culture' => 1, 'tourism' => 1, 'food' => 1, 'commerce' => 1, 'road' => 1],
                 'priorities' => [],
                 'conclusion' => [
                     'advantage' => 'Strong agricultural base',
                     'challenge' => 'Lack of modern infrastructure',
                 ],
             ]);

        $this->assertDatabaseHas('infras_conclusions', [
            'assessment_id' => $assessment->id,
            'advantage'     => 'Strong agricultural base',
            'challenge'     => 'Lack of modern infrastructure',
        ]);
    }

    public function test_update_infrastructure_redirects_to_budget_real(): void
    {
        $admin = $this->createAdmin();
        ['assessment' => $assessment] = $this->makeAssessmentWithGov();

        $response = $this->actingAs($admin)
                         ->put(route('assessment-details.infrastructure.update', $assessment), [
                             'services'   => ['education' => 3, 'health' => 2, 'water' => 1, 'waste' => 1,
                                              'it' => 1, 'agriculture' => 1, 'transport' => 1, 'electricity' => 1,
                                              'sport_art_culture' => 1, 'tourism' => 1, 'food' => 1, 'commerce' => 1, 'road' => 1],
                             'priorities' => [],
                             'conclusion' => ['advantage' => 'x', 'challenge' => 'y'],
                         ]);

        $response->assertRedirect(
            route('assessment-details.budget-real.edit', $assessment->id)
        );
    }
}
