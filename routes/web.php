<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\VeriPagosWebhookController;

// Esta instancia es para wellnesscentrointegral.com, que todavía no tiene
// web propia: la raíz del sitio manda directo al login en vez de mostrar
// la landing de marketing de MiConsulta.
Route::get('/', function () {
    return redirect()->route('login');
});

// Webhook público de VeriPagos (sin sesión, protegido con Basic Auth propio
// dentro del controlador). Excluido de CSRF en bootstrap/app.php.
Route::post('/webhooks/veripagos', [VeriPagosWebhookController::class, 'handle'])->name('webhooks.veripagos');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Route::get('/dashboard', function () {
    //     return view('admin.dashboard');
    // })->name('dashboard');
});

