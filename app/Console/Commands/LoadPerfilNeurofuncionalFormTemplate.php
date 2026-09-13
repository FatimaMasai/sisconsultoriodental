<?php

namespace App\Console\Commands;

use App\Models\FormSection;
use App\Models\FormTemplate;
use App\Models\Speciality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga la plantilla de historial "Perfil Neurofuncional" (cuestionario de
 * orientación sobre neurotransmisores: GABA, Dopamina, Acetilcolina y
 * Serotonina), tal como está en el documento en papel que usa la doctora.
 *
 * Cada afirmación del papel (Verdadero/Falso) se carga como un campo
 * "Sí/No": Sí = la afirmación es verdadera para el paciente. Cada bloque de
 * afirmaciones (ej. "GABA — Memoria y Atención") es su propia sección, con
 * un campo "Subtotal" al final para que el profesional anote el conteo. Las
 * 4 secciones de un mismo neurotransmisor comparten el mismo
 * `grupo_visual`, para poder agruparlas más adelante al sumar el resultado
 * final, aunque en la vista aparezcan separadas, igual que en el papel.
 *
 * No lleva datos que ya viven en la ficha del paciente (nombre, edad,
 * fecha). Se agrega sí "Estatura" porque no existe en otro lado del
 * sistema.
 *
 * A diferencia de las otras plantillas, acá la especialidad NO tiene un
 * nombre fijo en el código: hay que indicarla con
 * --speciality="Nombre exacto", porque este test puede aplicarse dentro de
 * más de una especialidad (por ejemplo, dentro de Nutrición) y no
 * queremos adivinar mal a cuál asignarlo. Si no se indica, o el nombre no
 * existe, el comando lista las especialidades disponibles para volver a
 * correrlo con el nombre correcto.
 *
 * Es segura de ejecutar sin --force: si la especialidad ya tiene una
 * plantilla activa con secciones, no hace nada (para no duplicar). Con
 * --force borra las secciones (y sus campos, en cascada) de la plantilla
 * actual y las vuelve a cargar desde cero; la plantilla en sí no se borra,
 * así que su id y versión no cambian.
 */
class LoadPerfilNeurofuncionalFormTemplate extends Command
{
    /**
     * @var string
     */
    protected $signature = 'historial:plantilla-perfil-neurofuncional
        {--speciality= : Nombre exacto de la especialidad a la que pertenece esta plantilla}
        {--force : Borra las secciones y campos actuales de la plantilla y los vuelve a cargar}';

    /**
     * @var string
     */
    protected $description = 'Carga (o recarga con --force) la plantilla de historial "Perfil Neurofuncional"';

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
        // una especialidad puede tener más de una plantilla activa a la vez
        // (ej. Medicina Ortomolecular también puede tener una "Historia
        // Clínica Funcional" aparte), y no queremos pisar esa otra.
        $template = $speciality->formTemplates()->where('name', 'Perfil Neurofuncional')->first();

        if ($template && $template->sections()->exists() && ! $this->option('force')) {
            $this->warn("La especialidad \"{$nombreEspecialidad}\" ya tiene una plantilla \"Perfil Neurofuncional\" activa con secciones cargadas (id {$template->id}).");
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
                    'name' => 'Perfil Neurofuncional',
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

        $this->info('Plantilla de Perfil Neurofuncional cargada correctamente.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{title: string, grupo_visual?: string|null, fields: array<int, array{label: string, type: string, options?: array|null, required?: bool, help_text?: string|null}>}>
     */
    private function secciones(): array
    {
        $secciones = [
            [
                'title' => 'Datos complementarios',
                'fields' => [
                    ['label' => 'Estatura', 'type' => 'numero'],
                ],
            ],
        ];

        // Cada neurotransmisor trae sus 4 bloques (Memoria y Atención,
        // Físico, Personalidad, Carácter) con sus afirmaciones, tal como
        // están numeradas en el papel. Algunas afirmaciones se repiten entre
        // categorías (ej. "Carácter" de GABA y de Acetilcolina comparten
        // varias) porque así está armado el cuestionario original.
        $categorias = [
            'GABA' => [
                'Memoria y Atención' => [
                    'Se me hace difícil concentrarme porque soy nervioso.',
                    'No puedo recordar números telefónicos.',
                    'Tengo problemas para encontrar la "palabra correcta".',
                    'Se me dificulta recordar las cosas que son importantes.',
                    'Sé que soy inteligente, pero me es difícil demostrarlo a los demás.',
                    'Mi capacidad para concentrarme va y viene.',
                    'Cuando leo, tengo que regresarme al mismo párrafo algunas veces para captar la información.',
                    'Soy un pensador rápido, pero no siempre puedo explicar lo que quiero decir.',
                ],
                'Físico' => [
                    'Me siento nervioso.',
                    'Algunas veces tiemblo.',
                    'Tengo frecuentes dolores de espalda y/o cabeza.',
                    'Tiendo a tener dificultad para respirar.',
                    'Tiendo a tener palpitaciones cardíacas aceleradas.',
                    'Tiendo a tener las manos frías.',
                    'Algunas veces sudo demasiado.',
                    'En ocasiones me mareo.',
                    'Usualmente tengo tensión muscular.',
                    'Tiendo a tener mariposas en mi estómago.',
                    'Siento antojo por alimentos agridulces.',
                    'Usualmente soy nervioso.',
                    'Me gusta el yoga porque me ayuda a relajarme.',
                    'Comúnmente me siento fatigado, incluso aunque haya dormido bien durante la noche.',
                    'Como en exceso.',
                ],
                'Personalidad' => [
                    'Tengo cambios de estado de ánimo.',
                    'Me gusta hacer muchas cosas en un mismo momento, pero se me hace difícil decidir cuál haré primero.',
                    'Tiendo a hacer cosas sólo porque pienso que deben ser divertidas.',
                    'Cuando las cosas están aburridas, siempre intento poner algo de emoción.',
                    'Tiendo a ser inconstante, cambiando frecuentemente de estado de ánimo y pensamientos.',
                    'Tiendo a emocionarme en exceso por las cosas.',
                    'Mis impulsos tienden a meterme en muchos problemas.',
                    'Tiendo a ser teatral y a atraer la atención hacia mí mismo.',
                    'Habla con mi mente sin importar la reacción que puedan tener los demás.',
                    'Algunas veces tengo bloques de ira y después me siento terriblemente culpable.',
                    'En ocasiones digo mentiras para salir de los problemas.',
                    'Siempre tengo menos interés en el sexo que las personas promedio.',
                ],
                'Carácter' => [
                    'No paso las reglas.',
                    'He perdido muchos amigos.',
                    'No puedo conservar las relaciones románticas.',
                    'Considero arbitraria a la ley sin tener alguna razón.',
                    'Considero inútiles las reglas que suelo seguir.',
                ],
            ],
            'DOPAMINA' => [
                'Memoria y Atención' => [
                    'Tengo problemas para poner mucha atención y concentrarme.',
                    'Necesito cafeína para despertarme.',
                    'No puedo pensar lo suficientemente rápido.',
                    'No tengo un buen rango de atención.',
                    'Tengo problemas para terminar una tarea, incluso cuando me interesa.',
                    'Soy lento para aprender nuevas ideas.',
                ],
                'Físico' => [
                    'Tengo antojos de azúcar.',
                    'Ha disminuido mi libido.',
                    'Duermo demasiado.',
                    'Tengo un historial de alcohol o adicciones.',
                    'Recientemente me he sentido cansado sin razón aparente.',
                    'Algunas veces experimento cansancio total sin haberme ejercitado.',
                    'Siempre he tenido problemas para controlar mi peso.',
                    'Tengo poca motivación para las experiencias sexuales.',
                    'Tengo problemas para levantarme de la cama por las mañanas.',
                    'He tenido antojo de cocaína, anfetaminas o éxtasis.',
                ],
                'Personalidad' => [
                    'Sólo me siento bien cuando sigo a los demás.',
                    'La gente parece tomar ventaja de mí.',
                    'Me estoy sintiendo muy decaído o deprimido.',
                    'La gente me ha dicho que soy demasiado tranquilo.',
                    'Tengo pocas urgencias.',
                    'Dejo que la gente me critique.',
                    'Siempre veo que otros me guían.',
                ],
                'Carácter' => [
                    'He perdido mis capacidades de razonamiento.',
                    'No puedo tomar buenas decisiones.',
                ],
            ],
            'ACETILCOLINA' => [
                'Memoria y Atención' => [
                    'Me falta imaginación.',
                    'Tengo dificultad para recordar nombres cuando conozco a la gente.',
                    'He notado que mi capacidad de memoria está disminuyendo.',
                    'Otros me dicen que no tengo pensamientos románticos.',
                    'No puedo recordar los cumpleaños de mis amigos.',
                    'He perdido parte de mi creatividad.',
                ],
                'Físico' => [
                    'Tengo insomnio.',
                    'He perdido tono muscular.',
                    'Ya no hago ejercicio.',
                    'Tengo antojo de alimentos grasosos.',
                    'He probado los alucinógenos, el LSD u otras drogas ilícitas.',
                    'Siento como si mi cuerpo se estuviera separando.',
                    'No puedo respirar fácilmente.',
                ],
                'Personalidad' => [
                    'No me siento feliz muy seguido.',
                    'Me siento desesperado.',
                    'Evito que los demás me hagan daño al no decirles mucho sobre mí.',
                    'Se me hace más cómodo hacer las cosas solo que en un grupo grande.',
                    'Otras personas se enojan por las cosas que hago.',
                    'Me rindo fácilmente y tiendo a ser sumiso.',
                    'Raramente me siento apasionado por algo.',
                    'Me gusta la rutina.',
                ],
                'Carácter' => [
                    'No me preocupan las historias de los demás, sólo las mías.',
                    'No pongo atención a los sentimientos de las personas.',
                    'No me siento optimista.',
                    'Estoy obsesionado con mis deficiencias.',
                    'No paso las reglas.',
                    'He perdido muchos amigos.',
                    'No puedo conservar las relaciones románticas.',
                    'Considero arbitraria a la ley sin tener alguna razón.',
                    'Considero inútiles las reglas que suelo seguir.',
                ],
            ],
            'SEROTONINA' => [
                'Memoria y Atención' => [
                    'No soy muy perceptivo.',
                    'No puedo recordar las cosas que he visto en el pasado.',
                    'Tengo un lento tiempo de reacción.',
                    'Tengo poco sentido de la dirección.',
                ],
                'Físico' => [
                    'Tengo sudoraciones nocturnas.',
                    'Tengo insomnio.',
                    'Tiendo a dormir en muchas posiciones diferentes para sentirme cómodo.',
                    'Siempre despierto temprano por las mañanas.',
                    'No me puedo relajar.',
                    'Me despierto al menos 2 veces por las noches.',
                    'Es difícil para mí volverme a quedar dormido cuando me despierto.',
                    'Tengo antojo de sal.',
                    'Tengo menos energía para ejercitarme.',
                ],
                'Personalidad' => [
                    'Tengo ansiedad crónica.',
                    'Me irrito fácilmente.',
                    'Tengo pensamientos de autodestrucción.',
                    'He tenido pensamientos suicidas en mi vida.',
                    'Tiendo a mortificarme mucho por mis ideas.',
                    'Algunas veces soy tan cuadrado que me he vuelto inflexible.',
                    'Mi imaginación me domina.',
                    'El miedo me oprime.',
                ],
                'Carácter' => [
                    'No puedo dejar de pensar en el significado de la vida.',
                    'He deseado tomar riesgos por mucho tiempo.',
                    'La falta de significado en la vida es dolorosa para mí.',
                ],
            ],
        ];

        $esElPrimerCampo = true;

        foreach ($categorias as $categoria => $bloques) {
            foreach ($bloques as $subcategoria => $afirmaciones) {
                $fields = [];

                foreach ($afirmaciones as $afirmacion) {
                    $field = ['label' => $afirmacion, 'type' => 'si_no'];

                    if ($esElPrimerCampo) {
                        $field['help_text'] = 'Marque "Sí" si la afirmación es verdadera para el paciente, o "No" si es falsa.';
                        $esElPrimerCampo = false;
                    }

                    $fields[] = $field;
                }

                $fields[] = ['label' => "Subtotal ({$subcategoria})", 'type' => 'numero'];

                $secciones[] = [
                    'title' => "{$categoria} — {$subcategoria}",
                    'grupo_visual' => $categoria,
                    'fields' => $fields,
                ];
            }
        }

        $secciones[] = [
            'title' => 'Resultado final',
            'fields' => [
                ['label' => 'Total Dopamina', 'type' => 'numero'],
                ['label' => 'Total Acetilcolina', 'type' => 'numero'],
                ['label' => 'Total GABA', 'type' => 'numero'],
                ['label' => 'Total Serotonina', 'type' => 'numero'],
            ],
        ];

        $secciones[] = [
            'title' => 'Hábitos y observaciones',
            'fields' => [
                ['label' => '¿Consume tabaco?', 'type' => 'si_no'],
                ['label' => 'Frecuencia de tabaco', 'type' => 'texto'],
                ['label' => '¿Consume drogas?', 'type' => 'si_no'],
                ['label' => 'Frecuencia de drogas', 'type' => 'texto'],
                ['label' => 'Observaciones del profesional', 'type' => 'texto'],
            ],
        ];

        return $secciones;
    }
}
