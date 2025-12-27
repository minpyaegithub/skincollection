<?php

namespace Tests\Feature;

use App\Http\Livewire\PurchasesManager;
use App\Models\Clinic;
use App\Models\OutOfStock;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchasesManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createAdminUser(): User
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        return $admin;
    }

    public function test_admin_can_scope_purchases_and_create_new_record(): void
    {
        $clinicA = Clinic::factory()->create(['name' => 'Clinic A']);
        $clinicB = Clinic::factory()->create(['name' => 'Clinic B']);

        $pharmacyA = Pharmacy::factory()->create([
            'clinic_id' => $clinicA->id,
            'name' => 'Alpha Med',
            'selling_price' => 20,
            'net_price' => 15,
        ]);

        $pharmacyB = Pharmacy::factory()->create([
            'clinic_id' => $clinicB->id,
            'name' => 'Beta Med',
            'selling_price' => 18,
            'net_price' => 12,
        ]);

        $purchaseA = Purchase::factory()->forPharmacy($pharmacyA)->create([
            'selling_price' => 30,
            'net_price' => 20,
            'qty' => 10,
            'created_time' => now()->subDays(2),
        ]);

        $purchaseB = Purchase::factory()->forPharmacy($pharmacyB)->create([
            'selling_price' => 40,
            'net_price' => 25,
            'qty' => 8,
            'created_time' => now()->subDay(),
        ]);

        OutOfStock::create([
            'phar_id' => $pharmacyA->id,
            'clinic_id' => $clinicA->id,
            'total' => $purchaseA->qty,
            'sale' => 0,
        ]);

        OutOfStock::create([
            'phar_id' => $pharmacyB->id,
            'clinic_id' => $clinicB->id,
            'total' => $purchaseB->qty,
            'sale' => 0,
        ]);

        $admin = $this->createAdminUser();

        $this->actingAs($admin);

        $component = Livewire::test(PurchasesManager::class)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', (string) $clinicA->id)
            ->assertSee('Alpha Med')
            ->assertDontSee('Beta Med')
            ->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->assertSee('Alpha Med')
            ->assertSee('Beta Med')
            ->set('clinicId', (string) $clinicA->id)
            ->assertSet('viewingAllClinics', false)
            ->assertSee('Alpha Med')
            ->assertDontSee('Beta Med')
            ->call('openModal')
            ->set('pharmacyId', $pharmacyA->id)
            ->set('qty', 5)
            ->set('selling_price', 45.5)
            ->set('net_price', 30.25)
            ->set('purchase_date', now()->format('Y-m-d'))
            ->call('savePurchase');

        $component->assertSet('isModalOpen', false);

        $this->assertDatabaseHas('purchases', [
            'phar_id' => $pharmacyA->id,
            'clinic_id' => $clinicA->id,
            'qty' => 5,
            'selling_price' => 45.5,
            'net_price' => 30.25,
        ]);

        $this->assertDatabaseHas('out_of_stocks', [
            'phar_id' => $pharmacyA->id,
            'clinic_id' => $clinicA->id,
            'total' => 15,
        ]);

        $this->assertDatabaseHas('pharmacies', [
            'id' => $pharmacyA->id,
            'selling_price' => 45.5,
            'net_price' => 30.25,
        ]);
    }

    public function test_admin_can_delete_purchase_and_inventory_is_updated(): void
    {
        $clinic = Clinic::factory()->create();
        $pharmacy = Pharmacy::factory()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Med',
            'selling_price' => 40,
            'net_price' => 30,
        ]);

        $olderPurchase = Purchase::factory()->forPharmacy($pharmacy)->create([
            'selling_price' => 20,
            'net_price' => 12,
            'qty' => 4,
            'created_time' => now()->subDays(3),
        ]);

        $latestPurchase = Purchase::factory()->forPharmacy($pharmacy)->create([
            'selling_price' => 25,
            'net_price' => 15,
            'qty' => 6,
            'created_time' => now()->subDay(),
        ]);

        OutOfStock::create([
            'phar_id' => $pharmacy->id,
            'clinic_id' => $clinic->id,
            'total' => $olderPurchase->qty + $latestPurchase->qty,
            'sale' => 0,
        ]);

        $admin = $this->createAdminUser();

        $this->actingAs($admin);

        Livewire::test(PurchasesManager::class)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', (string) $clinic->id)
            ->call('delete', $latestPurchase->id);

        $this->assertDatabaseMissing('purchases', ['id' => $latestPurchase->id]);

        $this->assertDatabaseHas('out_of_stocks', [
            'phar_id' => $pharmacy->id,
            'total' => $olderPurchase->qty,
        ]);

        $this->assertDatabaseHas('pharmacies', [
            'id' => $pharmacy->id,
            'selling_price' => 20,
            'net_price' => 12,
        ]);
    }
}
