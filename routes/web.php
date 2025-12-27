<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\WeightController;
use App\Http\Controllers\PatientPhotoController;
use App\Http\Controllers\PatientRecordController;
use App\Http\Controllers\ReportController;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\PatientManagement;
use App\Http\Livewire\UserManagement;
use App\Http\Livewire\ClinicManagement;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ClinicSelectionController;
use App\Http\Controllers\TreatmentPackageController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => true]);

// Test route for appointments calendar
Route::get('/test-appointments', function() {
    return view('test-appointments');
});

// Test route for clinic management
Route::get('/test-clinic', function() {
    return view('test-clinic');
});
Route::get('/inventory-home', [HomeController::class, 'Inventoryindex'])
    ->name('inventory-home')
    ->middleware(['auth', 'clinic.context']);

// Livewire Routes
Route::middleware(['auth', 'clinic.context'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    //Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/patients', PatientManagement::class)->name('patients.index')->middleware('permission:view-patients');
    Route::get('/user-management', UserManagement::class)->name('user-management.index')->middleware('permission:view-users');
    Route::get('/clinics', ClinicManagement::class)
        ->name('clinics.index')
        ->middleware(['permission:view-clinics', 'role:admin']);
    Route::post('/clinic-context/select', [ClinicSelectionController::class, 'update'])->name('clinic-context.update');
});

// Profile Routes
Route::prefix('profile')->name('profile.')->middleware('auth')->group(function(){
    Route::get('/', [HomeController::class, 'getProfile'])->name('detail')->middleware('role:admin');
    Route::post('/update', [HomeController::class, 'updateProfile'])->name('update');
    Route::post('/change-password', [HomeController::class, 'changePassword'])->name('change-password');
});

// Roles
Route::resource('roles', App\Http\Controllers\RolesController::class);

// Permissions
Route::resource('permissions', App\Http\Controllers\PermissionsController::class);

// Users 
Route::middleware(['auth', 'permission:view-users'])->prefix('users')->name('users.')->group(function(){
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('permission:create-users');
    Route::post('/store', [UserController::class, 'store'])->name('store')->middleware('permission:create-users');
    Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit')->middleware('permission:edit-users');
    Route::put('/update/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:edit-users');
    Route::delete('/delete/{user}', [UserController::class, 'delete'])->name('destroy')->middleware('permission:delete-users');
    Route::get('/update/status/{user_id}/{status}', [UserController::class, 'updateStatus'])->name('status')->middleware('permission:edit-users');

    
    Route::get('/import-users', [UserController::class, 'importUsers'])->name('import')->middleware('permission:create-users');
    Route::post('/upload-users', [UserController::class, 'uploadUsers'])->name('upload')->middleware('permission:create-users');

    Route::get('export/', [UserController::class, 'export'])->name('export')->middleware('permission:view-users');

});

//clinics
Route::middleware(['auth', 'role:admin'])->prefix('clinics')->name('clinics.')->group(function(){
    Route::get('/', [ClinicController::class, 'index'])->name('index');
});

// Patient
Route::middleware(['auth', 'clinic.context'])->prefix('patients')->name('patients.')->group(function(){
    Route::get('/', [PatientController::class, 'index'])->name('index');
    Route::get('/create', [PatientController::class, 'create'])->name('create');
    Route::post('/store', [PatientController::class, 'store'])->name('store');
    Route::get('/edit/{patient}', [PatientController::class, 'edit'])->name('edit');
    Route::put('/update/{patient}', [PatientController::class, 'update'])->name('update');
    Route::delete('/delete/{patient}', [PatientController::class, 'delete'])->name('destroy');
    Route::get('/update/status/{patient_id}/{status}', [PatientController::class, 'updateStatus'])->name('status');

    Route::get('/profile/{patient}', [PatientController::class, 'profile'])->name('profile');
    
    Route::get('/import-patient', [PatientController::class, 'importPatients'])->name('import');
    Route::post('/upload-patient', [PatientController::class, 'uploadPatients'])->name('upload');

    Route::get('export/', [PatientController::class, 'export'])->name('export');

});


// Pharmacy
Route::middleware(['auth', 'clinic.context'])->prefix('pharmacy')->name('pharmacy.')->group(function(){
    Route::get('/', [PharmacyController::class, 'index'])->name('index');
});

// Purchase
Route::middleware(['auth', 'clinic.context', 'role:admin'])->prefix('purchase')->name('purchase.')->group(function(){
    Route::get('/', [PurchaseController::class, 'index'])->name('index');
    Route::get('export/', [PurchaseController::class, 'export'])->name('export');
});

// Appointment
Route::middleware(['auth', 'clinic.context'])->prefix('appointments')->name('appointments.')->group(function(){
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
});

// Treatment
Route::middleware(['auth', 'clinic.context'])->prefix('treatment')->name('treatment.')->group(function(){
    Route::get('/', [TreatmentController::class, 'index'])->name('index');
    Route::get('/export', [TreatmentController::class, 'export'])->name('export');
});

// Treatment Packages (global, admin-only)
Route::middleware(['auth', 'clinic.context', 'role:admin'])->prefix('treatment-packages')->name('treatment-packages.')->group(function () {
    Route::get('/', [TreatmentPackageController::class, 'index'])->name('index');
    Route::get('/create', [TreatmentPackageController::class, 'create'])->name('create');
    Route::post('/', [TreatmentPackageController::class, 'store'])->name('store');
    Route::get('/{treatment_package}/edit', [TreatmentPackageController::class, 'edit'])->name('edit');
    Route::put('/{treatment_package}', [TreatmentPackageController::class, 'update'])->name('update');
    Route::delete('/{treatment_package}', [TreatmentPackageController::class, 'destroy'])->name('destroy');
});

//invoice
Route::middleware(['auth', 'clinic.context'])->prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
});

// Expense
Route::middleware(['auth', 'clinic.context'])->prefix('expense')->name('expense.')->group(function(){
    Route::get('/', [ExpenseController::class, 'index'])->name('index');
});

// weight
Route::middleware(['auth', 'clinic.context'])->prefix('weight')->name('weight.')->group(function(){
    Route::get('/', [WeightController::class, 'index'])->name('index');
    Route::get('/create', [WeightController::class, 'create'])->name('create');
    Route::post('/store', [WeightController::class, 'store'])->name('store');
    Route::get('/view/{weight}', [WeightController::class, 'view'])->name('view');
    Route::get('/edit/{weight}', [WeightController::class, 'edit'])->name('edit');
    Route::get('/listByPatient/{patient_id}', [WeightController::class, 'listByPatient'])->name('listByPatient');
    Route::put('/update/{weight}', [WeightController::class, 'update'])->name('update');
    Route::delete('/delete/{weight}', [WeightController::class, 'delete'])->name('destroy');
});

// Patient Photo
Route::middleware(['auth', 'clinic.context'])->prefix('photo')->name('photo.')->group(function(){
    Route::get('/', [PatientPhotoController::class, 'index'])->name('index')->middleware('role:admin');
    Route::get('/create', [PatientPhotoController::class, 'create'])->name('create');
    Route::post('/store', [PatientPhotoController::class, 'store'])->name('store');
    Route::get('/view/{photo}', [PatientPhotoController::class, 'view'])->name('view');
    Route::get('/edit/{photo}', [PatientPhotoController::class, 'edit'])->name('edit');
    Route::put('/update/{photo}', [PatientPhotoController::class, 'update'])->name('update');
    Route::delete('/delete/{photo}', [PatientPhotoController::class, 'delete'])->name('destroy');
});

// Patient Record
Route::middleware(['auth', 'clinic.context'])->prefix('record')->name('record.')->group(function(){
    Route::get('/', [PatientRecordController::class, 'index'])->name('index');
    Route::get('/create', [PatientRecordController::class, 'create'])->name('create');
    Route::post('/store', [PatientRecordController::class, 'store'])->name('store');
    Route::get('/view/{record}', [PatientRecordController::class, 'view'])->name('view');
    Route::get('/edit/{record}', [PatientRecordController::class, 'edit'])->name('edit');
    Route::put('/update/{record}', [PatientRecordController::class, 'update'])->name('update');
    Route::delete('/delete/{record}', [PatientRecordController::class, 'delete'])->name('destroy');
});

// Report
Route::middleware(['auth', 'clinic.context'])->prefix('report')->name('report.')->group(function(){
    Route::get('/Profit-Loss', [ReportController::class, 'index'])->name('index')->middleware('role:Admin');
    Route::get('/getPfData', [ReportController::class, 'getPfData'])->name('getPfData')->middleware('role:Admin');
});


Route::get('/generate-invoice-pdf/{invoice}/{type}', [PDFController::class, 'generateInvoicePDF'])->name('generateInvoicePDF');
Route::get('/invoice/{invoice}/{type}', [PDFController::class, 'generateInvoice'])->name('generateInvoice');
//Route::get('/generate-invoice-pdf', array('as'=> 'generate.invoice.pdf', 'uses' => 'PDFController@generateInvoicePDF'));