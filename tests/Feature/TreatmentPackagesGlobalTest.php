<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\TreatmentPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TreatmentPackagesGlobalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_packages_are_global_not_clinic_scoped(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A', 'prefix' => 'A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B', 'prefix' => 'B']);

        $package = TreatmentPackage::factory()->create(['name' => 'Global Package']);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['clinic_id' => $clinicA->id]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        // This app doesn't yet have a dedicated packages UI/route.
        // For now, we assert at the model layer that packages aren't tied to a clinic.
        $this->assertDatabaseHas('treatment_packages', [
            'id' => $package->id,
            'name' => 'Global Package',
        ]);

        // Ensure there is no clinic_id column by trying to select it via schema inspection.
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('treatment_packages', 'clinic_id'));

        // Sanity: both clinics exist and package still exists (i.e., not cascaded).
        $this->assertDatabaseHas('clinics', ['id' => $clinicB->id]);
        $this->assertDatabaseCount('treatment_packages', 1);
    }
}
