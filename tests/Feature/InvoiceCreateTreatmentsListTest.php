<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\TreatmentPackage;
use App\Models\User;
use App\Services\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceCreateTreatmentsListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_invoice_form_lists_treatments_from_all_clinics_for_admin(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A', 'prefix' => 'A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B', 'prefix' => 'B']);

        $packageA = TreatmentPackage::factory()->create(['name' => 'Package A', 'price' => 10]);
        $packageB = TreatmentPackage::factory()->create(['name' => 'Package B', 'price' => 20]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['clinic_id' => $clinicA->id]);
        $admin->assignRole($adminRole);

        // Select a specific clinic so the invoice modal can open.
        // Treatments should still list globally (cross-clinic) per requirement.
        app(ClinicContext::class)->setClinic($admin, $clinicA->id);

        $this->actingAs($admin);

        Livewire::test(\App\Http\Livewire\InvoicesManager::class)
            ->call('openModal')
            ->assertSet('isModalOpen', true)
            ->assertSee('Package A')
            ->assertSee('Package B');
    }
}
