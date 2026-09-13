<?php

use App\Http\Controllers\Admin\AppearanceSettingController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\ExpedienteController;
use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\Admin\GoogleCalendarController;
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
use App\Http\Controllers\Admin\VeriPagosController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function()
// {
//     return view('admin.dashboard');
// })->name('dasboard');

// can:admin.dashboard: antes esta ruta solo pedía estar logueado (sin
// ningún permiso), así que cualquier usuario recién registrado —sin rol
// todavía asignado por el admin— podía ver las cifras del panel (ventas,
// cuotas vencidas, etc.) con solo entrar a /admin/dashboard a mano. Ahora
// que el registro público está habilitado en el login, esto importa más:
// con el permiso, alguien sin rol asignado no puede entrar hasta que el
// admin le agregue uno (Admin/Doctor/Recepcionista/Compras ya lo tienen,
// ver RoleSeeder).
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard')->middleware('can:admin.dashboard');
Route::get('/dashboard2', [DashboardController::class, 'dashboard2'])->name('dashboard2')->middleware('can:admin.dashboard');
// Route::get('/', function() {
//     return redirect()->route('login');  // Redirige a la página de login
// });

//rutas de administrador
Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard')->middleware('can:admin.dashboard');


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

// Acceso por especialidad: autorizar/revocar que un doctor vea también el
// historial clínico de pacientes de otra especialidad.
Route::post('doctors/{doctor}/speciality-access', [DoctorController::class, 'grantSpecialityAccess'])->name('doctors.speciality_access.store');
Route::delete('doctors/{doctor}/speciality-access/{grant}', [DoctorController::class, 'revokeSpecialityAccess'])->name('doctors.speciality_access.destroy');

// Debe ir antes del resource: 'sales' tiene ruta 'show' (GET sales/{sale}),
// así que si "excel" fuera después, Laravel intentaría interpretar "excel"
// como un id de venta en vez de llegar al método excel().
Route::get('sales/excel', [SaleController::class, 'excel'])->name('sales.excel');
// Mismo motivo: debe ir antes del resource para que "pendientes-cobro" no se
// interprete como un id de venta.
Route::get('sales/pendientes-cobro', [SaleController::class, 'pendingCharges'])->name('sales.pending-charges');
Route::resource('sales', SaleController::class);

Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
Route::post('sales/{sale}/installments/{installment}/pay', [SaleController::class, 'payInstallment'])->name('sales.installments.pay');
Route::get('sales/{sale}/cuotas-pagadas/pdf', [SaleController::class, 'salePaidInstallmentsPdf'])->name('sales.paid_installments.pdf');

// Abonos libres de una venta a Crédito (reemplaza al plan de cuotas fijas para ventas nuevas).
Route::post('sales/{sale}/abonos', [SaleController::class, 'addAbono'])->name('sales.abonos.store');
Route::get('sales/{sale}/abonos/{payment}/print', [SaleController::class, 'printAbono'])->name('sales.abonos.print');

// Reporte de todas las cuotas pagadas (de cualquier venta a Crédito).
Route::get('cuotas-pagadas', [SaleController::class, 'paidInstallments'])->name('installments.paid');
Route::get('cuotas-pagadas/excel', [SaleController::class, 'paidInstallmentsExcel'])->name('installments.paid.excel');
Route::get('cuotas-pagadas/pdf', [SaleController::class, 'paidInstallmentsPdf'])->name('installments.paid.pdf');

// Cobro por QR (VeriPagos), usado desde Nueva Venta y desde el pago de cuotas.
Route::post('veripagos/qr', [VeriPagosController::class, 'generar'])->name('veripagos.qr.generar');
Route::post('veripagos/qr/{movimientoId}/estado', [VeriPagosController::class, 'estado'])->name('veripagos.qr.estado');




//historias y notas medicas (archivo: se deja intacto, ver admin.expedientes.* para lo nuevo)
Route::resource('histories', HistoryController::class);
Route::post('histories/{id}/add-note',[HistoryController::class, 'addNote'])->name('histories.addNote');
Route::post('histories/{id}/photos', [HistoryController::class, 'storePhoto'])->name('histories.photos.store');
Route::delete('histories/photos/{id}', [HistoryController::class, 'destroyPhoto'])->name('histories.photos.destroy');

// Expedientes por especialidad: historial clínico actual (reemplaza a
// "histories" en el uso diario). Un expediente agrupa las consultas de un
// paciente en una especialidad; ver ExpedienteController.
Route::get('expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
// 'create' debe ir antes de '{expediente}': si no, Laravel intentaría
// interpretar "create" como un id de expediente y nunca llegaría al form.
Route::get('expedientes/create', [ExpedienteController::class, 'create'])->name('expedientes.create');
Route::post('expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');
Route::get('expedientes/{expediente}', [ExpedienteController::class, 'show'])->name('expedientes.show');
Route::post('expedientes/{expediente}/consultas', [ExpedienteController::class, 'storeConsulta'])->name('expedientes.consultas.store');
Route::get('consultas/{consulta}/pdf', [ExpedienteController::class, 'pdfConsulta'])->name('consultas.pdf');
Route::get('consultas/{consulta}/edit', [ExpedienteController::class, 'editConsulta'])->name('consultas.edit');
Route::put('consultas/{consulta}', [ExpedienteController::class, 'updateConsulta'])->name('consultas.update');
Route::post('consultas/{consulta}/add-note', [ExpedienteController::class, 'addNote'])->name('consultas.addNote');
Route::post('consultas/{consulta}/photos', [ExpedienteController::class, 'storePhoto'])->name('consultas.photos.store');
Route::delete('consulta-photos/{photo}', [ExpedienteController::class, 'destroyPhoto'])->name('consultas.photos.destroy');
Route::get('expedientes/{expediente}/recetas', [ExpedienteController::class, 'recetasHistorial'])->name('expedientes.recetas.index');
Route::post('consultas/{consulta}/recetas', [ExpedienteController::class, 'storeReceta'])->name('consultas.recetas.store');
Route::put('recetas/{receta}', [ExpedienteController::class, 'updateReceta'])->name('recetas.update');
Route::delete('recetas/{receta}', [ExpedienteController::class, 'destroyReceta'])->name('recetas.destroy');
Route::get('recetas/{receta}/pdf', [ExpedienteController::class, 'pdfReceta'])->name('recetas.pdf');
Route::get('expedientes/{expediente}/odontograma', [ExpedienteController::class, 'odontograma'])->name('expedientes.odontograma.index');
Route::post('expedientes/{expediente}/tooth-treatments', [ExpedienteController::class, 'storeToothTreatment'])->name('expedientes.tooth-treatments.store');
Route::put('tooth-treatments/{toothTreatment}', [ExpedienteController::class, 'updateToothTreatment'])->name('tooth-treatments.update');
Route::delete('tooth-treatments/{toothTreatment}', [ExpedienteController::class, 'destroyToothTreatment'])->name('tooth-treatments.destroy');

// Constructor de formularios sin código: qué secciones y campos se llenan
// al registrar una consulta de cada especialidad. Ver FormTemplateController.
Route::get('specialities/{speciality}/plantilla', [FormTemplateController::class, 'edit'])->name('specialities.plantilla.edit');
Route::post('specialities/{speciality}/plantilla', [FormTemplateController::class, 'store'])->name('specialities.plantilla.store');

Route::post('form-templates/{formTemplate}/sections', [FormTemplateController::class, 'storeSection'])->name('form_templates.sections.store');
Route::put('form-sections/{formSection}', [FormTemplateController::class, 'updateSection'])->name('form_sections.update');
Route::delete('form-sections/{formSection}', [FormTemplateController::class, 'destroySection'])->name('form_sections.destroy');
Route::post('form-sections/{formSection}/move', [FormTemplateController::class, 'moveSection'])->name('form_sections.move');

Route::post('form-sections/{formSection}/fields', [FormTemplateController::class, 'storeField'])->name('form_sections.fields.store');
Route::put('form-fields/{formField}', [FormTemplateController::class, 'updateField'])->name('form_fields.update');
Route::delete('form-fields/{formField}', [FormTemplateController::class, 'destroyField'])->name('form_fields.destroy');
Route::post('form-fields/{formField}/move', [FormTemplateController::class, 'moveField'])->name('form_fields.move');


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


// Configuración del sistema: logo/color de marca y la sincronización con
// Google Calendar. OJO: admin.settings.google.* ya se usaba desde la vista
// admin/settings/google-calendar.blade.php, pero nunca había quedado
// registrado acá (por eso daba "Route not defined" al entrar). Quedan
// agrupados bajo /admin/settings/... para que el sidebar los muestre
// juntos en "Configuración".
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('appearance', [AppearanceSettingController::class, 'index'])->name('appearance.index');
    Route::post('appearance', [AppearanceSettingController::class, 'update'])->name('appearance.update');

    Route::get('google', [GoogleCalendarController::class, 'index'])->name('google.index');
    Route::get('google/connect', [GoogleCalendarController::class, 'connect'])->name('google.connect');
    Route::get('google/callback', [GoogleCalendarController::class, 'callback'])->name('google.callback');
    Route::post('google/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.disconnect');
});


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


