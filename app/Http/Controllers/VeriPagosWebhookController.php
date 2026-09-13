<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\VeriPagosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe la notificación de VeriPagos cuando un QR fue pagado.
 *
 * No es indispensable para que el cobro por QR funcione (la pantalla de
 * Nueva Venta / pago de cuotas confirma el pago consultando
 * verificar-estado-qr mientras el cajero espera), pero sirve de respaldo:
 * si el pago se completa por Payment ya está registrado, aquí se
 * completan los datos del remitente por si la consulta desde el navegador
 * no alcanzó a traerlos.
 *
 * Ruta pública (sin sesión), protegida con el mismo Basic Auth que usa la
 * API — ver credencialesWebhookValidas() en VeriPagosService.
 */
class VeriPagosWebhookController extends Controller
{
    public function handle(Request $request, VeriPagosService $veripagos)
    {
        if (! $veripagos->credencialesWebhookValidas($request->getUser(), $request->getPassword())) {
            abort(401);
        }

        $payload = $request->all();
        Log::info('VeriPagos webhook recibido', $payload);

        $movimientoId = $payload['movimiento_id'] ?? null;

        if ($movimientoId) {
            Payment::where('qr_movimiento_id', $movimientoId)
                ->whereNull('qr_remitente')
                ->update(['qr_remitente' => json_encode($payload['remitente'] ?? null)]);
        }

        return response()->json(['ok' => true]);
    }
}
