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
