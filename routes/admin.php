<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SpecialityController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function()
{
    return view('admin.dashboard');
})->name('dasboard');

// Route::get('/', function() {
//     return redirect()->route('login');  // Redirige a la página de login
// });

//rutas de administrador
Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::resource('users', UserController::class)->except(['show']); 
Route::resource('roles', RoleController::class);


//ventas
Route::resource('service_categories', ServiceCategoryController::class);
Route::resource('services', ServiceController::class)->except(['show']);
Route::resource('persons', PersonController::class)->except(['show']); 
Route::resource('patients', PatientController::class)->except(['show']);
Route::resource('specialities', SpecialityController::class);
Route::resource('doctors', DoctorController::class);
Route::resource('sales', SaleController::class);


//historias y notas medicas
Route::resource('histories', HistoryController::class);
Route::post('histories/{id}/add-note',[HistoryController::class, 'addNote'])->name('histories.addNote');
 

//compras
Route::resource('product_categories', ProductCategoryController::class );
Route::resource('products', ProductController::class);
Route::resource('suppliers', SupplierController::class);
Route::resource('purchases', PurchaseController::class);
  


                
//PDF 
Route::get('users/pdf', [UserController::class, 'pdf'])->name('users.pdf');


Route::get('/service-categories/pdf', [ServiceCategoryController::class, 'pdf'])->name('service_categories.pdf');
Route::get('/services/pdf', [ServiceController::class, 'pdf'])->name('services.pdf');

//ventas
Route::get('/persons/pdf', [PersonController::class, 'pdf'])->name('persons.pdf');
Route::get('/patients/pdf', [PatientController::class, 'pdf'])->name('patients.pdf');
Route::get('/specialities/pdf', [SpecialityController::class, 'pdf'])->name('specialities.pdf');
Route::get('/doctors/pdf', [DoctorController::class, 'pdf'])->name('doctors.pdf');

Route::get('/sales/{salePrint}/print', [SaleController::class, 'print'])->name('sales.print');
Route::get('/histories/{id}/pdf', [HistoryController::class, 'pdf'])->name('histories.pdf');

//compras
Route::get('/product_categories/pdf', [ProductCategoryController::class, 'pdf'])->name('product_categories.pdf');
Route::get('/products/pdf', [ProductController::class, 'pdf'])->name('products.pdf');
Route::get('/suppliers/pdf', [SupplierController::class, 'pdf'])->name('suppliers.pdf');
Route::get('/purchases/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
Route::get('/purchases/{purchasePrint}/print', [PurchaseController::class, 'print'])->name('purchases.print');

 
   