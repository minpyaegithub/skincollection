<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientsIndexClinicFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_sees_all_patients_by_default_and_can_filter_by_clinic(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A', 'prefix' => 'A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B', 'prefix' => 'B']);

        Patient::factory()->create([
            'clinic_id' => $clinicA->id,
            'token' => 'A0001',
        ]);
        Patient::factory()->create([
            'clinic_id' => $clinicB->id,
            'token' => 'B0001',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['clinic_id' => $clinicA->id]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        // Default: all patients
        $this->get(route('patients.index'))
            ->assertOk()
            ->assertSee('All Clinics')
            ->assertSee('Clinic')
            ->assertSee('Clinic A')
            ->assertSee('Clinic B');

        // Filter: clinic A only
        $this->get(route('patients.index', ['clinic_id' => $clinicA->id]))
            ->assertOk()
            ->assertSee('A0001')
            ->assertDontSee('B0001');
    }

    public function test_non_admin_non_doctor_does_not_see_filter_and_is_scoped_to_own_clinic(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A', 'prefix' => 'A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B', 'prefix' => 'B']);

        Patient::factory()->create(['clinic_id' => $clinicA->id]);
        Patient::factory()->create(['clinic_id' => $clinicB->id]);

        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff = User::factory()->create(['clinic_id' => $clinicA->id]);
        $staff->assignRole($staffRole);

        $this->actingAs($staff);

        $this->get(route('patients.index'))
            ->assertOk()
            ->assertDontSee('All Clinics')
            ->assertSee('Clinic')
            ->assertSee('Clinic A')
            ->assertDontSee('Clinic B');
    }

    public function test_doctor_sees_all_patients_without_clinic_filter_dropdown(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A', 'prefix' => 'A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B', 'prefix' => 'B']);

        Patient::factory()->create([
            'clinic_id' => $clinicA->id,
            'token' => 'A0001',
        ]);
        Patient::factory()->create([
            'clinic_id' => $clinicB->id,
            'token' => 'B0001',
        ]);

        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $doctor = User::factory()->create(['clinic_id' => $clinicA->id]);
        $doctor->assignRole($doctorRole);

        $this->actingAs($doctor);

        $this->get(route('patients.index'))
            ->assertOk()
            // Filter dropdown is admin-only
            ->assertDontSee('All Clinics')
            // But doctor can see patients from multiple clinics
            ->assertSee('A0001')
            ->assertSee('B0001');
    }
}
