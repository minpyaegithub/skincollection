<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\TreatmentPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TreatmentPackagesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_view_create_and_update_packages(): void
    {
        $clinic = Clinic::factory()->create();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['clinic_id' => $clinic->id]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $this->get(route('treatment-packages.index'))
            ->assertOk()
            ->assertSee('Treatment Packages');

        $this->post(route('treatment-packages.store'), [
            'name' => 'Package One',
            'description' => 'Desc',
            'price' => 100,
            'sessions' => 5,
            'is_active' => 1,
        ])->assertRedirect(route('treatment-packages.index'));

        $package = TreatmentPackage::query()->where('name', 'Package One')->firstOrFail();

        $this->put(route('treatment-packages.update', $package), [
            'name' => 'Package One Updated',
            'description' => 'Desc 2',
            'price' => 120,
            'sessions' => 6,
            'is_active' => 0,
        ])->assertRedirect(route('treatment-packages.index'));

        $this->assertDatabaseHas('treatment_packages', [
            'id' => $package->id,
            'name' => 'Package One Updated',
            'sessions' => 6,
            'is_active' => 0,
        ]);
    }

    public function test_non_admin_cannot_access_packages_pages(): void
    {
        $clinic = Clinic::factory()->create();

        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff = User::factory()->create(['clinic_id' => $clinic->id]);
        $staff->assignRole($staffRole);

        $this->actingAs($staff);

        $this->get(route('treatment-packages.index'))
            ->assertForbidden();
    }
}
