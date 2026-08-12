<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Assessee;
use App\Models\Gov;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetsUpRolesAndPermissions;

class AssessmentTest extends TestCase
{
    use RefreshDatabase, SetsUpRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // -----------------------------------------------------------------------
    // Authorization
    // -----------------------------------------------------------------------

    public function test_guest_cannot_access_assessments_index(): void
    {
        $response = $this->get(route('assessments.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_create_assessment(): void
    {
        $assessee = Assessee::factory()->create();
        $response = $this->post(route('assessments.store'), [
            'assessee_id' => $assessee->id,
            'date'        => '2025-01-01',
        ]);
        $response->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function test_admin_can_view_assessments_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('assessments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessments/Index')
                 ->has('assessments')
        );
    }

    // -----------------------------------------------------------------------
    // Create / Store
    // -----------------------------------------------------------------------

    public function test_admin_can_view_create_assessment_form(): void
    {
        $gov      = Gov::factory()->create();
        $admin    = $this->createAdmin();
        Assessee::factory()->create(['user_id' => $admin->id, 'gov_id' => $gov->id]);

        $response = $this->actingAs($admin)->get(route('assessments.create'));

        // Will redirect because $assessee = Assessee::where("user_id", Auth::id())->first()
        // and admin may not have an assessee — acceptable for route access test
        $response->assertStatus(200)->assertOk();
    }

    public function test_admin_can_store_new_assessment(): void
    {
        $admin    = $this->createAdmin();
        $assessee = Assessee::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('assessments.store'), [
            'assessee_id' => $assessee->id,
            'date'        => '2025-06-01',
            'info'        => 'Initial assessment info',
        ]);

        $response->assertRedirect(route('assessments.index'));
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('assessments', [
            'assessee_id' => $assessee->id,
            'date'        => '2025-06-01',
        ]);
    }

    public function test_store_assessment_fails_without_assessee_id(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('assessments.store'), [
            'date' => '2025-06-01',
        ]);

        $response->assertSessionHasErrors('assessee_id');
    }

    public function test_store_assessment_fails_without_date(): void
    {
        $admin    = $this->createAdmin();
        $assessee = Assessee::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('assessments.store'), [
            'assessee_id' => $assessee->id,
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_store_assessment_fails_with_invalid_assessee_id(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('assessments.store'), [
            'assessee_id' => 99999,
            'date'        => '2025-06-01',
        ]);

        $response->assertSessionHasErrors('assessee_id');
    }

    // -----------------------------------------------------------------------
    // Edit / Update
    // -----------------------------------------------------------------------

    public function test_admin_can_view_edit_assessment_form(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)->get(route('assessments.edit', $assessment));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('assessments/Edit')
                 ->has('assessment')
                 ->has('assessee')
        );
    }

    public function test_admin_can_update_assessment(): void
    {
        $admin      = $this->createAdmin();
        $assessee   = Assessee::factory()->create(['user_id' => $admin->id]);
        $assessment = Assessment::factory()->create(['assessee_id' => $assessee->id]);

        $response = $this->actingAs($admin)->put(route('assessments.update', $assessment), [
            'assessee_id' => $assessee->id,
            'date'        => '2025-09-15',
            'info'        => 'Updated info text',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('assessments', [
            'id'   => $assessment->id,
            'date' => '2025-09-15',
            'info' => 'Updated info text',
        ]);
    }

    public function test_update_assessment_fails_without_required_fields(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)->put(route('assessments.update', $assessment), []);

        $response->assertSessionHasErrors(['assessee_id', 'date']);
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    public function test_admin_can_delete_assessment(): void
    {
        $admin      = $this->createAdmin();
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($admin)->delete(route('assessments.destroy', $assessment));

        $response->assertRedirect(route('assessments.index'));
        $response->assertSessionHas('message');
        $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
    }
}
