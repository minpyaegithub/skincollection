<?php

namespace Tests\Feature;

use App\Http\Livewire\PharmaciesManager;
use App\Models\Clinic;
use App\Models\OutOfStock;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmaciesManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_non_admin_is_locked_to_assigned_clinic(): void
    {
        $clinic = Clinic::factory()->create(['name' => 'Clinic Alpha']);
        $otherClinic = Clinic::factory()->create(['name' => 'Clinic Beta']);

        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);

        $doctor = User::factory()->for($clinic)->create();
        $doctor->assignRole($doctorRole);

        $alphaPharmacy = Pharmacy::factory()->for($clinic)->create(['name' => 'Alpha Med']);
        OutOfStock::create([
            'phar_id' => $alphaPharmacy->id,
            'clinic_id' => $clinic->id,
            'total' => 10,
            'sale' => 2,
        ]);

        $betaPharmacy = Pharmacy::factory()->for($otherClinic)->create(['name' => 'Beta Med']);
        OutOfStock::create([
            'phar_id' => $betaPharmacy->id,
            'clinic_id' => $otherClinic->id,
            'total' => 5,
            'sale' => 1,
        ]);

        $this->actingAs($doctor);

        Livewire::test(PharmaciesManager::class)
            ->assertSet('isAdmin', false)
            ->assertSet('clinicId', (string) $clinic->id)
            ->assertSee('Alpha Med')
            ->assertDontSee('Beta Med')
            ->set('clinicId', (string) $otherClinic->id)
            ->assertSet('clinicId', (string) $clinic->id);
    }

    public function test_admin_can_toggle_clinic_scope_and_create_pharmacy(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B']);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        $pharmacyA = Pharmacy::factory()->for($clinicA)->create(['name' => 'Alpha Pills']);
        OutOfStock::create([
            'phar_id' => $pharmacyA->id,
            'clinic_id' => $clinicA->id,
            'total' => 20,
            'sale' => 5,
        ]);

        $pharmacyB = Pharmacy::factory()->for($clinicB)->create(['name' => 'Beta Pills']);
        OutOfStock::create([
            'phar_id' => $pharmacyB->id,
            'clinic_id' => $clinicB->id,
            'total' => 15,
            'sale' => 3,
        ]);

        $this->actingAs($admin);

        Livewire::test(PharmaciesManager::class)
            ->assertSet('isAdmin', true)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', (string) $clinicA->id)
            ->assertSee('Alpha Pills')
            ->assertDontSee('Beta Pills')
            ->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->assertSee('Alpha Pills')
            ->assertSee('Beta Pills')
            ->set('clinicId', (string) $clinicA->id)
            ->assertSet('viewingAllClinics', false)
            ->assertSee('Alpha Pills')
            ->assertDontSee('Beta Pills')
            ->call('openModal')
            ->set('name', 'New Pharmacy')
            ->set('formClinicId', (string) $clinicA->id)
            ->call('savePharmacy');

        $this->assertDatabaseHas('pharmacies', [
            'name' => 'New Pharmacy',
            'clinic_id' => $clinicA->id,
        ]);

        $createdPharmacyId = Pharmacy::where('name', 'New Pharmacy')->value('id');

        $this->assertNotNull($createdPharmacyId);

        $this->assertDatabaseHas('out_of_stocks', [
            'phar_id' => $createdPharmacyId,
            'clinic_id' => $clinicA->id,
        ]);
    }
}
