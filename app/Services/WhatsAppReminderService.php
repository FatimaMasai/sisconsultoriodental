<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

/**
 * Manda recordatorios de citas por WhatsApp usando la API de Twilio.
 *
 * Si el paquete twilio/sdk no está instalado o faltan las credenciales
 * en .env, isConfigured() devuelve false y el comando que manda los
 * recordatorios simplemente no hace nada (no rompe la app).
 */
class WhatsAppReminderService
{
    public function isPackageInstalled(): bool
    {
        return class_exists(\Twilio\Rest\Client::class);
    }

    public function isConfigured(): bool
    {
        return $this->isPackageInstalled()
            && filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'))
            && filled(config('services.twilio.whatsapp_from'));
    }

    /**
     * Manda el recordatorio de una cita al celular del paciente.
     * Devuelve true si se pudo mandar, false si falló (queda en el log el motivo).
     */
    public function sendAppointmentReminder(Appointment $appointment): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $appointment->loadMissing(['patient.person', 'doctor.person']);

        $phone = $this->normalizePhone($appointment->patient->person->phone);

        if (! $phone) {
            Log::warning("No se pudo mandar recordatorio de la cita #{$appointment->id}: el paciente no tiene un teléfono válido.");

            return false;
        }

        $patientFirstName = $appointment->patient->person->name;
        $doctorName = trim($appointment->doctor->person->name . ' ' . $appointment->doctor->person->last_name_father);

        $message = "Hola {$patientFirstName}, te recordamos tu cita en Mi Consulta el "
            . $appointment->starts_at->translatedFormat('d/m/Y \a \l\a\s H:i')
            . " con {$doctorName}"
            . ($appointment->service ? " para {$appointment->service->name}" : '')
            . '. Si no podés asistir, avisanos para reagendar.';

        try {
            $client = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $client->messages->create(
                'whatsapp:' . $phone,
                [
                    'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
                    'body' => $message,
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning("No se pudo mandar el recordatorio por WhatsApp de la cita #{$appointment->id}: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Normaliza el teléfono guardado (que puede venir sin código de país)
     * a formato E.164 (+591XXXXXXXX) para la API de WhatsApp.
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($digits, '+')) {
            return $digits;
        }

        // Bolivia: números de celular tienen 8 dígitos.
        $countryCode = config('services.twilio.default_country_code', '+591');

        return $countryCode . ltrim($digits, '0');
    }
}
