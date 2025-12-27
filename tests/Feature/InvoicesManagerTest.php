<?php

namespace Tests\Feature;

use App\Http\Livewire\InvoicesManager;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OutOfStock;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\TreatmentPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoicesManagerTest extends TestCase
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

    public function test_admin_must_select_clinic_before_creating_invoice(): void
    {
        $admin = $this->createAdminUser();
        Clinic::factory()->create(['name' => 'Primary Clinic']);

        $this->actingAs($admin);

        Livewire::test(InvoicesManager::class)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', function ($value) {
                return is_string($value) && $value !== 'all';
            })
            ->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->call('openModal')
            ->assertSet('isModalOpen', false)
            ->assertSee('Select a clinic before creating an invoice.');
    }

    public function test_admin_can_create_invoice_and_updates_inventory(): void
    {
        $clinic = Clinic::factory()->create(['name' => 'North Clinic', 'prefix' => 'NC']);

        $admin = $this->createAdminUser();

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'female',
            'token' => 'PAT' . Str::random(4),
        ]);

        $package = TreatmentPackage::factory()->create([
            'name' => 'Facial Package',
            'price' => 150.00,
        ]);

        $pharmacy = Pharmacy::factory()->create([
            'clinic_id' => $clinic->id,
            'name' => 'Radiant Cream',
            'selling_price' => 45.00,
            'net_price' => 30.00,
        ]);

        OutOfStock::create([
            'phar_id' => $pharmacy->id,
            'clinic_id' => $clinic->id,
            'total' => 50,
            'sale' => 0,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(InvoicesManager::class);

        $component
            ->set('clinicId', (string) $clinic->id)
            ->call('openModal')
            ->assertSet('isModalOpen', true)
            ->set('patientId', $patient->id)
            ->set('treatmentLines.0.treatment_package_id', $package->id)
            ->set('treatmentLines.0.qty', 1)
            ->set('treatmentLines.0.discount', 10)
            ->set('treatmentLines.0.discount_type', 'fixed')
            ->call('addSaleLine')
            ->set('saleLines.0.pharmacy_id', $pharmacy->id)
            ->set('saleLines.0.qty', 2)
            ->set('saleLines.0.discount', 0)
            ->call('saveInvoice')
            ->assertSet('isModalOpen', false)
            ->assertSee('created successfully.');

        $this->assertDatabaseCount('invoices', 1);
        $invoice = Invoice::with('items')->first();

        $this->assertNotNull($invoice->invoice_number);
        $this->assertEquals($clinic->id, $invoice->clinic_id);
        $this->assertEquals($patient->id, $invoice->patient_id);
        $this->assertEquals(2, $invoice->items->count());

        $treatmentItem = $invoice->items->firstWhere('item_type', InvoiceItem::TYPE_TREATMENT);
        $saleItem = $invoice->items->firstWhere('item_type', InvoiceItem::TYPE_SALE);

        $this->assertNotNull($treatmentItem);
    $this->assertEquals($package->id, $treatmentItem->treatment_package_id);
        $this->assertEquals(140.00, (float) $treatmentItem->subtotal); // 150 - 10 discount

        $this->assertNotNull($saleItem);
        $this->assertEquals($pharmacy->id, $saleItem->pharmacy_id);
        $this->assertEquals(2, $saleItem->qty);
        $this->assertEquals(90.00, (float) $saleItem->subtotal);

        $this->assertDatabaseHas('out_of_stocks', [
            'phar_id' => $pharmacy->id,
            'clinic_id' => $clinic->id,
            'sale' => 2,
        ]);
    }
}
