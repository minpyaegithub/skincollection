<?php

namespace Tests\Feature;

use App\Http\Livewire\ExpensesManager;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpensesManagerTest extends TestCase
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

        Expense::factory()->for($clinic)->create(['category' => 'Alpha Expense']);
        Expense::factory()->for($otherClinic)->create(['category' => 'Beta Expense']);

        $this->actingAs($doctor);

        Livewire::test(ExpensesManager::class)
            ->assertSet('isAdmin', false)
            ->assertSet('clinicId', (string) $clinic->id)
            ->assertSee('Alpha Expense')
            ->assertDontSee('Beta Expense')
            ->set('clinicId', (string) $otherClinic->id)
            ->assertSet('clinicId', (string) $clinic->id);
    }

    public function test_admin_can_toggle_clinic_scope_and_create_expense(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B']);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        Expense::factory()->for($clinicA)->create(['category' => 'Clinic A Expense']);
        Expense::factory()->for($clinicB)->create(['category' => 'Clinic B Expense']);

        $this->actingAs($admin);

        Livewire::test(ExpensesManager::class)
            ->assertSet('isAdmin', true)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', (string) $clinicA->id)
            ->assertSee('Clinic A Expense')
            ->assertDontSee('Clinic B Expense')
            ->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->assertSee('Clinic A Expense')
            ->assertSee('Clinic B Expense')
            ->set('clinicId', (string) $clinicA->id)
            ->assertSet('viewingAllClinics', false)
            ->assertSee('Clinic A Expense')
            ->assertDontSee('Clinic B Expense')
            ->call('openModal')
            ->set('category', 'New Expense')
            ->set('amount', 123.45)
            ->set('expense_date', now()->format('Y-m-d'))
            ->set('description', 'Test Description')
            ->call('saveExpense');

        $this->assertDatabaseHas('expenses', [
            'category' => 'New Expense',
            'clinic_id' => $clinicA->id,
        ]);
    }
}
