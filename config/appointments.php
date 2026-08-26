<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recordatorio de citas
    |--------------------------------------------------------------------------
    |
    | Cuántas horas antes de la cita se manda el recordatorio por WhatsApp.
    | El comando de recordatorios corre cada hora (ver routes/console.php),
    | así que no hace falta más precisión que esa.
    |
    */
    'reminder_hours_before' => env('APPOINTMENT_REMINDER_HOURS_BEFORE', 24),

];
