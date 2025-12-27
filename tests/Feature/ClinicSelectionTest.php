<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClinicSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_switch_to_specific_clinic(): void
    {
        $clinicA = Clinic::factory()->create();
        $clinicB = Clinic::factory()->create();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)
            ->post(route('clinic-context.update'), ['clinic_id' => (string) $clinicA->id]);

        $response->assertRedirect();
        $this->assertSame($clinicA->id, session('clinic_context.selected'));

        $response = $this->post(route('clinic-context.update'), ['clinic_id' => 'all']);

        $response->assertRedirect();
        $this->assertSame('all', session('clinic_context.selected'));

        $response = $this->post(route('clinic-context.update'), ['clinic_id' => (string) $clinicB->id]);

        $response->assertRedirect();
        $this->assertSame($clinicB->id, session('clinic_context.selected'));
    }

    public function test_invalid_clinic_selection_is_rejected(): void
    {
        $clinic = Clinic::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->post(route('clinic-context.update'), ['clinic_id' => '999'])
            ->assertSessionHasErrors('clinic_id');
    }
}
