<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientTokenUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_patient_store_generates_unique_tokens(): void
    {
        $clinic = Clinic::factory()->create([
            'prefix' => 'MAIN',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create([
            'clinic_id' => $clinic->id,
        ]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        // Call the real endpoint twice; it should generate MAIN0001 then MAIN0002.
        $payload = [
            'first_name' => 'First',
            'last_name' => 'Patient',
            'gender' => 'Female',
        ];

        $this->post(route('patients.store'), $payload)->assertRedirect(route('patients.index'));
        $this->post(route('patients.store'), array_merge($payload, ['first_name' => 'Second']))->assertRedirect(route('patients.index'));

        $tokens = Patient::orderBy('id')->pluck('token')->all();
        $this->assertSame(['MAIN0001', 'MAIN0002'], $tokens);
    }
}
