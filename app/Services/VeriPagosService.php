<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente para la API de cobro por QR de VeriPagos (BCP).
 *
 * Documentación: https://literate-hickory-4bc.notion.site/Requests-4e38d256f8754dfea8e58948877e8d16
 *
 * El Basic Auth de la API usa el secret_key como usuario y una contraseña
 * aparte (confirmado con el proveedor), ambos guardados en config/services.php.
 */
class VeriPagosService
{
    protected string $baseUrl;
    protected string $secretKey;
    protected string $authUser;
    protected string $authPassword;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.veripagos.base_url'), '/');
        $this->secretKey = (string) config('services.veripagos.secret_key');
        $this->authUser = (string) config('services.veripagos.auth_user');
        $this->authPassword = (string) config('services.veripagos.auth_password');
    }

    protected function client()
    {
        return Http::withBasicAuth($this->authUser, $this->authPassword)
            ->acceptJson()
            ->timeout(15);
    }

    /**
     * Genera un QR de cobro.
     *
     * @param  float  $monto  Monto a cobrar en Bs.
     * @param  array  $data  Información extra que VeriPagos reenvía tal cual en el webhook.
     * @param  string|null  $vigencia  Formato "dias/horas:minutos", ej. "0/00:10". Default de la API: "0/00:15".
     * @param  bool  $usoUnico  true = QR de un solo pago (default de la API).
     * @param  string|null  $detalle  Texto que ve el pagador.
     * @return array{movimiento_id: int, qr: string} "qr" ya viene con el prefijo data:image/png;base64,
     *
     * @throws \RuntimeException
     */
    public function generarQr(
        float $monto,
        array $data = [],
        ?string $vigencia = null,
        bool $usoUnico = true,
        ?string $detalle = null
    ): array {
        $payload = [
            'secret_key' => $this->secretKey,
            'monto' => $monto,
            'uso_unico' => $usoUnico,
        ];

        if (! empty($data)) {
            $payload['data'] = $data;
        }
        if ($vigencia) {
            $payload['vigencia'] = $vigencia;
        }
        if ($detalle) {
            $payload['detalle'] = $detalle;
        }

        $response = $this->client()->post("{$this->baseUrl}/generar-qr", $payload);
        $body = $response->json();

        if (! $response->successful() || (int) ($body['Codigo'] ?? 1) !== 0) {
            Log::warning('VeriPagos: fallo al generar QR', ['status' => $response->status(), 'body' => $body]);

            throw new \RuntimeException($body['Mensaje'] ?? 'No se pudo generar el QR. Intente nuevamente.');
        }

        return [
            'movimiento_id' => $body['Data']['movimiento_id'],
            'qr' => 'data:image/png;base64,' . $body['Data']['qr'],
        ];
    }

    /**
     * Consulta el estado de un QR generado previamente.
     *
     * @return array{movimiento_id: mixed, monto: float, detalle: string, estado: string, estado_notificacion: string, remitente: array|null}
     *
     * @throws \RuntimeException
     */
    public function verificarEstado(string $movimientoId): array
    {
        $response = $this->client()->post("{$this->baseUrl}/verificar-estado-qr", [
            'secret_key' => $this->secretKey,
            'movimiento_id' => $movimientoId,
        ]);
        $body = $response->json();

        if (! $response->successful() || (int) ($body['Codigo'] ?? 1) !== 0) {
            Log::warning('VeriPagos: fallo al verificar estado de QR', [
                'movimiento_id' => $movimientoId,
                'status' => $response->status(),
                'body' => $body,
            ]);

            throw new \RuntimeException($body['Mensaje'] ?? 'No se pudo verificar el estado del QR.');
        }

        return $body['Data'];
    }

    /**
     * Verifica que las credenciales Basic Auth de una petición entrante
     * (por ejemplo el webhook) coincidan con las configuradas.
     */
    public function credencialesWebhookValidas(?string $user, ?string $password): bool
    {
        return hash_equals($this->authUser, (string) $user)
            && hash_equals($this->authPassword, (string) $password);
    }
}
