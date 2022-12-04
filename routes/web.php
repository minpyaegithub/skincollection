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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/inventory-home', [App\Http\Controllers\HomeController::class, 'Inventoryindex'])->name('inventory-home');

// Profile Routes
Route::prefix('profile')->name('profile.')->middleware('auth')->group(function(){
    Route::get('/', [HomeController::class, 'getProfile'])->name('detail');
    Route::post('/update', [HomeController::class, 'updateProfile'])->name('update');
    Route::post('/change-password', [HomeController::class, 'changePassword'])->name('change-password');
});

// Roles
Route::resource('roles', App\Http\Controllers\RolesController::class);

// Permissions
Route::resource('permissions', App\Http\Controllers\PermissionsController::class);

// Users 
Route::middleware('auth')->prefix('users')->name('users.')->group(function(){
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/store', [UserController::class, 'store'])->name('store');
    Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
    Route::put('/update/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/delete/{user}', [UserController::class, 'delete'])->name('destroy');
    Route::get('/update/status/{user_id}/{status}', [UserController::class, 'updateStatus'])->name('status');

    
    Route::get('/import-users', [UserController::class, 'importUsers'])->name('import');
    Route::post('/upload-users', [UserController::class, 'uploadUsers'])->name('upload');

    Route::get('export/', [UserController::class, 'export'])->name('export');

});

// Patient
Route::middleware('auth')->prefix('patients')->name('patients.')->group(function(){
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
Route::middleware('auth')->prefix('pharmacy')->name('pharmacy.')->group(function(){
    Route::get('/', [PharmacyController::class, 'index'])->name('index');
    Route::get('/create', [PharmacyController::class, 'create'])->name('create');
    Route::post('/store', [PharmacyController::class, 'store'])->name('store');
    Route::get('/edit/{pharmacy}', [PharmacyController::class, 'edit'])->name('edit');
    Route::put('/update/{pharmacy}', [PharmacyController::class, 'update'])->name('update');
    Route::delete('/delete/{pharmacy}', [PharmacyController::class, 'delete'])->name('destroy');

    
    Route::get('/import-pharmacy', [PharmacyController::class, 'importPharmacy'])->name('import');
    Route::post('/upload-pharmacy', [PharmacyController::class, 'uploadPharmacy'])->name('upload');

    Route::get('export/', [PharmacyController::class, 'export'])->name('export');

});

// Purchase
Route::middleware('auth')->prefix('purchase')->name('purchase.')->group(function(){
    Route::get('/', [PurchaseController::class, 'index'])->name('index');
    Route::get('/create', [PurchaseController::class, 'create'])->name('create');
    Route::post('/store', [PurchaseController::class, 'store'])->name('store');
    Route::get('/edit/{purchase}', [PurchaseController::class, 'edit'])->name('edit');
    Route::put('/update/{purchase}', [PurchaseController::class, 'update'])->name('update');
    Route::delete('/delete/{purchase}', [PurchaseController::class, 'delete'])->name('destroy');

    
    Route::get('/import-purchase', [PurchaseController::class, 'importPurchase'])->name('import');
    Route::post('/upload-purchase', [PurchaseController::class, 'uploadPurchase'])->name('upload');

    Route::get('export/', [PurchaseController::class, 'export'])->name('export');

});

// Appointment
Route::middleware('auth')->prefix('appointments')->name('appointments.')->group(function(){
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::get('/list', [AppointmentController::class, 'list'])->name('list');
    Route::get('/view', [AppointmentController::class, 'view'])->name('view');
    Route::get('/create', [AppointmentController::class, 'create'])->name('create');
    Route::post('/store', [AppointmentController::class, 'store'])->name('store');
    Route::get('/editAppointment/', [AppointmentController::class, 'editAppointment'])->name('editAppointment');
    Route::post('/updateAppointment/', [AppointmentController::class, 'updateAppointment'])->name('updateAppointment');
    Route::get('/edit/{appointment}', [AppointmentController::class, 'edit'])->name('edit');
    Route::put('/update/{appointment}', [AppointmentController::class, 'update'])->name('update');
    Route::delete('/delete/{appointment}', [AppointmentController::class, 'delete'])->name('destroy');
});

// Treatment
Route::middleware('auth')->prefix('treatment')->name('treatment.')->group(function(){
    Route::get('/', [TreatmentController::class, 'index'])->name('index');
    Route::get('/create', [TreatmentController::class, 'create'])->name('create');
    Route::post('/store', [TreatmentController::class, 'store'])->name('store');
    Route::get('/edit/{treatment}', [TreatmentController::class, 'edit'])->name('edit');
    Route::put('/update/{treatment}', [TreatmentController::class, 'update'])->name('update');
    Route::delete('/delete/{treatment}', [TreatmentController::class, 'delete'])->name('destroy');

    Route::get('export/', [TreatmentController::class, 'export'])->name('export');

    Route::get('/saveIndex', [TreatmentController::class, 'saveIndex'])->name('saveIndex');
    Route::get('/updateIndex', [TreatmentController::class, 'updateIndex'])->name('updateIndex');

});

//invoice
Route::middleware('auth')->prefix('invoices')->name('invoices.')->group(function(){
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/create', [InvoiceController::class, 'create'])->name('create');
    Route::post('/store', [InvoiceController::class, 'store'])->name('store');
    Route::get('/edit/{invoice}', [InvoiceController::class, 'edit'])->name('edit');
    Route::put('/update/{invoice}', [InvoiceController::class, 'update'])->name('update');
    Route::delete('/delete/{invoice}', [InvoiceController::class, 'delete'])->name('destroy');
});

// Expense
Route::middleware('auth')->prefix('expense')->name('expense.')->group(function(){
    Route::get('/', [ExpenseController::class, 'index'])->name('index');
    Route::get('/create', [ExpenseController::class, 'create'])->name('create');
    Route::post('/store', [ExpenseController::class, 'store'])->name('store');
    Route::get('/edit/{expense}', [ExpenseController::class, 'edit'])->name('edit');
    Route::put('/update/{expense}', [ExpenseController::class, 'update'])->name('update');
    Route::delete('/delete/{expense}', [ExpenseController::class, 'delete'])->name('destroy');
});

// weight
Route::middleware('auth')->prefix('weight')->name('weight.')->group(function(){
    Route::get('/', [WeightController::class, 'index'])->name('index');
    Route::get('/create', [WeightController::class, 'create'])->name('create');
    Route::post('/store', [WeightController::class, 'store'])->name('store');
    Route::get('/edit/{weight}', [WeightController::class, 'edit'])->name('edit');
    Route::put('/update/{weight}', [WeightController::class, 'update'])->name('update');
    Route::delete('/delete/{weight}', [WeightController::class, 'delete'])->name('destroy');
});

// Patient Photo
Route::middleware('auth')->prefix('photo')->name('photo.')->group(function(){
    Route::get('/', [PatientPhotoController::class, 'index'])->name('index');
    Route::get('/create', [PatientPhotoController::class, 'create'])->name('create');
    Route::post('/store', [PatientPhotoController::class, 'store'])->name('store');
    Route::get('/edit/{photo}', [PatientPhotoController::class, 'edit'])->name('edit');
    Route::put('/update/{photo}', [PatientPhotoController::class, 'update'])->name('update');
    Route::delete('/delete/{photo}', [PatientPhotoController::class, 'delete'])->name('destroy');
});

// Patient Record
Route::middleware('auth')->prefix('record')->name('record.')->group(function(){
    Route::get('/', [PatientRecordController::class, 'index'])->name('index');
    Route::get('/create', [PatientRecordController::class, 'create'])->name('create');
    Route::post('/store', [PatientRecordController::class, 'store'])->name('store');
    Route::get('/edit/{record}', [PatientRecordController::class, 'edit'])->name('edit');
    Route::put('/update/{record}', [PatientRecordController::class, 'update'])->name('update');
    Route::delete('/delete/{record}', [PatientRecordController::class, 'delete'])->name('destroy');
});


Route::get('/generate-invoice-pdf/{invoice}/{type}', [PDFController::class, 'generateInvoicePDF'])->name('generateInvoicePDF');
Route::get('/invoice/{invoice}/{type}', [PDFController::class, 'generateInvoice'])->name('generateInvoice');
//Route::get('/generate-invoice-pdf', array('as'=> 'generate.invoice.pdf', 'uses' => 'PDFController@generateInvoicePDF'));