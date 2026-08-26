<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use Illuminate\Support\Facades\Log;

/**
 * Sincroniza las citas con un único Google Calendar compartido de la
 * clínica (no uno por doctor).
 *
 * Este servicio está pensado para no romper nada si todavía no está
 * configurado: mientras no exista el paquete google/apiclient instalado,
 * o no haya credenciales en .env, o la clínica no haya conectado su
 * cuenta de Google, todos los métodos de sincronización simplemente no
 * hacen nada (y dejan un aviso en el log). El módulo de Citas funciona
 * igual sin Google Calendar conectado.
 */
class GoogleCalendarService
{
    private const SCOPE = 'https://www.googleapis.com/auth/calendar';

    public function isPackageInstalled(): bool
    {
        return class_exists(\Google\Client::class) && class_exists(\Google\Service\Calendar::class);
    }

    public function isConfigured(): bool
    {
        return $this->isPackageInstalled()
            && filled(config('services.google_calendar.client_id'))
            && filled(config('services.google_calendar.client_secret'))
            && filled(config('services.google_calendar.redirect_uri'));
    }

    public function isConnected(): bool
    {
        return ClinicSetting::instance()->isGoogleConnected();
    }

    /**
     * Arma la URL a la que hay que mandar al admin para que autorice el
     * acceso a su Google Calendar (pantalla de consentimiento de Google).
     */
    public function getAuthUrl(): string
    {
        $client = $this->baseClient();

        $client->setAccessType('offline');
        $client->setPrompt('consent'); // fuerza a que siempre devuelva refresh_token
        $client->addScope(self::SCOPE);

        return $client->createAuthUrl();
    }

    /**
     * Recibe el "code" que Google manda de vuelta después de que el admin
     * autoriza, lo cambia por tokens y los guarda.
     */
    public function handleCallback(string $code): void
    {
        $client = $this->baseClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException('Google Calendar no autorizó la conexión: ' . $token['error']);
        }

        $setting = ClinicSetting::instance();
        $setting->google_access_token = $token['access_token'];
        $setting->google_token_expires_at = now()->addSeconds($token['expires_in'] ?? 3600);

        if (! empty($token['refresh_token'])) {
            $setting->google_refresh_token = $token['refresh_token'];
        }

        // Guardamos el email de la cuenta conectada solo para mostrarlo en la UI.
        try {
            $client->setAccessToken($token);
            $oauth = new \Google\Service\Oauth2($client);
            $setting->google_account_email = $oauth->userinfo->get()->email;
        } catch (\Throwable $e) {
            Log::warning('No se pudo obtener el email de la cuenta de Google Calendar: ' . $e->getMessage());
        }

        $setting->save();
    }

    public function disconnect(): void
    {
        $setting = ClinicSetting::instance();
        $setting->update([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_account_email' => null,
        ]);
    }

    public function syncCreate(Appointment $appointment): void
    {
        $this->withClient(function ($service, ClinicSetting $setting) use ($appointment) {
            $event = $service->events->insert($setting->google_calendar_id, $this->buildEvent($appointment));
            $appointment->update(['google_event_id' => $event->getId()]);
        }, 'crear');
    }

    public function syncUpdate(Appointment $appointment): void
    {
        if (! $appointment->google_event_id) {
            $this->syncCreate($appointment);

            return;
        }

        $this->withClient(function ($service, ClinicSetting $setting) use ($appointment) {
            $service->events->update($setting->google_calendar_id, $appointment->google_event_id, $this->buildEvent($appointment));
        }, 'actualizar');
    }

    public function syncDelete(Appointment $appointment): void
    {
        if (! $appointment->google_event_id) {
            return;
        }

        $this->withClient(function ($service, ClinicSetting $setting) use ($appointment) {
            $service->events->delete($setting->google_calendar_id, $appointment->google_event_id);
        }, 'borrar');
    }

    /**
     * Corre el callback con un cliente de Google ya autenticado, refrescando
     * el token si hizo falta. Si algo no está listo (paquete no instalado,
     * no conectado, token vencido y sin refresh, etc.) no tira error hacia
     * arriba: solo lo deja anotado en el log para no romper el flujo normal
     * de citas.
     */
    private function withClient(callable $callback, string $accion): void
    {
        if (! $this->isConfigured() || ! $this->isConnected()) {
            return;
        }

        try {
            $setting = ClinicSetting::instance();
            $client = $this->authenticatedClient($setting);
            $service = new \Google\Service\Calendar($client);

            $callback($service, $setting);
        } catch (\Throwable $e) {
            Log::warning("No se pudo sincronizar la cita con Google Calendar ({$accion}): " . $e->getMessage());
        }
    }

    private function authenticatedClient(ClinicSetting $setting): \Google\Client
    {
        $client = $this->baseClient();

        $client->setAccessToken([
            'access_token' => $setting->google_access_token,
            'refresh_token' => $setting->google_refresh_token,
        ]);

        if ($client->isAccessTokenExpired()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($setting->google_refresh_token);
            $setting->google_access_token = $newToken['access_token'] ?? $setting->google_access_token;
            $setting->google_token_expires_at = now()->addSeconds($newToken['expires_in'] ?? 3600);
            $setting->save();

            $client->setAccessToken($setting->google_access_token);
        }

        return $client;
    }

    private function baseClient(): \Google\Client
    {
        $client = new \Google\Client();
        $client->setClientId(config('services.google_calendar.client_id'));
        $client->setClientSecret(config('services.google_calendar.client_secret'));
        $client->setRedirectUri(config('services.google_calendar.redirect_uri'));

        return $client;
    }

    private function buildEvent(Appointment $appointment): \Google\Service\Calendar\Event
    {
        $appointment->loadMissing(['patient.person', 'doctor.person', 'service']);

        $patientName = trim($appointment->patient->person->name . ' ' . $appointment->patient->person->last_name_father);
        $doctorName = trim($appointment->doctor->person->name . ' ' . $appointment->doctor->person->last_name_father);

        return new \Google\Service\Calendar\Event([
            'summary' => 'Cita: ' . $patientName,
            'description' => trim(
                'Doctor: ' . $doctorName
                . ($appointment->service ? "\nServicio: " . $appointment->service->name : '')
                . ($appointment->notes ? "\nNotas: " . $appointment->notes : '')
            ),
            'start' => ['dateTime' => $appointment->starts_at->toRfc3339String()],
            'end' => ['dateTime' => $appointment->ends_at->toRfc3339String()],
        ]);
    }
}
