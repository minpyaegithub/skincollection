<?php

namespace Tests\Feature;

use App\Http\Livewire\AppointmentsCalendar;
use App\Models\Appointment;
use App\Models\AppointmentTime;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentsCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_doctor_is_locked_to_assigned_clinic(): void
    {
        $clinic = Clinic::factory()->create();
        $otherClinic = Clinic::factory()->create();

        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);

        $doctor = User::factory()->for($clinic)->create();
        $doctor->assignRole($doctorRole);

        $this->actingAs($doctor);

        $component = Livewire::test(AppointmentsCalendar::class);

        $component->assertSet('isAdmin', false)
            ->assertSet('clinicId', (string) $clinic->id)
            ->assertSet('clinicOptions', function ($options) use ($clinic) {
                return count($options) === 1 && (int) $options[0]['id'] === $clinic->id;
            });

        // Attempting to change the clinic should revert back to the assigned clinic
        $component->set('clinicId', (string) $otherClinic->id)
            ->assertSet('clinicId', (string) $clinic->id);
    }

    public function test_admin_can_toggle_between_all_and_single_clinic(): void
    {
        $clinic = Clinic::factory()->create();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $component = Livewire::test(AppointmentsCalendar::class);

        $component->assertSet('isAdmin', true)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('clinicId', (string) $clinic->id);

        $component->set('clinicId', 'all')
            ->assertSet('viewingAllClinics', true)
            ->assertSet('clinicId', 'all');

        $component->set('clinicId', (string) $clinic->id)
            ->assertSet('clinicId', (string) $clinic->id)
            ->assertSet('viewingAllClinics', false)
            ->assertSet('formClinicId', $clinic->id);
    }

    public function test_admin_sees_doctor_created_appointment(): void
    {
        $clinic = Clinic::factory()->create();

        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $doctor = User::factory()->for($clinic)->create();
        $doctor->assignRole($doctorRole);

        $admin = User::factory()->create(['clinic_id' => null]);
        $admin->assignRole($adminRole);

        $timeSlot = AppointmentTime::create([
            'time' => '09:00',
            'custom_time' => '09:00 AM',
        ]);

        $appointment = Appointment::create([
            'name' => 'Test Patient',
            'phone' => '123456789',
            'description' => 'Consultation',
            'status' => 0,
            'date' => now()->format('Y-m-d'),
            'clinic_id' => $clinic->id,
            'time' => '09:00',
        ]);

        $appointment->timeSlots()->attach($timeSlot->id);

        $this->actingAs($admin);

        Livewire::test(AppointmentsCalendar::class)
            ->set('clinicId', (string) $clinic->id)
            ->assertSee('Test Patient');
    }
}
