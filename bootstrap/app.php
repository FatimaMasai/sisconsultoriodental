<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        //nueva ruta para admin
        then: function()
        {
            Route::middleware('web', 'auth')
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
        }


    )
    ->withMiddleware(function (Middleware $middleware) {
        // El webhook de VeriPagos lo llama su servidor, no un navegador con
        // sesión: no puede enviar token CSRF, así que queda excluido. Se
        // protege con Basic Auth propio dentro de VeriPagosWebhookController.
        $middleware->validateCsrfTokens(except: [
            'webhooks/veripagos',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Evita que el usuario vea la pantalla cruda de Laravel "419 | PAGE
        // EXPIRED" (pasa típicamente después de reiniciar la base de datos
        // con migrate:fresh mientras había una pestaña vieja abierta, o
        // simplemente cuando la sesión venció por inactividad). En vez de
        // eso, lo regresamos a la página anterior (normalmente el login)
        // con un mensaje amigable, sin reenviar la contraseña escrita.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('info', 'Tu sesión expiró por inactividad. Por favor, intenta de nuevo.');
        });
    })->create();
