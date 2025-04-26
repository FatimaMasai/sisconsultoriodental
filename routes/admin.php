<?php

use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SpecialityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function()
{
    return view('admin.dashboard');
})->name('dasboard');

//rutas de administrador
Route::resource('service_categories', ServiceCategoryController::class);
Route::resource('services', ServiceController::class);
Route::resource('persons', PersonController::class);
Route::resource('patients', PatientController::class);
Route::resource('specialities', SpecialityController::class);
Route::resource('doctors', DoctorController::class);
Route::resource('sales', SaleController::class);

Route::get('/sales/{salePrint}/print', [SaleController::class, 'print'])->name('sales.print');

Route::get('/service-categories/pdf', [ServiceCategoryController::class, 'pdf'])->name('service_categories.pdf');