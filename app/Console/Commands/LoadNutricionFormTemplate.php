<?php

namespace App\Console\Commands;

use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Speciality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga la plantilla de historial de Nutrición, tal como está en el
 * documento "Historia clínica nutricional" que usa el consultorio en papel.
 *
 * Quedan afuera a propósito los datos personales del paciente (nombre,
 * edad, dirección, teléfono, etc.): esos ya viven en la ficha del paciente
 * (módulo Pacientes) y cargarlos de nuevo acá duplicaría información.
 *
 * Es segura de ejecutar sin --force: si la especialidad ya tiene una
 * plantilla activa con secciones, no hace nada (para no duplicar). Con
 * --force borra las secciones (y sus campos, en cascada) de la plantilla
 * actual y las vuelve a cargar desde cero; la plantilla en sí no se borra,
 * así que su id y versión no cambian.
 */
class LoadNutricionFormTemplate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'historial:plantilla-nutricion
        {--force : Borra las secciones y campos actuales de la plantilla de Nutrición y los vuelve a cargar}';

    /**
     * @var string
     */
    protected $description = 'Carga (o recarga con --force) la plantilla de historial de Nutrición';

    public function handle(): int
    {
        $speciality = Speciality::where('name', 'Nutrición')->first();

        if (! $speciality) {
            $this->error(
                'No se encontró la especialidad "Nutrición". Especialidades disponibles: '
                . Speciality::pluck('name')->implode(', ')
            );

            return self::FAILURE;
        }

        $template = $speciality->activeFormTemplate()->first();

        if ($template && $template->sections()->exists() && ! $this->option('force')) {
            $this->warn("La especialidad \"Nutrición\" ya tiene una plantilla activa con secciones cargadas (id {$template->id}).");
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
                    'name' => 'Historial clínico nutricional',
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

        $this->info('Plantilla de Nutrición cargada correctamente.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{title: string, grupo_visual?: string|null, fields: array<int, array{label: string, type: string, options?: array|null, required?: bool, help_text?: string|null}>}>
     */
    private function secciones(): array
    {
        return [
            // La sección "Motivo de consulta" (con un único campo del mismo
            // nombre) se sacó de acá: ya está el campo "Descripción" al
            // registrar cualquier consulta (arriba, fijo para todas las
            // especialidades), que cubre lo mismo. Repetirlo acá solo
            // duplicaba la pregunta (mismo criterio ya aplicado en Medicina
            // Ortomolecular).
            [
                'title' => 'Antecedentes e indicadores clínicos',
                'fields' => [
                    ['label' => 'Problemas actuales', 'type' => 'seleccion_multiple', 'options' => [
                        'Diarrea', 'Estreñimiento', 'Úlcera', 'Náuseas', 'Gastritis', 'Dentadura', 'Colitis', 'Vómito', 'Otros',
                    ]],
                    ['label' => 'Padece alguna enfermedad', 'type' => 'texto'],
                    ['label' => 'Ha padecido alguna enfermedad', 'type' => 'texto'],
                    ['label' => 'Toma medicamentos', 'type' => 'texto'],
                    ['label' => 'Toma (laxantes, diuréticos, antiácidos, otros)', 'type' => 'seleccion_multiple', 'options' => [
                        'Laxantes', 'Diuréticos', 'Antiácidos', 'Otros',
                    ]],
                    ['label' => 'Antecedentes familiares', 'type' => 'seleccion_multiple', 'options' => [
                        'Obesidad', 'Diabetes', 'HTA', 'Cáncer', 'Hipercolesterolemia', 'Otros',
                    ]],
                    ['label' => 'Antecedentes médicos y quirúrgicos', 'type' => 'texto'],
                    ['label' => 'Antecedentes gineco-obstétricos', 'type' => 'texto', 'help_text' => 'Dejar en blanco si no aplica.'],
                ],
            ],
            [
                'title' => 'Aspectos personales y estilo de vida',
                'fields' => [
                    ['label' => 'Actividad física', 'type' => 'seleccion_unica', 'options' => [
                        'Muy ligera', 'Ligera', 'Moderada', 'Pesada', 'Fuerte',
                    ]],
                    ['label' => 'Deporte que practica', 'type' => 'texto'],
                    ['label' => 'Sustrato energético', 'type' => 'texto'],
                    ['label' => 'Duración de la sesión de entrenamiento', 'type' => 'texto'],
                    ['label' => 'Frecuencia de entrenamiento', 'type' => 'texto'],
                    ['label' => 'Antigüedad en el deporte', 'type' => 'texto'],
                    ['label' => 'Temporada deportiva actual', 'type' => 'texto'],
                    ['label' => 'Próximo evento deportivo', 'type' => 'texto'],
                    ['label' => 'Lugar del evento', 'type' => 'texto'],
                    ['label' => 'Consumo de alcohol', 'type' => 'texto'],
                    ['label' => 'Consumo de tabaco', 'type' => 'texto'],
                    ['label' => 'Consumo de café', 'type' => 'texto'],
                    ['label' => 'Exploración física (aspecto general)', 'type' => 'texto', 'help_text' => 'Cabello, ojos, piel, uñas, labios, encías, dientes, etc.'],
                ],
            ],
            [
                'title' => 'Indicadores bioquímicos',
                'fields' => [
                    ['label' => 'Datos bioquímicos relevantes', 'type' => 'texto'],
                    ['label' => '¿Se solicitaron análisis?', 'type' => 'si_no'],
                    ['label' => 'Cuáles análisis', 'type' => 'texto'],
                ],
            ],
            [
                'title' => 'Indicadores dietéticos',
                'fields' => [
                    ['label' => 'Cuántas comidas hace al día', 'type' => 'numero'],
                    ['label' => 'Quién prepara sus alimentos', 'type' => 'texto'],
                    ['label' => '¿Come entre comidas?', 'type' => 'texto'],
                    ['label' => '¿Modificó su alimentación en los últimos 6 meses?', 'type' => 'si_no'],
                    ['label' => 'Por qué modificó su alimentación', 'type' => 'texto'],
                    ['label' => 'Cómo modificó su alimentación', 'type' => 'texto'],
                    ['label' => 'Apetito', 'type' => 'seleccion_unica', 'options' => ['Bueno', 'Malo', 'Regular']],
                    ['label' => 'Alimentos preferidos', 'type' => 'texto'],
                    ['label' => 'Alimentos que no le agradan', 'type' => 'texto'],
                    ['label' => 'Alimentos que le causan malestar', 'type' => 'texto'],
                    ['label' => '¿Es alérgico o intolerante a algún alimento?', 'type' => 'texto'],
                    ['label' => '¿Toma algún suplemento o complemento?', 'type' => 'si_no'],
                    ['label' => 'Cuál suplemento', 'type' => 'texto'],
                    ['label' => 'Por qué toma el suplemento', 'type' => 'texto'],
                    ['label' => 'Su consumo varía cuando está', 'type' => 'seleccion_multiple', 'options' => ['Triste', 'Nervioso', 'Ansioso']],
                    ['label' => '¿Agrega sal a las comidas ya preparadas?', 'type' => 'si_no'],
                    ['label' => '¿Ha llevado alguna dieta especial?', 'type' => 'texto'],
                    ['label' => 'Cuántas dietas ha llevado', 'type' => 'numero'],
                    ['label' => 'Motivo de la dieta', 'type' => 'texto'],
                    ['label' => 'Qué tipo de dieta', 'type' => 'texto'],
                    ['label' => 'Hace cuánto tiempo', 'type' => 'texto'],
                    ['label' => 'Por cuánto tiempo la siguió', 'type' => 'texto'],
                    ['label' => '¿Obtuvo los resultados esperados?', 'type' => 'texto'],
                    ['label' => '¿Ha utilizado medicamentos para bajar de peso?', 'type' => 'si_no'],
                    ['label' => 'Cuáles medicamentos', 'type' => 'texto'],
                    ['label' => '¿Toma refresco?', 'type' => 'texto'],
                    ['label' => 'De qué tipo', 'type' => 'texto'],
                    ['label' => 'Con qué frecuencia toma refresco', 'type' => 'texto'],
                    ['label' => '¿Toma energizantes?', 'type' => 'texto'],
                    ['label' => 'Frecuencia de energizantes', 'type' => 'texto'],
                    ['label' => 'Vasos de agua al día', 'type' => 'numero'],
                    ['label' => 'Vasos de líquido al día', 'type' => 'numero'],
                ],
            ],
            [
                'title' => 'Recordatorio de 24 horas',
                'fields' => [
                    [
                        'label' => 'Comidas del día',
                        'type' => 'tabla_repetible',
                        'options' => ['Hora', 'Tiempo de comida', 'Preparación', 'Alimentos', 'Cantidad', 'Lugar'],
                        'help_text' => 'Agregar una fila por cada comida del día.',
                    ],
                ],
            ],
            [
                'title' => 'Frecuencia de consumo de alimentos',
                'fields' => array_map(
                    fn (string $alimento) => [
                        'label' => $alimento,
                        'type' => 'seleccion_unica',
                        'options' => ['Diario', 'Semanal', 'Quincenal', 'Mensual', 'Ocasional', 'Nunca'],
                    ],
                    [
                        'Leche', 'Pollo', 'Res', 'Cerdo', 'Pescado', 'Embutidos', 'Queso', 'Huevo', 'Hígado',
                        'Corazón', 'Riñón', 'Tripas', 'Arroz', 'Fideos', 'Papa', 'Maíz', 'Harinas', 'Verduras',
                        'Frutas', 'Frijoles', 'Lentejas', 'Haba', 'Soya', 'Garbanzo', 'Mantequilla', 'Manteca',
                        'Aderezos', 'Aceites vegetales', 'Azúcar', 'Mermelada', 'Miel', 'Caramelos',
                        'Chocolate en polvo', 'Gelatina', 'Helados',
                    ]
                ),
            ],
            [
                'title' => 'Indicadores antropométricos',
                'fields' => [
                    ['label' => 'Peso actual (kg)', 'type' => 'numero'],
                    ['label' => 'Peso habitual (kg)', 'type' => 'numero'],
                    ['label' => 'Talla (m)', 'type' => 'numero'],
                    ['label' => 'Cintura (cm)', 'type' => 'numero'],
                    ['label' => 'Cadera (cm)', 'type' => 'numero'],
                    ['label' => 'Muñeca (cm)', 'type' => 'numero'],
                    ['label' => 'Brazo (cm)', 'type' => 'numero'],
                    ['label' => 'Abdomen (cm)', 'type' => 'numero'],
                    ['label' => 'IMC', 'type' => 'numero'],
                    ['label' => 'Peso teórico', 'type' => 'numero'],
                    ['label' => '% Grasa (bioimpedancia)', 'type' => 'numero'],
                    ['label' => '% Grasa visceral', 'type' => 'numero'],
                    ['label' => '% Músculo', 'type' => 'numero'],
                    ['label' => 'Edad metabólica', 'type' => 'numero'],
                    ['label' => 'Kcal', 'type' => 'numero'],
                ],
            ],
        ];
    }
}
