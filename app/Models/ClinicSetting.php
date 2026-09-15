<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ClinicSetting extends Model
{
    /**
     * Color de marca por defecto (el mismo verde/teal que ya se usaba
     * "quemado" en el código antes de que este campo existiera).
     */
    public const DEFAULT_PRIMARY_COLOR = '#0d9488';

    /**
     * Tamaño de letra por defecto y las únicas opciones válidas. Cada una
     * se traduce a un porcentaje que se aplica sobre el font-size raíz
     * (ver fontSizePercent()): como el resto del sistema usa las clases de
     * Tailwind en rem, cambiar el raíz reescala todo el texto sin tener
     * que tocar cada clase una por una.
     */
    public const DEFAULT_FONT_SIZE = 'normal';

    public const FONT_SIZES = [
        'small' => '87.5%',
        'normal' => '100%',
        'large' => '112.5%',
    ];

    protected $fillable = [
        'google_calendar_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_account_email',
        'logo_path',
        'primary_color',
        'font_size',
        'system_name',
        'contact_address',
        'contact_phone',
        'contact_social',
    ];

    protected $casts = [
        'google_access_token' => 'encrypted',
        'google_refresh_token' => 'encrypted',
        'google_token_expires_at' => 'datetime',
    ];

    /**
     * La configuración de la clínica vive en una sola fila (id = 1).
     * Este helper la trae (o la crea vacía si todavía no existe).
     */
    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function isGoogleConnected(): bool
    {
        return ! empty($this->google_refresh_token);
    }

    /**
     * Color de marca a usar en el layout (siempre devuelve algo válido,
     * nunca null/vacío), para no tener que repetir el fallback en cada
     * vista que lo use.
     */
    public function brandColor(): string
    {
        return $this->primary_color ?: self::DEFAULT_PRIMARY_COLOR;
    }

    /**
     * Porcentaje de font-size a aplicar sobre <html>, listo para usar en
     * un @style/CSS inline. Si el valor guardado no es uno de los
     * válidos (dato corrupto, fila recién creada, etc.) cae al 100%
     * normal en vez de romper el layout.
     */
    public function fontSizePercent(): string
    {
        return self::FONT_SIZES[$this->font_size] ?? self::FONT_SIZES[self::DEFAULT_FONT_SIZE];
    }

    /**
     * Nombre del sistema para el <title> del navegador. Si el admin no
     * configuró uno propio desde Apariencia, usa el APP_NAME del .env
     * como venía funcionando antes de este campo.
     */
    public function systemName(): string
    {
        return $this->system_name ?: config('app.name', 'Laravel');
    }

    /**
     * True si hay al menos un dato de contacto cargado, para que las
     * plantillas PDF puedan decidir si muestran el bloque de contacto o
     * no lo muestran directamente (en vez de imprimir un bloque vacío).
     */
    public function hasContactInfo(): bool
    {
        return filled($this->contact_address) || filled($this->contact_phone) || filled($this->contact_social);
    }

    /**
     * URL del logo para usar en <img> dentro del navegador: el que se
     * subió desde Configuración > Apariencia, o el que trae el sistema
     * por defecto (public/images/logo.png) si todavía no se subió ninguno
     * o el archivo subido ya no existe en disco.
     */
    public function logoUrl(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }

        return asset('images/logo.png');
    }

    /**
     * Logo ya codificado como data URI (data:image/...;base64,...), listo
     * para incrustar en un PDF (dompdf no puede resolver URLs con sesión/
     * auth, así que en los PDF el logo siempre se manda embebido).
     * Devuelve null si no hay ningún logo disponible (ni el subido ni el
     * de public/images/logo.png), en cuyo caso la vista simplemente no
     * debe mostrar el bloque del logo.
     *
     * Centraliza acá lo que antes estaba duplicado en cada plantilla PDF
     * (receta, historia clínica, listados de pacientes/doctores/etc.).
     */
    public function logoBase64(): ?string
    {
        $absolutePath = null;

        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            $absolutePath = Storage::disk('public')->path($this->logo_path);
        } elseif (file_exists(public_path('images/logo.png'))) {
            $absolutePath = public_path('images/logo.png');
        }

        if (! $absolutePath || ! is_readable($absolutePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        // dompdf no compone bien la transparencia de los PNG: el fondo
        // transparente del logo (que en el navegador se ve normal) le sale
        // negro "sucio", con un borde irregular alrededor. Para evitarlo,
        // acá se aplana el logo sobre un fondo negro sólido (a propósito:
        // el logo tiene el subtítulo "CENTRO INTEGRAL" en blanco, así que
        // negro es el único fondo plano donde se sigue leyendo) antes de
        // incrustarlo, dejando un resultado prolijo y siempre igual.
        if ($extension === 'png' && function_exists('imagecreatefrompng')) {
            $flattened = $this->flattenPngOnBlack($absolutePath);

            if ($flattened !== null) {
                return 'data:image/png;base64,' . base64_encode($flattened);
            }
        }

        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absolutePath));
    }

    /**
     * Aplana un PNG (con transparencia) sobre un fondo negro sólido y
     * devuelve los bytes del PNG resultante, o null si GD no pudo leerlo
     * (en cuyo caso logoBase64() cae de nuevo al archivo original tal cual).
     */
    private function flattenPngOnBlack(string $absolutePath): ?string
    {
        $source = @imagecreatefrompng($absolutePath);

        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        $flattened = imagecreatetruecolor($width, $height);
        $black = imagecolorallocate($flattened, 0, 0, 0);
        imagefill($flattened, 0, 0, $black);

        imagealphablending($flattened, true);
        imagesavealpha($flattened, false);
        imagecopy($flattened, $source, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagepng($flattened);
        $data = ob_get_clean();

        imagedestroy($source);
        imagedestroy($flattened);

        return $data !== false && $data !== '' ? $data : null;
    }
}
