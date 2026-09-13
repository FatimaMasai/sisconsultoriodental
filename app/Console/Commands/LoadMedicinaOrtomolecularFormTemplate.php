<?php

namespace App\Console\Commands;

use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Speciality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga la plantilla "Historia Clínica Funcional" (el historial general de
 * Medicina Ortomolecular, distinto del test "Perfil Neurofuncional"), tal
 * como está en el documento en papel que usa la doctora.
 *
 * Quedan afuera a propósito los datos personales del paciente que ya viven
 * en la ficha del paciente (módulo Pacientes): nombre, fecha de nacimiento,
 * sexo, estado civil, dirección y teléfonos (lugar de nacimiento se deja
 * afuera, igual que en las demás plantillas).
 *
 * El papel trae, al final, dos tablas repetibles distintas: una "Hoja de
 * evolución" (fecha, procedimiento, signos vitales y observaciones de cada
 * control) y otra de procedimientos con costo (igual de idea al "Plan de
 * tratamiento" de Odontología).
 *
 * A diferencia de Odontología/Nutrición/Medicina Estética (que tienen un
 * único historial), esta especialidad convive con otra plantilla activa
 * ("Perfil Neurofuncional"): por eso acá también hay que indicar
 * --speciality="Nombre exacto", y el chequeo de "ya existe" busca por
 * nombre de plantilla, no por cualquier plantilla activa de la
 * especialidad, para no pisar la otra.
 *
 * Es segura de ejecutar sin --force (no duplica si ya hay secciones
 * cargadas para esta plantilla puntual). Con --force borra sus secciones y
 * las vuelve a cargar; no toca ninguna otra plantilla de la especialidad.
 */
class LoadMedicinaOrtomolecularFormTemplate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'historial:plantilla-medicina-ortomolecular
        {--speciality= : Nombre exacto de la especialidad a la que pertenece esta plantilla}
        {--force : Borra las secciones y campos actuales de esta plantilla y los vuelve a cargar}';

    /**
     * @var string
     */
    protected $description = 'Carga (o recarga con --force) la plantilla "Historia Clínica Funcional" de Medicina Ortomolecular';

    public function handle(): int
    {
        $nombreEspecialidad = $this->option('speciality');

        if (! $nombreEspecialidad) {
            $this->error('Indicá a qué especialidad pertenece esta plantilla con --speciality="Nombre exacto".');
            $this->line('Especialidades disponibles: ' . Speciality::pluck('name')->implode(', '));

            return self::FAILURE;
        }

        $speciality = Speciality::where('name', $nombreEspecialidad)->first();

        if (! $speciality) {
            $this->error("No se encontró la especialidad \"{$nombreEspecialidad}\".");
            $this->line('Especialidades disponibles: ' . Speciality::pluck('name')->implode(', '));

            return self::FAILURE;
        }

        // Se busca por nombre (no "la" plantilla activa de la especialidad):
        // esta especialidad puede convivir con otra plantilla activa
        // (Perfil Neurofuncional), y no queremos pisarla.
        $template = $speciality->formTemplates()->where('name', 'Historia Clínica Funcional')->first();

        if ($template && $template->sections()->exists() && ! $this->option('force')) {
            $this->warn("La especialidad \"{$nombreEspecialidad}\" ya tiene una plantilla \"Historia Clínica Funcional\" activa con secciones cargadas (id {$template->id}).");
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
                    'name' => 'Historia Clínica Funcional',
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

        $this->info('Plantilla "Historia Clínica Funcional" cargada correctamente.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{title: string, grupo_visual?: string|null, fields: array<int, array{label: string, type: string, options?: array|null, required?: bool, help_text?: string|null}>}>
     */
    private function secciones(): array
    {
        return [
            // "Datos complementarios" (Estado Civil) ya no va acá: se movió
            // a la ficha del paciente (Person::CIVIL_STATUSES), porque es un
            // dato de la persona y no algo específico de esta especialidad
            // (antes no aparecía si el paciente se atendía en otra
            // especialidad).
            [
                'title' => 'Anamnesis',
                'fields' => [
                    // "Motivo de consulta" no va acá: ya está el campo
                    // "Descripción" al registrar cualquier consulta (arriba,
                    // fijo para todas las especialidades), que cubre lo
                    // mismo. Repetirlo acá solo duplicaba la pregunta.
                    ['label' => 'Enfermedad actual', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Antecedentes Heredofamiliares',
                'fields' => array_map(
                    fn (string $familiar) => ['label' => $familiar, 'type' => 'texto'],
                    ['Abuelo paterno', 'Abuela paterna', 'Abuelo materno', 'Abuela materna', 'Padre', 'Madre', 'Hermano(s)', 'Hermana(s)']
                ),
            ],
            [
                'title' => 'Antecedentes Personales / Hábitos de vida',
                'fields' => [
                    ['label' => 'Alcohol', 'type' => 'si_no'],
                    ['label' => 'Frecuencia (alcohol)', 'type' => 'texto'],
                    ['label' => 'Tabaco', 'type' => 'si_no'],
                    ['label' => 'Frecuencia (tabaco)', 'type' => 'texto'],
                    ['label' => 'Drogas', 'type' => 'si_no'],
                    ['label' => 'Frecuencia (drogas)', 'type' => 'texto'],
                    ['label' => 'Tipo de alimentación', 'type' => 'texto'],
                    ['label' => '¿Cuál es la actividad física que realiza?', 'type' => 'seleccion_unica', 'options' => [
                        'Ninguna', '2 a 3 veces por semana', '+5 días por semana',
                    ]],
                    ['label' => 'Diuresis', 'type' => 'texto'],
                    ['label' => 'Catarsis', 'type' => 'texto'],
                    ['label' => 'Calidad de sueño', 'type' => 'texto'],
                    ['label' => 'Vida sexual', 'type' => 'texto'],
                    ['label' => 'Grado de estrés', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Antecedentes',
                'fields' => [
                    ['label' => 'Antecedentes Patológicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes Quirúrgicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes Traumatológicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes Alérgicos', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Antecedentes Ginecológicos - Obstétricos (si es mujer)',
                'fields' => [
                    ['label' => 'Menarca', 'type' => 'texto'],
                    ['label' => 'FUM', 'type' => 'texto'],
                    ['label' => 'Gestas', 'type' => 'numero'],
                    ['label' => 'Partos', 'type' => 'numero'],
                    ['label' => 'Cesáreas', 'type' => 'numero'],
                    ['label' => 'Abortos', 'type' => 'numero'],
                    ['label' => 'Menopausia', 'type' => 'texto'],
                    ['label' => 'N° de parejas sexuales', 'type' => 'numero'],
                    ['label' => 'Anticonceptivos', 'type' => 'texto'],
                    ['label' => 'Cirugías Ginecológicas', 'type' => 'texto'],
                    [
                        'label' => 'Medicación y Suplementación actual',
                        'type' => 'texto',
                        'help_text' => 'Colocar nombre de los medicamentos y/o suplementos.',
                    ],
                ],
            ],
            [
                'title' => 'Examen Físico',
                'fields' => [
                    ['label' => 'FC (Frecuencia cardíaca)', 'type' => 'numero'],
                    ['label' => 'PA (Presión arterial)', 'type' => 'texto', 'help_text' => 'Ej: 120/80'],
                    ['label' => 'FR (Frecuencia respiratoria)', 'type' => 'numero'],
                    ['label' => 'T (Temperatura)', 'type' => 'numero'],
                    ['label' => 'SatO2', 'type' => 'numero'],
                    ['label' => '¿Está siguiendo algún tratamiento médico?', 'type' => 'si_no'],
                    ['label' => '¿Cuál tratamiento?', 'type' => 'texto'],
                    ['label' => '¿Tiene alergia a algún antibiótico?', 'type' => 'si_no'],
                    ['label' => '¿Cuál antibiótico?', 'type' => 'texto'],
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
                    ['label' => 'RM', 'type' => 'numero'],
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
                    ['label' => 'Observaciones', 'type' => 'texto'],
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
                'title' => 'Hoja de Evolución',
                'fields' => [
                    [
                        'label' => 'Evolución',
                        'type' => 'tabla_repetible',
                        'options' => [
                            ['label' => 'Fecha', 'type' => 'fecha'],
                            ['label' => 'Procedimiento realizado', 'type' => 'texto'],
                            ['label' => 'FC', 'type' => 'numero'],
                            ['label' => 'PA', 'type' => 'texto'],
                            ['label' => 'FR', 'type' => 'numero'],
                            ['label' => 'T', 'type' => 'numero'],
                            ['label' => 'Observaciones', 'type' => 'texto'],
                        ],
                        'help_text' => 'Agregar una fila por cada control/visita de seguimiento.',
                    ],
                ],
            ],
            [
                'title' => 'Procedimientos y Costos',
                'fields' => [
                    [
                        'label' => 'Procedimientos',
                        'type' => 'tabla_repetible',
                        'options' => [
                            ['label' => 'Fecha', 'type' => 'fecha'],
                            ['label' => 'Procedimiento', 'type' => 'texto'],
                            ['label' => 'Costo', 'type' => 'numero'],
                            ['label' => 'Observaciones', 'type' => 'texto'],
                        ],
                        'help_text' => 'Agregar una fila por cada procedimiento realizado y su costo.',
                    ],
                    [
                        'label' => 'Total',
                        'type' => 'numero',
                        // Se autocompleta solo sumando la columna "Costo" de la
                        // tabla de arriba (ver JS en show.blade.php /
                        // consulta_edit.blade.php: recalcularTotalCostos). Sigue
                        // editable por si hace falta corregirlo a mano.
                        'help_text' => 'Se calcula solo sumando el costo de todas las filas de arriba; se puede corregir a mano si hace falta.',
                    ],
                ],
            ],
        ];
    }
}
