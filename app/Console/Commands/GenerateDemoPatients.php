<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Genera pacientes de PRUEBA (datos inventados) para poder probar
 * listados, búsquedas, reportes y PDF sin tener que cargar pacientes
 * reales a mano. Pensado para un entorno de desarrollo/pruebas, no para
 * producción (el sistema ya se dejó limpio de datos de ejemplo para el
 * cliente — ver estado-del-proyecto.md).
 *
 * Cada paciente de prueba queda marcado de dos formas para poder
 * encontrarlo y borrarlo después sin tocar pacientes reales:
 *   - Carnet de identidad dentro de un rango reservado (90000000 a
 *     90999999) que no colisiona con carnets reales.
 *   - Campo "observation" del paciente con un texto fijo identificable.
 */
class GenerateDemoPatients extends Command
{
    private const CI_RANGE_START = 90000000;
    private const CI_RANGE_END = 90999999;

    private const MARCA_OBSERVACION = 'Paciente de prueba (generado con patients:demo, se puede borrar).';

    protected $signature = 'patients:demo
        {count=100 : Cuántos pacientes de prueba generar}
        {--delete : Borra los pacientes de prueba generados antes, en vez de crear nuevos}
        {--force : No pedir confirmación aunque el entorno sea de producción}';

    protected $description = 'Genera (o borra con --delete) pacientes de prueba con datos inventados, para probar el sistema sin tocar datos reales.';

    private const NOMBRES_FEMENINOS = [
        'María', 'Ana', 'Carla', 'Sofía', 'Patricia', 'Lucía', 'Gabriela', 'Daniela', 'Valentina', 'Camila',
        'Fernanda', 'Andrea', 'Paola', 'Rosa', 'Claudia', 'Verónica', 'Mónica', 'Silvia', 'Elena', 'Beatriz',
    ];

    private const NOMBRES_MASCULINOS = [
        'Juan', 'Luis', 'Carlos', 'José', 'Miguel', 'Roberto', 'Fernando', 'Ricardo', 'Andrés', 'Diego',
        'Jorge', 'Pedro', 'Raúl', 'Alberto', 'Óscar', 'Marco', 'Iván', 'Rubén', 'Sergio', 'Pablo',
    ];

    private const APELLIDOS = [
        'Quispe', 'Mamani', 'Condori', 'Choque', 'Flores', 'Gutiérrez', 'Vargas', 'Rojas', 'Fernández', 'Gonzales',
        'Aguilar', 'Torrez', 'Ramírez', 'Colque', 'Apaza', 'Cruz', 'Vega', 'Salazar', 'Paredes', 'Herrera',
        'Mendoza', 'Rivera', 'Guzmán', 'Castro', 'Ortiz',
    ];

    private const ZONAS = [
        'Miraflores', 'Sopocachi', 'San Pedro', 'Achumani', 'Calacoto', 'Obrajes', 'Villa Fátima',
        'Villa Copacabana', 'Alto Following', 'San Jorge', 'Cota Cota', 'Irpavi', 'Sopocachi Alto',
    ];

    private const CALLES = [
        'Av. Busch', 'Av. Arce', 'Av. Saavedra', 'Av. Ballivián', 'Calle Illimani', 'Calle Murillo',
        'Av. 6 de Agosto', 'Calle Landaeta', 'Av. Montenegro', 'Calle Colombia',
    ];

    public function handle(): int
    {
        if ($this->option('delete')) {
            return $this->borrarDemo();
        }

        if (app()->environment('production') && ! $this->option('force')) {
            $continuar = $this->confirm(
                'Este comando genera pacientes de PRUEBA (datos inventados) y el entorno actual es "production". ¿Continuar de todas formas?',
                false
            );

            if (! $continuar) {
                $this->warn('Cancelado.');

                return self::FAILURE;
            }
        }

        $count = max(1, (int) $this->argument('count'));

        $maxCi = (int) (Person::whereRaw('CAST(identity_card AS UNSIGNED) BETWEEN ? AND ?', [self::CI_RANGE_START, self::CI_RANGE_END])
            ->selectRaw('MAX(CAST(identity_card AS UNSIGNED)) as max_ci')
            ->value('max_ci'));

        $siguienteCi = max(self::CI_RANGE_START, $maxCi + 1);

        if ($siguienteCi + $count - 1 > self::CI_RANGE_END) {
            $this->error('No hay suficiente rango de carnets de prueba libres. Borrá los pacientes de prueba existentes primero con: php artisan patients:demo --delete');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $creados = 0;

        DB::transaction(function () use ($count, $siguienteCi, $bar, &$creados) {
            for ($i = 0; $i < $count; $i++) {
                $esMujer = (bool) random_int(0, 1);
                $nombre = $esMujer
                    ? self::NOMBRES_FEMENINOS[array_rand(self::NOMBRES_FEMENINOS)]
                    : self::NOMBRES_MASCULINOS[array_rand(self::NOMBRES_MASCULINOS)];

                $ci = $siguienteCi + $i;
                $edadAnios = random_int(18, 80);
                $fechaNacimiento = now()->subYears($edadAnios)->subDays(random_int(0, 365))->format('Y-m-d');
                $telefono = (random_int(0, 1) ? '6' : '7') . str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
                $direccion = self::CALLES[array_rand(self::CALLES)] . ' #' . random_int(100, 4999) . ', Zona ' . self::ZONAS[array_rand(self::ZONAS)];

                $person = Person::create([
                    'name' => $nombre,
                    'last_name_father' => self::APELLIDOS[array_rand(self::APELLIDOS)],
                    'last_name_mother' => self::APELLIDOS[array_rand(self::APELLIDOS)],
                    'identity_card' => (string) $ci,
                    'birth_date' => $fechaNacimiento,
                    'gender' => $esMujer ? 'Femenino' : 'Masculino',
                    'civil_status' => Person::CIVIL_STATUSES[array_rand(Person::CIVIL_STATUSES)],
                    'phone' => $telefono,
                    'email' => 'paciente.demo' . $ci . '@ejemplo.test',
                    'address' => $direccion,
                    'status' => 1,
                ]);

                Patient::create([
                    'allergy' => null,
                    'observation' => self::MARCA_OBSERVACION,
                    'recommended_by' => null,
                    'responsible_person' => null,
                    'medical_history' => null,
                    'status' => 1,
                    'person_id' => $person->id,
                ]);

                $creados++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Se crearon {$creados} pacientes de prueba (carnet desde {$siguienteCi} hasta " . ($siguienteCi + $count - 1) . ').');
        $this->line('Para borrarlos después: php artisan patients:demo --delete');

        return self::SUCCESS;
    }

    private function borrarDemo(): int
    {
        $personas = Person::whereRaw('CAST(identity_card AS UNSIGNED) BETWEEN ? AND ?', [self::CI_RANGE_START, self::CI_RANGE_END])->get();

        if ($personas->isEmpty()) {
            $this->info('No hay pacientes de prueba para borrar.');

            return self::SUCCESS;
        }

        $borrados = 0;
        $conVentas = 0;

        foreach ($personas as $persona) {
            // No se borra si ya se usó para registrar una venta real de
            // prueba: se prefiere dejarlo y avisar, antes que perder ese
            // historial silenciosamente.
            if ($persona->sales()->exists()) {
                $conVentas++;

                continue;
            }

            // Patient se borra solo (onDelete cascade por person_id).
            $persona->delete();
            $borrados++;
        }

        $this->info("Se borraron {$borrados} paciente(s) de prueba.");

        if ($conVentas > 0) {
            $this->warn("{$conVentas} paciente(s) de prueba no se borraron porque ya tienen ventas registradas (para no perder ese historial). Si estás seguro, borralos a mano desde Pacientes.");
        }

        return self::SUCCESS;
    }
}
