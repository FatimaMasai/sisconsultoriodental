<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicSetting;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;

class GoogleCalendarController extends Controller
{
    public function __construct(protected GoogleCalendarService $googleCalendar)
    {
        $this->middleware('can:admin.settings.google');
    }

    public function index()
    {
        $setting = ClinicSetting::instance();

        return view('admin.settings.google-calendar', [
            'setting' => $setting,
            'packageInstalled' => $this->googleCalendar->isPackageInstalled(),
            'configured' => $this->googleCalendar->isConfigured(),
            'connected' => $this->googleCalendar->isConnected(),
        ]);
    }

    public function connect()
    {
        if (! $this->googleCalendar->isConfigured()) {
            return redirect()->route('admin.settings.google.index')
                ->with('info', 'Todavía falta instalar el paquete de Google o completar las credenciales en el archivo .env.');
        }

        return redirect()->away($this->googleCalendar->getAuthUrl());
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()->route('admin.settings.google.index')
                ->with('info', 'Cancelaste la conexión con Google Calendar.');
        }

        if (! $request->filled('code')) {
            return redirect()->route('admin.settings.google.index')
                ->with('info', 'Google no devolvió un código de autorización válido.');
        }

        try {
            $this->googleCalendar->handleCallback($request->query('code'));

            session()->flash('swal', [
                'title' => 'Google Calendar conectado',
                'text' => 'Las citas se van a sincronizar automáticamente de ahora en más.',
                'icon' => 'success',
            ]);
        } catch (\Throwable $e) {
            session()->flash('swal', [
                'title' => 'No se pudo conectar',
                'text' => $e->getMessage(),
                'icon' => 'error',
            ]);
        }

        return redirect()->route('admin.settings.google.index');
    }

    public function disconnect()
    {
        $this->googleCalendar->disconnect();

        return redirect()->route('admin.settings.google.index')
            ->with('info', 'Se desconectó Google Calendar. Las citas nuevas ya no se van a sincronizar.');
    }
}
