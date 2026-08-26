<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppReminderService;
use Illuminate\Console\Command;

/**
 * Busca las citas que están por venir (dentro de la ventana configurada en
 * config('appointments.reminder_hours_before')) y todavía no tienen
 * recordatorio mandado, y les manda un WhatsApp al paciente.
 *
 * Se registra en el scheduler (routes/console.php) para correr cada hora.
 * Para que efectivamente se ejecute sola, el servidor necesita tener
 * corriendo "php artisan schedule:work" (desarrollo) o un cron real
 * apuntando a "php artisan schedule:run" cada minuto (producción).
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Manda por WhatsApp el recordatorio de las citas próximas que todavía no lo recibieron';

    public function handle(WhatsAppReminderService $whatsApp): int
    {
        if (! $whatsApp->isConfigured()) {
            $this->comment('WhatsApp/Twilio no está configurado todavía, no se manda ningún recordatorio.');

            return self::SUCCESS;
        }

        $hoursBefore = (int) config('appointments.reminder_hours_before', 24);

        $windowStart = now();
        $windowEnd = now()->addHours($hoursBefore);

        $appointments = Appointment::with(['patient.person', 'doctor.person', 'service'])
            ->whereIn('status', ['Programada', 'Confirmada'])
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [$windowStart, $windowEnd])
            ->get();

        if ($appointments->isEmpty()) {
            $this->comment('No hay citas próximas pendientes de recordatorio.');

            return self::SUCCESS;
        }

        $enviados = 0;

        foreach ($appointments as $appointment) {
            if ($whatsApp->sendAppointmentReminder($appointment)) {
                $appointment->update(['reminder_sent_at' => now()]);
                $enviados++;
            }
        }

        $this->info("Recordatorios mandados: {$enviados} de {$appointments->count()} citas próximas.");

        return self::SUCCESS;
    }
}
