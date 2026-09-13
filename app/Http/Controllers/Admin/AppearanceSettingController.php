<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicSetting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

/**
 * Configuración > Apariencia: acá el admin cambia el logo del sistema (se
 * usa en el menú y en todos los PDF/impresiones) y el color de marca
 * (menú, encabezados y botón principal), sin tener que tocar código.
 */
class AppearanceSettingController extends Controller
{
    // Ancho máximo (px) al que se normaliza el logo subido. Si la imagen
    // original es más chica que esto no se agranda (agrandarla es lo que la
    // pixela); si es más grande, se achica para no guardar archivos pesados
    // sin necesidad.
    private const MAX_LOGO_WIDTH = 1000;

    // Por debajo de este ancho o alto (de la imagen ORIGINAL que subió el
    // usuario, antes de normalizar) avisamos que la resolución es baja y
    // puede pixelarse, sobre todo al imprimirse en las PDF.
    private const MIN_RECOMMENDED_WIDTH = 400;

    private const MIN_RECOMMENDED_HEIGHT = 100;

    public function __construct()
    {
        $this->middleware('can:admin.settings.appearance');
    }

    public function index()
    {
        $setting = ClinicSetting::instance();

        return view('admin.settings.appearance', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = ClinicSetting::instance();

        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'primary_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'font_size' => 'required|in:'.implode(',', array_keys(ClinicSetting::FONT_SIZES)),
            'system_name' => 'nullable|string|max:60',
            'contact_address' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:40',
            'contact_social' => 'nullable|string|max:150',
        ], [
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'Formatos permitidos: PNG, JPG o WEBP.',
            'logo.max' => 'El logo no debe superar los 2MB.',
            'primary_color.regex' => 'El color debe ser un código hexadecimal válido (ej: #0d9488).',
            'font_size.in' => 'Elegí un tamaño de letra válido.',
        ]);

        // "Quitar logo actual": vuelve a usar el logo por defecto del
        // sistema (public/images/logo.png) sin subir uno nuevo.
        if ($request->boolean('remove_logo')) {
            $this->deleteStoredLogo($setting);
            $setting->logo_path = null;
        }

        $lowResWarning = null;

        if ($request->hasFile('logo')) {
            // Reemplaza el anterior (si había uno subido) para no dejar
            // archivos huérfanos ocupando espacio en el servidor.
            $this->deleteStoredLogo($setting);

            [$path, $lowResWarning] = $this->storeNormalizedLogo($request->file('logo'));
            $setting->logo_path = $path;
        }

        $setting->primary_color = $request->primary_color;
        $setting->font_size = $request->font_size;
        $setting->system_name = $request->system_name;
        $setting->contact_address = $request->contact_address;
        $setting->contact_phone = $request->contact_phone;
        $setting->contact_social = $request->contact_social;
        $setting->save();

        if ($lowResWarning) {
            session()->flash('logo_warning', $lowResWarning);
        }

        session()->flash('swal', [
            'title' => 'Apariencia actualizada',
            'text' => 'Los cambios ya se ven en el sistema y en los PDF nuevos que se generen.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.settings.appearance.index');
    }

    /**
     * Normaliza el logo subido: le recorta el margen en blanco/transparente
     * sobrante alrededor de la marca (muchos logos vienen con harto espacio
     * vacío en el archivo original, lo que hace que se vea chico y perdido
     * dentro de su recuadro), lo convierte a PNG y lo achica si después de
     * recortarlo sigue siendo más grande de lo necesario (nunca lo agranda,
     * porque agrandar una imagen chica es justamente lo que la pixela).
     * Devuelve la ruta guardada y, si lo que quedó después de recortar
     * tiene poca resolución, un aviso para el usuario.
     *
     * @return array{0: string, 1: ?string}
     */
    private function storeNormalizedLogo(UploadedFile $file): array
    {
        $manager = ImageManager::gd();
        $image = $manager->read($file->getRealPath());

        // Recorta el margen sobrante para que el logo ocupe todo el
        // espacio visible en vez de quedar chico en el medio de un
        // recuadro vacío.
        $image->trim();

        $trimmedWidth = $image->width();
        $trimmedHeight = $image->height();

        // scaleDown() solo achica, nunca agranda: si la imagen ya recortada
        // es más chica que MAX_LOGO_WIDTH la deja tal cual.
        $image->scaleDown(width: self::MAX_LOGO_WIDTH);

        $path = 'branding/'.uniqid('logo_').'.png';
        Storage::disk('public')->put($path, (string) $image->toPng());

        $warning = null;
        if ($trimmedWidth < self::MIN_RECOMMENDED_WIDTH || $trimmedHeight < self::MIN_RECOMMENDED_HEIGHT) {
            $warning = "El logo, ya sin los márgenes en blanco, quedó en {$trimmedWidth}×{$trimmedHeight} px, una "
                .'resolución baja. Para que se vea nítido en el menú y sobre todo al imprimirse en las PDF, te '
                .'recomendamos subir uno de al menos '.self::MIN_RECOMMENDED_WIDTH.'×'.self::MIN_RECOMMENDED_HEIGHT.' px.';
        }

        return [$path, $warning];
    }

    private function deleteStoredLogo(ClinicSetting $setting): void
    {
        if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
            Storage::disk('public')->delete($setting->logo_path);
        }
    }
}
