<?php

namespace App\Console\Commands;

use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Speciality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga la plantilla de historial de Medicina Estética, tal como está en el
 * documento "Historia clínica funcional" + "Hoja de evolución" que usa el
 * consultorio en papel.
 *
 * Quedan afuera a propósito los datos personales del paciente (nombre,
 * edad, sexo, estado civil, lugar y fecha de nacimiento, etc.): esos ya
 * viven en la ficha del paciente (módulo Pacientes) y cargarlos de nuevo
 * acá duplicaría información.
 *
 * La tabla de "Antecedentes Heredofamiliares" del papel (una fila por
 * familiar) se resuelve como 8 campos de texto fijos, uno por familiar, en
 * vez de una tabla repetible: cada familiar es siempre el mismo, no hace
 * falta agregar filas (mismo criterio que se usó para la plantilla de
 * Nutrición).
 *
 * Es segura de ejecutar sin --force: si la especialidad ya tiene una
 * plantilla activa con secciones, no hace nada (para no duplicar). Con
 * --force borra las secciones (y sus campos, en cascada) de la plantilla
 * actual y las vuelve a cargar desde cero; la plantilla en sí no se borra,
 * así que su id y versión no cambian.
 */
class LoadMedicinaEsteticaFormTemplate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'historial:plantilla-medicina-estetica
        {--force : Borra las secciones y campos actuales de la plantilla de Medicina Estética y los vuelve a cargar}';

    /**
     * @var string
     */
    protected $description = 'Carga (o recarga con --force) la plantilla de historial de Medicina Estética';

    public function handle(): int
    {
        $speciality = Speciality::where('name', 'Medicina Estética')->first();

        if (! $speciality) {
            $this->error(
                'No se encontró la especialidad "Medicina Estética". Especialidades disponibles: '
                . Speciality::pluck('name')->implode(', ')
            );

            return self::FAILURE;
        }

        $template = $speciality->activeFormTemplate()->first();

        if ($template && $template->sections()->exists() && ! $this->option('force')) {
            $this->warn("La especialidad \"Medicina Estética\" ya tiene una plantilla activa con secciones cargadas (id {$template->id}).");
            $this->warn('Si querés borrar lo que tiene y volver a cargar la plantilla completa, corré este comando con --force.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($speciality, &$template) {
            if ($template && $this->option('force')) {
                // Borra las secciones (y en cascada sus campos) pero deja la
                // plantilla en sí, para no cambiarle el id ni la versión.
                $template->sections()->delete();
            }

            if (! $template) {
                $template = FormTemplate::create([
                    'speciality_id' => $speciality->id,
                    'name' => 'Historial clínico funcional - Medicina Estética',
                    'version' => ($speciality->formTemplates()->max('version') ?? 0) + 1,
                    'status' => true,
                ]);
            }

            foreach ($this->secciones() as $ordenSeccion => $seccion) {
                $formSection = FormSection::create([
                    'form_template_id' => $template->id,
                    'title' => $seccion['title'],
                    'grupo_visual' => $seccion['grupo_visual'] ?? null,
                    'order' => $ordenSeccion + 1,
                ]);

                foreach ($seccion['fields'] as $ordenCampo => $campo) {
                    $formSection->fields()->create([
                        'label' => $campo['label'],
                        'type' => $campo['type'],
                        'options' => $campo['options'] ?? null,
                        'required' => $campo['required'] ?? false,
                        'help_text' => $campo['help_text'] ?? null,
                        'order' => $ordenCampo + 1,
                    ]);
                }
            }
        });

        $this->info('Plantilla de Medicina Estética cargada correctamente.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{title: string, grupo_visual?: string|null, fields: array<int, array{label: string, type: string, options?: array|null, required?: bool, help_text?: string|null}>}>
     */
    private function secciones(): array
    {
        $frecuencia = ['Ninguna', '2 a 3 veces por semana', '+5 días por semana'];

        return [
            [
                'title' => 'Anamnesis',
                'fields' => [
                    // "Motivo de consulta" no va acá: ya está el campo
                    // "Descripción" al registrar cualquier consulta (arriba,
                    // fijo para todas las especialidades), que cubre lo
                    // mismo. Repetirlo acá solo duplicaba la pregunta (mismo
                    // criterio ya aplicado en Medicina Ortomolecular).
                    ['label' => 'Enfermedad actual', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Antecedentes Heredofamiliares',
                'fields' => [
                    ['label' => 'Abuelo Paterno', 'type' => 'texto'],
                    ['label' => 'Abuela Paterna', 'type' => 'texto'],
                    ['label' => 'Abuelo Materno', 'type' => 'texto'],
                    ['label' => 'Abuela Materna', 'type' => 'texto'],
                    ['label' => 'Padre', 'type' => 'texto'],
                    ['label' => 'Madre', 'type' => 'texto'],
                    ['label' => 'Hermano(s)', 'type' => 'texto'],
                    ['label' => 'Hermana(s)', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Antecedentes Personales / Hábitos de vida',
                'fields' => [
                    ['label' => 'Alcohol', 'type' => 'si_no'],
                    ['label' => 'Alcohol - Frecuencia', 'type' => 'texto'],
                    ['label' => 'Tabaco', 'type' => 'si_no'],
                    ['label' => 'Tabaco - Frecuencia', 'type' => 'texto'],
                    ['label' => 'Drogas', 'type' => 'si_no'],
                    ['label' => 'Drogas - Frecuencia', 'type' => 'texto'],
                    ['label' => 'Tipo de alimentación', 'type' => 'texto'],
                    ['label' => 'Diuresis', 'type' => 'seleccion_unica', 'options' => $frecuencia],
                    ['label' => 'Catarsis', 'type' => 'seleccion_unica', 'options' => $frecuencia],
                    ['label' => 'Calidad de sueño', 'type' => 'seleccion_unica', 'options' => $frecuencia],
                    ['label' => 'Vida sexual', 'type' => 'seleccion_unica', 'options' => $frecuencia],
                    ['label' => 'Grado de estrés', 'type' => 'seleccion_unica', 'options' => $frecuencia],
                    ['label' => 'Actividad física', 'type' => 'seleccion_unica', 'options' => $frecuencia],
                ],
            ],
            [
                'title' => 'Otros antecedentes',
                'fields' => [
                    ['label' => 'Antecedentes Alérgicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes Traumatológicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes Quirúrgicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes Patológicos', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Antecedentes Ginecológicos-Obstétricos (si es mujer)',
                'fields' => [
                    ['label' => 'Gestas', 'type' => 'numero'],
                    ['label' => 'Partos', 'type' => 'numero'],
                    ['label' => 'Cesáreas', 'type' => 'numero'],
                    ['label' => 'Abortos', 'type' => 'numero'],
                    ['label' => 'FUM', 'type' => 'texto', 'help_text' => 'Fecha de última menstruación.'],
                    ['label' => 'Menarca', 'type' => 'texto'],
                    ['label' => 'Menopausia', 'type' => 'texto'],
                    ['label' => 'N° de parejas sexuales', 'type' => 'numero'],
                    ['label' => 'Anticonceptivos', 'type' => 'texto'],
                    ['label' => 'Cirugías Ginecológicas', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Medicación actual',
                'fields' => [
                    ['label' => 'Medicación y Suplementación actual', 'type' => 'texto', 'help_text' => 'Colocar nombre de los medicamentos y/o suplementos.'],
                ],
            ],
            [
                'title' => 'Examen Físico',
                'fields' => [
                    ['label' => 'FC', 'type' => 'texto', 'help_text' => 'Frecuencia cardíaca.'],
                    ['label' => 'PA', 'type' => 'texto', 'help_text' => 'Presión arterial.'],
                    ['label' => 'FR', 'type' => 'texto', 'help_text' => 'Frecuencia respiratoria.'],
                    ['label' => 'T', 'type' => 'texto', 'help_text' => 'Temperatura.'],
                    ['label' => 'Sat O2', 'type' => 'texto'],
                    ['label' => '¿Alguna otra que quiera informar?', 'type' => 'si_no'],
                    ['label' => '¿Está siguiendo algún tratamiento médico?', 'type' => 'si_no'],
                    ['label' => 'Cuál tratamiento', 'type' => 'texto'],
                    ['label' => 'Desmayos o convulsiones', 'type' => 'si_no'],
                    ['label' => '¿Es alérgico a la anestesia local?', 'type' => 'si_no'],
                    ['label' => '¿Fuma?', 'type' => 'si_no'],
                ],
            ],
            [
                'title' => 'Bioimpedancia',
                'fields' => [
                    ['label' => 'Estatura', 'type' => 'numero'],
                    ['label' => 'Peso', 'type' => 'numero'],
                    ['label' => 'IMC', 'type' => 'numero'],
                    ['label' => '% Grasa', 'type' => 'numero'],
                    ['label' => '% Músculo', 'type' => 'numero'],
                    ['label' => 'Grasa Visceral', 'type' => 'numero'],
                    ['label' => 'Edad Corporal', 'type' => 'numero'],
                ],
            ],
            [
                'title' => 'Medidas Antropométricas',
                'fields' => [
                    ['label' => 'Cuello', 'type' => 'numero'],
                    ['label' => 'Tórax', 'type' => 'numero'],
                    ['label' => 'Ab. Superior', 'type' => 'numero'],
                    ['label' => 'Ab. Medio', 'type' => 'numero'],
                    ['label' => 'Cintura', 'type' => 'numero'],
                    ['label' => 'Cadera', 'type' => 'numero'],
                    ['label' => 'Glúteos', 'type' => 'numero'],
                    ['label' => 'Brazo Izq.', 'type' => 'numero'],
                    ['label' => 'Brazo Der.', 'type' => 'numero'],
                    ['label' => 'Muñeca Izq.', 'type' => 'numero'],
                    ['label' => 'Muñeca Der.', 'type' => 'numero'],
                    ['label' => 'Pierna Izq.', 'type' => 'numero'],
                    ['label' => 'Pierna Der.', 'type' => 'numero'],
                    ['label' => 'Pantorrilla Izq.', 'type' => 'numero'],
                    ['label' => 'Pantorrilla Der.', 'type' => 'numero'],
                ],
            ],
            [
                'title' => 'Inspección General',
                'fields' => [
                    ['label' => 'Estado general', 'type' => 'seleccion_unica', 'options' => [
                        'Buen estado general', 'Regular estado general', 'Mal estado general',
                    ]],
                ],
            ],
            [
                'title' => 'Diagnóstico y Plan',
                'fields' => [
                    ['label' => 'Diagnósticos Presuntivos', 'type' => 'texto'],
                    ['label' => 'Sugerencia de Alimentación', 'type' => 'texto'],
                    ['label' => 'Plan Terapéutico', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Órdenes Médicas Solicitadas',
                'fields' => [
                    ['label' => 'Laboratorio', 'type' => 'texto'],
                    ['label' => 'Ecografía', 'type' => 'texto'],
                    ['label' => 'Rayos X', 'type' => 'texto'],
                    ['label' => 'Otros', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Procedimiento de esta visita',
                'fields' => [
                    ['label' => 'Procedimiento realizado', 'type' => 'texto'],
                    ['label' => 'Costo', 'type' => 'numero'],
                ],
            ],
        ];
    }
}
