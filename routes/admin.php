<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AuditLogController;
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

// Route::get('/', function()
// {
//     return view('admin.dashboard');
// })->name('dasboard');

Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/dashboard2', [DashboardController::class, 'dashboard2'])->name('dashboard2');
// Route::get('/', function() {
//     return redirect()->route('login');  // Redirige a la página de login
// });

//rutas de administrador
Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');


// Cambiar contraseña (colocar antes del resource)
Route::get('users/{user}/password', [UserController::class, 'editPassword'])->name('users.editPassword');
Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');


Route::resource('users', UserController::class)->except(['show']);





Route::resource('roles', RoleController::class);


//ventas
Route::resource('service_categories', ServiceCategoryController::class);
Route::resource('services', ServiceController::class)->except(['show']);
Route::resource('persons', PersonController::class)->except(['show']);
Route::resource('patients', PatientController::class)->except(['show']);
Route::resource('specialities', SpecialityController::class)->except(['show']);
Route::resource('doctors', DoctorController::class)->except(['show']);

// Debe ir antes del resource: 'sales' tiene ruta 'show' (GET sales/{sale}),
// así que si "excel" fuera después, Laravel intentaría interpretar "excel"
// como un id de venta en vez de llegar al método excel().
Route::get('sales/excel', [SaleController::class, 'excel'])->name('sales.excel');
Route::resource('sales', SaleController::class);

Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
Route::post('sales/{sale}/installments/{installment}/pay', [SaleController::class, 'payInstallment'])->name('sales.installments.pay');
Route::get('sales/{sale}/cuotas-pagadas/pdf', [SaleController::class, 'salePaidInstallmentsPdf'])->name('sales.paid_installments.pdf');

// Reporte de todas las cuotas pagadas (de cualquier venta a Crédito).
Route::get('cuotas-pagadas', [SaleController::class, 'paidInstallments'])->name('installments.paid');
Route::get('cuotas-pagadas/excel', [SaleController::class, 'paidInstallmentsExcel'])->name('installments.paid.excel');
Route::get('cuotas-pagadas/pdf', [SaleController::class, 'paidInstallmentsPdf'])->name('installments.paid.pdf');




//historias y notas medicas
Route::resource('histories', HistoryController::class);
Route::post('histories/{id}/add-note',[HistoryController::class, 'addNote'])->name('histories.addNote');
Route::post('histories/{id}/photos', [HistoryController::class, 'storePhoto'])->name('histories.photos.store');
Route::delete('histories/photos/{id}', [HistoryController::class, 'destroyPhoto'])->name('histories.photos.destroy');


//compras
Route::resource('product_categories', ProductCategoryController::class )->except(['show']);
Route::resource('products', ProductController::class)->except(['show']);
Route::resource('suppliers', SupplierController::class)->except(['show']);

// Igual que con 'sales/excel': 'purchases' tiene ruta 'show' (GET
// purchases/{purchase}), así que 'pdf' y 'excel' deben ir antes del
// resource o Laravel los toma como un id de compra y nunca llegan al
// controlador (esto es lo que tenía rota la ruta purchases.pdf).
Route::get('purchases/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
Route::get('purchases/excel', [PurchaseController::class, 'excel'])->name('purchases.excel');
Route::resource('purchases', PurchaseController::class);

Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');




//auditoría
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');


//citas (agenda)
Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
Route::get('appointments/events', [AppointmentController::class, 'events'])->name('appointments.events');
Route::get('appointments/upcoming-alerts', [AppointmentController::class, 'upcomingAlerts'])->name('appointments.upcoming_alerts');
Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
Route::get('appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');


//PDF
Route::get('users/pdf', [UserController::class, 'pdf'])->name('users.pdf');


Route::get('/service-categories/pdf', [ServiceCategoryController::class, 'pdf'])->name('service_categories.pdf');
Route::get('/service-categories/excel', [ServiceCategoryController::class, 'excel'])->name('service_categories.excel');
Route::get('/services/pdf', [ServiceController::class, 'pdf'])->name('services.pdf');
Route::get('/services/excel', [ServiceController::class, 'excel'])->name('services.excel');

//ventas
Route::get('/persons/pdf', [PersonController::class, 'pdf'])->name('persons.pdf');
Route::get('/persons/excel', [PersonController::class, 'excel'])->name('persons.excel');
Route::get('/patients/pdf', [PatientController::class, 'pdf'])->name('patients.pdf');
Route::get('/patients/excel', [PatientController::class, 'excel'])->name('patients.excel');
Route::get('/specialities/pdf', [SpecialityController::class, 'pdf'])->name('specialities.pdf');
Route::get('/doctors/pdf', [DoctorController::class, 'pdf'])->name('doctors.pdf');
Route::get('/doctors/excel', [DoctorController::class, 'excel'])->name('doctors.excel');

Route::get('/sales/{salePrint}/print', [SaleController::class, 'print'])->name('sales.print');
Route::get('/histories/{id}/pdf', [HistoryController::class, 'pdf'])->name('histories.pdf');

//compras
Route::get('/product_categories/pdf', [ProductCategoryController::class, 'pdf'])->name('product_categories.pdf');
Route::get('/product_categories/excel', [ProductCategoryController::class, 'excel'])->name('product_categories.excel');
Route::get('/products/pdf', [ProductController::class, 'pdf'])->name('products.pdf');
Route::get('/products/excel', [ProductController::class, 'excel'])->name('products.excel');
Route::get('/suppliers/pdf', [SupplierController::class, 'pdf'])->name('suppliers.pdf');
Route::get('/suppliers/excel', [SupplierController::class, 'excel'])->name('suppliers.excel');
Route::get('/purchases/{purchasePrint}/print', [PurchaseController::class, 'print'])->name('purchases.print');


