{{--
    Línea de contacto de la clínica (dirección · teléfono · redes), para
    incluir en el encabezado de cualquier PDF/impresión. Se carga desde
    Configuración > Apariencia y no se muestra nada si el admin no cargó
    ningún dato, para no dejar un espacio vacío en el papel.

    Centralizado acá (en vez de repetir la lógica en cada plantilla) para
    que agregar/quitar un dato de contacto se refleje en todas las PDF a
    la vez.
--}}
@php $clinicSetting = \App\Models\ClinicSetting::instance(); @endphp
@if ($clinicSetting->hasContactInfo())
    <p style="text-align: center; font-size: 10.5px; color: #777; margin: 2px 0 10px 0;">
        {{ collect([$clinicSetting->contact_address, $clinicSetting->contact_phone, $clinicSetting->contact_social])->filter()->implode('   ·   ') }}
    </p>
@endif
