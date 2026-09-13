<?php

namespace App\Console\Commands;

use App\Models\Consulta;
use App\Models\ConsultaNote;
use App\Models\ConsultaPhoto;
use App\Models\Expediente;
use App\Models\History;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Migra los historiales médicos actuales (tabla `histories`, con un
 * registro por cada servicio vendido) hacia la nueva estructura de
 * Expedientes por especialidad + Consultas.
 *
 * Agrupa en una sola Consulta los historiales que nacieron de la misma
 * venta: se reconocen porque comparten paciente, doctor y el mismo segundo
 * exacto de creación (se crearon uno tras otro, dentro del mismo request).
 * Los historiales cargados a mano desde "Nuevo Historial" quedan cada uno
 * como su propia Consulta, tal como estaban.
 *
 * Es segura de ejecutar sin --force: si detecta que ya se migró antes, no
 * hace nada para evitar duplicar datos. Todo corre dentro de una sola
 * transacción: si algo falla, no queda nada a medias.
 */
class MigrateHistoriesToExpedientes extends Command
{
    /**
     * @var string
     */
    protected $signature = 'historial:migrar-expedientes
        {--force : Vuelve a migrar desde cero, borrando antes los expedientes y consultas existentes}';

    /**
     * @var string
     */
    protected $description = 'Convierte los historiales médicos actuales en Expedientes por especialidad y Consultas';

    public function handle(): int
    {
        $yaMigrado = History::whereNotNull('consulta_id')->exists();

        if ($yaMigrado && ! $this->option('force')) {
            $this->warn('Ya parece que los historiales fueron migrados antes (hay historiales con consulta_id asignado).');
            $this->warn('Si quieres volver a hacerlo desde cero, corre este comando con --force (esto borra los expedientes y consultas actuales, no los historiales originales).');

            return self::FAILURE;
        }

        if ($this->option('force')) {
            $this->info('Borrando expedientes y consultas existentes...');
            ConsultaPhoto::query()->delete();
            ConsultaNote::query()->delete();
            Consulta::query()->delete();
            Expediente::query()->delete();
            History::query()->update(['consulta_id' => null]);
        }

        $totalExpedientes = 0;
        $totalConsultas = 0;
        $totalNotas = 0;
        $totalFotos = 0;
        $omitidos = 0;

        DB::transaction(function () use (&$totalExpedientes, &$totalConsultas, &$totalNotas, &$totalFotos, &$omitidos) {
            $histories = History::with(['doctor', 'notes', 'photos'])
                ->orderBy('patient_id')
                ->orderBy('doctor_id')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $expedientesCache = [];
            $consultaGroups = [];

            foreach ($histories as $history) {
                $doctor = $history->doctor;

                if (! $doctor || ! $doctor->speciality_id) {
                    $this->warn("Historial #{$history->id}: el doctor no tiene especialidad asignada, se omite.");
                    $omitidos++;
                    continue;
                }

                $specialityId = $doctor->speciality_id;
                $expedienteKey = $history->patient_id . '-' . $specialityId;

                if (! isset($expedientesCache[$expedienteKey])) {
                    $expediente = Expediente::firstOrCreate(
                        ['patient_id' => $history->patient_id, 'speciality_id' => $specialityId],
                        ['status' => true]
                    );

                    if ($expediente->wasRecentlyCreated) {
                        $totalExpedientes++;
                    }

                    $expedientesCache[$expedienteKey] = $expediente;
                }

                $expediente = $expedientesCache[$expedienteKey];

                // Los historiales que nacieron de la misma venta se crearon en
                // el mismo request, con el mismo paciente, doctor y segundo
                // exacto: se agrupan en una sola Consulta en vez de quedar
                // repetidos uno por cada servicio.
                $groupKey = $expediente->id . '-' . $history->doctor_id . '-' . $history->created_at->format('Y-m-d H:i:s');

                if (! isset($consultaGroups[$groupKey])) {
                    $consulta = Consulta::create([
                        'expediente_id' => $expediente->id,
                        'doctor_id' => $history->doctor_id,
                        'description' => $history->description,
                        'date' => $history->date,
                    ]);

                    $consultaGroups[$groupKey] = $consulta;
                    $totalConsultas++;
                } else {
                    $consulta = $consultaGroups[$groupKey];
                }

                $history->update(['consulta_id' => $consulta->id]);

                foreach ($history->notes as $note) {
                    ConsultaNote::create([
                        'consulta_id' => $consulta->id,
                        'note' => $note->note,
                    ]);
                    $totalNotas++;
                }

                foreach ($history->photos as $photo) {
                    ConsultaPhoto::create([
                        'consulta_id' => $consulta->id,
                        'type' => $photo->type,
                        'path' => $photo->path,
                    ]);
                    $totalFotos++;
                }
            }
        });

        $this->newLine();
        $this->info('Migración completada:');
        $this->table(
            ['Expedientes creados', 'Consultas creadas', 'Notas copiadas', 'Fotos copiadas', 'Historiales omitidos'],
            [[$totalExpedientes, $totalConsultas, $totalNotas, $totalFotos, $omitidos]]
        );

        return self::SUCCESS;
    }
}
