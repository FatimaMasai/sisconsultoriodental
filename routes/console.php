<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recordatorio de citas por WhatsApp. Para que esto realmente se ejecute
// hace falta tener corriendo "php artisan schedule:work" (en desarrollo)
// o un cron real llamando a "php artisan schedule:run" cada minuto (en
// producción) — ver la guía de configuración para más detalle.
Schedule::command('appointments:send-reminders')->hourly();
