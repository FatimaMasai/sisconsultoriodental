<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SpecialityController;
use App\Http\Controllers\Admin\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function()
{
    return view('admin.dashboard');
})->name('dasboard');

// Route::get('/', function() {
//     return redirect()->route('login');  // Redirige a la página de login
// });


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

Route::resource('histories', HistoryController::class);
Route::post('histories/{id}/add-note',[HistoryController::class, 'addNote'])->name('histories.addNote');
 
Route::get('/histories/{id}/pdf', [HistoryController::class, 'pdf'])->name('histories.pdf');


Route::resource('product_categories', ProductCategoryController::class );
Route::resource('products', ProductController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('purchases', PurchaseController::class);
  
Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

