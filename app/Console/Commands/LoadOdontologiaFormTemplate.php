<?php

namespace App\Console\Commands;

use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Speciality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga la plantilla de historial de Odontología, tal como está en el
 * documento en papel "Historia Médica General" + la hoja de
 * evolución/odontograma (numeración de piezas dentales con tratamiento y
 * precio) que usa el consultorio.
 *
 * Quedan afuera a propósito los datos personales del paciente que ya viven
 * en la ficha del paciente (módulo Pacientes): nombre, fecha y lugar de
 * nacimiento, sexo, dirección y teléfonos (Person ya tiene address/phone).
 * Se agregan sí Tipo de Sangre, Estatura, Peso y Profesión porque no
 * existen en ningún otro lado del sistema.
 *
 * El odontograma del papel (dibujo de las piezas dentales 11-85 con
 * tratamiento y precio por pieza) NO va como campo de esta plantilla: se
 * carga aparte, en el módulo Odontograma (clic sobre la pieza dental, con
 * seguimiento de Cobrado/Sin cobrar ligado a Ventas — ver ExpedienteController
 * y ToothTreatment). Al principio se había resuelto como una tabla
 * repetible "Plan de tratamiento" acá mismo mientras no existía ese módulo,
 * pero una vez armado el Odontograma visual quedaba duplicado (y peor: sin
 * el vínculo a Ventas/cobros que sí tiene el Odontograma), así que se sacó.
 *
 * Es segura de ejecutar sin --force: si la especialidad ya tiene una
 * plantilla activa con secciones, no hace nada (para no duplicar). Con
 * --force borra las secciones (y sus campos, en cascada) de la plantilla
 * actual y las vuelve a cargar desde cero; la plantilla en sí no se borra,
 * así que su id y versión no cambian.
 */
class LoadOdontologiaFormTemplate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'historial:plantilla-odontologia
        {--force : Borra las secciones y campos actuales de la plantilla de Odontología y los vuelve a cargar}';

    /**
     * @var string
     */
    protected $description = 'Carga (o recarga con --force) la plantilla de historial de Odontología';

    public function handle(): int
    {
        $speciality = Speciality::where('name', 'Odontología General')->first();

        if (! $speciality) {
            $this->error(
                'No se encontró la especialidad "Odontología General". Especialidades disponibles: '
                . Speciality::pluck('name')->implode(', ')
            );

            return self::FAILURE;
        }

        $template = $speciality->activeFormTemplate()->first();

        if ($template && $template->sections()->exists() && ! $this->option('force')) {
            $this->warn("La especialidad \"Odontología General\" ya tiene una plantilla activa con secciones cargadas (id {$template->id}).");
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
                    'name' => 'Historia médica general - Odontología',
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

        $this->info('Plantilla de Odontología cargada correctamente.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{title: string, grupo_visual?: string|null, fields: array<int, array{label: string, type: string, options?: array|null, required?: bool, help_text?: string|null}>}>
     */
    private function secciones(): array
    {
        $antecedentesFamiliaresOpciones = [
            'Buena salud', 'Enfermedades cardiacas', 'Diabetes', 'Enfermedades reumáticas',
            'Enfermedades Hemorrágicas', 'Tumores', 'Cáncer',
        ];

        return [
            [
                'title' => 'Datos complementarios',
                'fields' => [
                    // "Motivo de consulta" no va acá: ya está el campo
                    // "Descripción" al registrar cualquier consulta (arriba,
                    // fijo para todas las especialidades), que cubre lo
                    // mismo. Repetirlo acá solo duplicaba la pregunta (mismo
                    // criterio ya aplicado en Medicina Ortomolecular).
                    ['label' => 'Tipo de Sangre', 'type' => 'texto'],
                    ['label' => 'Estatura', 'type' => 'numero'],
                    ['label' => 'Peso', 'type' => 'numero'],
                    ['label' => 'Profesión', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Historia Médica General',
                'fields' => [
                    ['label' => '¿Cuáles son los síntomas o enfermedades que usted tiene o tuvo?', 'type' => 'seleccion_multiple', 'options' => [
                        'Enfermedades cardiacas', 'Alteración de presión arterial', 'Diabetes', 'Bronquitis',
                        'Sinusitis', 'Asma', 'Tuberculosis', 'Desmayos o convulsiones', 'Artritis', 'Hepatitis',
                        'Problemas de Coagulación', 'Anemia', 'Gastritis', 'Enfermedades Venéreas', 'VIH (SIDA)',
                    ]],
                    ['label' => '¿Alguna otra que quiera informar?', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Tratamientos y hábitos',
                'fields' => [
                    ['label' => '¿Está siguiendo algún tratamiento médico?', 'type' => 'si_no'],
                    ['label' => '¿Cuál tratamiento?', 'type' => 'texto'],
                    ['label' => '¿Toma algún medicamento?', 'type' => 'si_no'],
                    ['label' => '¿Cuál medicamento?', 'type' => 'texto'],
                    ['label' => '¿Tiene alergia a algún antibiótico?', 'type' => 'si_no'],
                    ['label' => '¿Cuál antibiótico?', 'type' => 'texto'],
                    ['label' => '¿Tiene alergia a la anestesia local?', 'type' => 'si_no'],
                    ['label' => '¿Ingiere bebidas alcohólicas?', 'type' => 'si_no'],
                    ['label' => '¿Fuma?', 'type' => 'si_no'],
                ],
            ],
            [
                'title' => 'Mujeres (si aplica)',
                'fields' => [
                    ['label' => '¿Está embarazada?', 'type' => 'si_no'],
                    ['label' => 'Tiempo de gestación', 'type' => 'texto'],
                    ['label' => '¿Está dando de lactar?', 'type' => 'si_no'],
                    ['label' => '¿Toma anticonceptivos?', 'type' => 'si_no'],
                ],
            ],
            [
                'title' => 'Antecedentes Familiares',
                'fields' => array_map(
                    fn (string $familiar) => [
                        'label' => $familiar,
                        'type' => 'seleccion_multiple',
                        'options' => $antecedentesFamiliaresOpciones,
                    ],
                    ['Papá', 'Mamá', 'Hermanos', 'Abuelos paternos', 'Abuelos maternos', 'Hijos']
                ),
            ],
            [
                'title' => 'Higiene Bucal',
                'fields' => [
                    ['label' => 'Frecuencia diaria de cepillado', 'type' => 'seleccion_unica', 'options' => ['1', '2', '3', '4']],
                    ['label' => 'Tipo de cepillo', 'type' => 'seleccion_unica', 'options' => ['Blando', 'Medio', 'Duro']],
                    ['label' => '¿Cada cuánto cambia de cepillo?', 'type' => 'texto'],
                    ['label' => '¿Utiliza otro elemento para su higiene bucal?', 'type' => 'si_no'],
                    ['label' => 'Cuál elemento', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Hábitos alimenticios',
                'fields' => [
                    ['label' => 'Ingesta de azúcares', 'type' => 'seleccion_multiple', 'options' => [
                        'Dulces', 'Chicles', 'Sodas', 'Miel', 'Pastillas', 'Fruta',
                    ]],
                ],
            ],
            // La sección "Plan de tratamiento" (tabla repetible de pieza
            // dental/diagnóstico/precio/realizado + un campo "Total") se
            // sacó de acá: quedaba duplicada con el módulo Odontograma, que
            // ya hace lo mismo con un selector visual de piezas y además
            // liga cada tratamiento a Ventas (Cobrado/Sin cobrar) — cosa que
            // esta tabla no podía hacer. Ver ExpedienteController y el
            // modelo ToothTreatment.
        ];
    }
}
