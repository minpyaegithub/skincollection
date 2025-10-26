<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $permissions = [
            // Clinic permissions
            'view-clinics',
            'create-clinics',
            'edit-clinics',
            'delete-clinics',
            
            // User permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            
            // Patient permissions
            'view-patients',
            'create-patients',
            'edit-patients',
            'delete-patients',
            'view-patient-photos',
            'upload-patient-photos',
            
            // Appointment permissions
            'view-appointments',
            'create-appointments',
            'edit-appointments',
            'delete-appointments',
            
            // Invoice permissions
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'delete-invoices',
            
            // Treatment permissions
            'view-treatments',
            'create-treatments',
            'edit-treatments',
            'delete-treatments',
            
            // Pharmacy permissions
            'view-pharmacy',
            'create-pharmacy',
            'edit-pharmacy',
            'delete-pharmacy',
            
            // Purchase permissions
            'view-purchases',
            'create-purchases',
            'edit-purchases',
            'delete-purchases',
            
            // Reports permissions
            'view-reports',
            'export-data',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $doctorRole = Role::create(['name' => 'doctor']);
        $operatorRole = Role::create(['name' => 'operator']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to doctor
        $doctorRole->givePermissionTo([
            'view-patients',
            'create-patients',
            'edit-patients',
            'view-patient-photos',
            'upload-patient-photos',
            'view-appointments',
            'create-appointments',
            'edit-appointments',
            'view-treatments',
            'create-treatments',
            'edit-treatments',
            'view-invoices',
            'create-invoices',
            'edit-invoices',
        ]);

        // Assign permissions to operator
        $operatorRole->givePermissionTo([
            'view-patients',
            'create-patients',
            'edit-patients',
            'view-patient-photos',
            'upload-patient-photos',
            'view-appointments',
            'create-appointments',
            'edit-appointments',
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'view-pharmacy',
            'create-pharmacy',
            'edit-pharmacy',
            'view-purchases',
            'create-purchases',
            'edit-purchases',
        ]);
    }
}
