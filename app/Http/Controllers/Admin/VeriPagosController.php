<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VeriPagosService;
use Illuminate\Http\Request;

/**
 * Proxy hacia VeriPagos para el cobro por QR desde el panel de administración
 * (Nueva Venta y pago de cuotas). El secret_key y la contraseña de la API
 * viven solo en el servidor (config/services.php), nunca llegan al navegador.
 */
class VeriPagosController extends Controller
{
    public function __construct(private VeriPagosService $veripagos)
    {
        // Solo quien puede registrar ventas o cobrar cuotas puede generar/verificar QRs.
        $this->middleware(function (Request $request, $next) {
            abort_unless(
                $request->user()?->can('admin.sales.create') || $request->user()?->can('admin.sales.payInstallment'),
                403
            );

            return $next($request);
        });
    }

    /**
     * Genera un QR de cobro para el monto indicado.
     */
    public function generar(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.5',
            'detalle' => 'nullable|string|max:150',
        ]);

        try {
            $resultado = $this->veripagos->generarQr(
                monto: (float) $request->monto,
                vigencia: '0/00:10', // 10 minutos: alcanza para que el paciente escanee y pague en caja
                usoUnico: true,
                detalle: $request->detalle,
            );

            return response()->json([
                'ok' => true,
                'movimiento_id' => $resultado['movimiento_id'],
                'qr' => $resultado['qr'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Consulta si un QR ya fue pagado.
     */
    public function estado(Request $request, string $movimientoId)
    {
        try {
            $data = $this->veripagos->verificarEstado($movimientoId);

            return response()->json([
                'ok' => true,
                'estado' => $data['estado'] ?? null,
                'remitente' => $data['remitente'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
