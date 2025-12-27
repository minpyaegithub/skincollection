<?php

namespace Tests\Feature;

use App\Http\Livewire\TreatmentsManager;
use App\Models\Clinic;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TreatmentsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_non_admin_is_locked_to_assigned_clinic(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B']);

        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);

        $doctor = User::factory()->for($clinicA)->create();
        $doctor->assignRole($doctorRole);

        Treatment::create([
            'clinic_id' => $clinicA->id,
            'name' => 'Hydrafacial',
            'price' => 120,
            'is_active' => true,
        ]);

        Treatment::create([
            'clinic_id' => $clinicB->id,
            'name' => 'Microdermabrasion',
            'price' => 150,
            'is_active' => true,
        ]);

        $this->actingAs($doctor);

        Livewire::test(TreatmentsManager::class)
            ->assertSet('isAdmin', false)
            ->assertSet('clinicId', (string) $clinicA->id)
            ->assertSee('Hydrafacial')
            ->assertDontSee('Microdermabrasion')
            ->set('clinicId', (string) $clinicB->id)
            ->assertSet('clinicId', (string) $clinicA->id);
    }

    public function test_admin_can_toggle_clinic_scope_and_manage_treatments(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'North Clinic']);
        $clinicB = Clinic::factory()->create(['name' => 'South Clinic']);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        $treatmentB = Treatment::create([
            'clinic_id' => $clinicB->id,
            'name' => 'Laser Therapy',
            'price' => 200,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(TreatmentsManager::class)
            ->assertSet('isAdmin', true)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', (string) $clinicA->id)
            ->assertDontSee('Laser Therapy')
            ->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->assertSee('Laser Therapy')
            ->set('clinicId', (string) $clinicA->id)
            ->assertSet('viewingAllClinics', false)
            ->call('openModal')
            ->assertSet('isModalOpen', true)
            ->set('name', 'Chemical Peel')
            ->set('price', 180)
            ->set('durationMinutes', 75)
            ->set('description', 'Deep exfoliation treatment.')
            ->call('saveTreatment')
            ->assertSet('isModalOpen', false)
            ->assertSee('Treatment created successfully.')
            ->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->assertSee('Laser Therapy')
            ->call('toggleStatus', $treatmentB->id)
            ->assertSee('deactivated');

        $this->assertDatabaseHas('treatments', [
            'name' => 'Chemical Peel',
            'clinic_id' => $clinicA->id,
            'price' => 180,
            'duration_minutes' => 75,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('treatments', [
            'id' => $treatmentB->id,
            'is_active' => false,
        ]);
    }
}
