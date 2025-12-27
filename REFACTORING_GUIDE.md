# Skin Collection - Livewire Refactoring Guide

## Overview

This document outlines the comprehensive refactoring of the Skin Collection Laravel application to use Livewire, implement multi-clinic support with separate counters, and add role-based permissions with S3 storage.

## Key Changes Made

### 1. Livewire Integration
- **Upgraded Livewire**: Updated from v2.12 to v3.0
- **Created Livewire Components**:
  - `Dashboard` - Main dashboard with statistics
  - `PatientManagement` - Patient CRUD operations
  - `UserManagement` - User management with role assignment
  - `ClinicManagement` - Multi-clinic management
  - `ExpensesManager` - Multi-clinic expenses CRUD via Livewire
  - `PharmaciesManager` - Pharmacy catalogue with inventory sync
  - `PurchasesManager` - Purchase tracking per clinic
  - `InvoicesManager` - Sales and treatment invoicing with clinic context
  - `TreatmentsManager` - Treatment catalogue with per-clinic scoping

### 2. Multi-Clinic System
- **Clinic Model**: Enhanced with address, phone, email, status fields
- **Clinic Counters**: Separate counters for each clinic (patient, appointment, invoice, treatment)
- **Clinic Relationships**: All major models now belong to a clinic
- **Counter Management**: Automatic counter initialization for new clinics
- **Legacy Cleanup**: Deprecated treatment package table removed (2025_11_11_010000)

### 3. Role-Based Permissions
- **Roles**: Admin, Doctor, Operator
- **Permissions**: Granular permissions for all operations
- **Middleware**: Custom middleware for role and permission checking
- **Navigation**: Dynamic sidebar based on user permissions

### 4. S3 Storage Integration
- **S3Service**: Custom service for handling S3 uploads
- **Patient Photos**: Automatic S3 upload for patient images
- **File Management**: Delete and URL generation utilities

## Database Changes

### New Migrations
1. `2023_10_30_020000_create_clinic_counters_table.php` - Clinic-specific counters
2. Enhanced existing models with clinic relationships

### Updated Models
- **User**: Added clinic relationship and role helper methods
- **Clinic**: Added counter management and enhanced fields
- **Patient**: Added clinic relationship and S3 photo handling
- **ClinicCounter**: New model for managing clinic-specific counters

## File Structure

```
app/
├── Http/
│   ├── Livewire/
│   │   ├── AppointmentsCalendar.php
│   │   ├── Dashboard.php
│   │   ├── ExpensesManager.php
│   │   ├── InvoicesManager.php
│   │   ├── PatientManagement.php
│   │   ├── PharmaciesManager.php
│   │   ├── PurchasesManager.php
│   │   ├── TreatmentsManager.php
│   │   └── UserManagement.php
│   └── Middleware/
│       ├── CheckRole.php
│       └── CheckPermission.php
├── Models/
│   ├── Clinic.php (enhanced)
│   ├── ClinicCounter.php (new)
│   ├── InvoiceItem.php (new)
│   ├── Patient.php (enhanced)
│   ├── Treatment.php (enhanced)
│   └── User.php (enhanced)
└── Services/
    └── ClinicContext.php (new)

resources/views/livewire/
├── appointments-calendar.blade.php
├── dashboard.blade.php
├── expenses-manager.blade.php
├── invoices-manager.blade.php
├── patient-management.blade.php
├── pharmacies-manager.blade.php
├── purchases-manager.blade.php
├── treatments-manager.blade.php
└── user-management.blade.php
```

## Key Features

### 1. Multi-Clinic Support
- Each clinic has its own counters
- Clinic-specific data isolation
- Admin can manage multiple clinics
- Users belong to specific clinics

### 2. Role-Based Access Control
- **Admin**: Full system access, can create users and assign roles
- **Doctor**: Patient management, appointments, treatments, invoices
- **Operator**: Patient management, appointments, pharmacy, purchases, invoices

### 3. S3 Storage
- Patient photos stored on S3
- Automatic file organization by clinic
- Secure file upload and management

### 4. Livewire Components
- Real-time updates without page refresh
- Form validation and error handling
- Modal-based CRUD operations
- Search and filtering capabilities

## Installation & Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Configuration
Add to `.env`:
```env
# S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=your_region
AWS_BUCKET=your_bucket_name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

### 3. Database Setup
```bash
php artisan migrate:fresh --seed
```

### 4. Default Login
- **Email**: admin@admin.com
- **Password**: Admin@123#

## Usage

### 1. Dashboard
- View clinic statistics
- Quick access to common operations
- Recent activity display

### 2. Patient Management
- Add/edit patients with photos
- Search and filter patients
- S3 photo storage

### 3. Treatments Manager
- Clinic-scoped treatment catalogue
- Status toggling and pricing controls
- Real-time filtering and search

### 4. Expenses Manager
- Clinic-specific expenses with role-aware access
- Modal-based CRUD flow
- Automatic flash messaging and pagination

### 5. Pharmacies Manager
- Inventory-aware pharmacy catalogue
- Clinic selection for admins vs. fixed clinics for staff
- Delete protections and success feedback

### 6. Purchases Manager
- Track purchases per clinic
- Inventory synchronization hooks
- Admin-only deletion safeguards

### 7. Invoices Manager
- Combined treatment/product invoicing
- Inventory sync and clinic counters
- Patient-aware validation

## Permissions

### Admin Permissions
- All permissions
- Can create users and assign roles
- Can manage all clinics
- Full system access

### Doctor Permissions
- View/create/edit patients
- Upload patient photos
- Manage appointments
- Manage treatments
- Create invoices

### Operator Permissions
- View/create/edit patients
- Upload patient photos
- Manage appointments
- Manage pharmacy
- Manage purchases
- Create invoices

### Doctor Permissions
- View/create/edit patients
- Upload patient photos
- Manage appointments
- Manage treatments
- Create invoices

### Operator Permissions
- View/create/edit patients
- Upload patient photos
- Manage appointments
- Manage pharmacy
- Manage purchases
- Create invoices

## API Endpoints

### Livewire Routes
- `/dashboard` - Main dashboard
- `/patients` - Patient management
- `/users` - User management
- `/clinics` - Clinic management

### Middleware Protection
- All routes protected by authentication
- Permission-based access control
- Role-based navigation

## Security Features

1. **Authentication**: Laravel's built-in authentication
2. **Authorization**: Spatie Laravel Permission package
3. **File Security**: S3 with proper access controls
4. **Data Isolation**: Clinic-specific data separation
5. **Role Validation**: Middleware-based permission checking

## Performance Considerations

1. **Database Indexing**: Proper indexes on clinic_id columns
2. **S3 Optimization**: Efficient file upload and storage
3. **Livewire Caching**: Component state management
4. **Query Optimization**: Eager loading relationships

## Troubleshooting

### Common Issues

1. **S3 Upload Failures**: Check AWS credentials and bucket permissions
2. **Permission Errors**: Verify user roles and permissions
3. **Counter Issues**: Ensure clinic counters are initialized
4. **File Upload Errors**: Check file size limits and S3 configuration

### Debug Commands
```bash
# Check user permissions
php artisan tinker
>>> $user = User::find(1);
>>> $user->getAllPermissions();

# Check clinic counters
>>> $clinic = Clinic::find(1);
>>> $clinic->counters;
```

## Future Enhancements

1. **Real-time Notifications**: WebSocket integration
2. **Advanced Reporting**: Clinic-specific analytics
3. **API Integration**: RESTful API for mobile apps
4. **Audit Logging**: Track all system changes
5. **Backup System**: Automated data backup to S3

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify database connections
3. Check S3 configuration
4. Review user permissions and roles

## Conclusion

This refactoring provides a robust, scalable foundation for the Skin Collection application with:
- Modern Livewire-based UI
- Multi-clinic architecture
- Comprehensive role-based security
- S3 cloud storage integration
- Maintainable code structure

The system is now ready for production use with proper security, scalability, and user experience considerations.
