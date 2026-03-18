<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Liquidacion;
use App\Models\Pedido;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiquidacionController extends Controller
{
    public function index()
    {
        $liquidaciones = Liquidacion::with('negocio')->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $liquidaciones]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'negocio_id' => 'required|exists:negocios,id',
            'periodo_inicio' => 'required|date',
            'periodo_fin' => 'required|date',
            'comision_porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        $negocio = Negocio::findOrFail($request->negocio_id);
        
        // Calcular ventas en el periodo
        $stats = Pedido::where('comercio_id', $negocio->id)
            ->where('estado', 'entregado')
            ->whereBetween('created_at', [
                Carbon::parse($request->periodo_inicio)->startOfDay(),
                Carbon::parse($request->periodo_fin)->endOfDay()
            ])
            ->selectRaw('SUM(total) as total_ventas')
            ->first();

        $totalVentas = $stats->total_ventas ?? 0;
        $porcentaje = $request->comision_porcentaje ?? 5; // Default 5%
        $comision = ($totalVentas * $porcentaje) / 100;
        $montoLiquidar = $totalVentas - $comision;

        $liquidacion = Liquidacion::create([
            'negocio_id' => $negocio->id,
            'periodo_inicio' => $request->periodo_inicio,
            'periodo_fin' => $request->periodo_fin,
            'total_ventas' => $totalVentas,
            'comision_plataforma' => $comision,
            'monto_liquidar' => $montoLiquidar,
            'estado' => 'pendiente',
            'notas' => "Liquidación generada automáticamente. Comisión: {$porcentaje}%"
        ]);

        return response()->json(['status' => 'success', 'data' => $liquidacion]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,pagado,cancelado',
            'comprobante_url' => 'nullable|string',
            'notas' => 'nullable|string'
        ]);

        $liquidacion = Liquidacion::findOrFail($id);
        
        $data = [
            'estado' => $request->estado,
            'notas' => $request->notas ?? $liquidacion->notas
        ];

        if ($request->estado === 'pagado') {
            $data['fecha_pago'] = now();
            if ($request->comprobante_url) {
                $data['comprobante_url'] = $request->comprobante_url;
            }
        }

        $liquidacion->update($data);

        return response()->json(['status' => 'success', 'data' => $liquidacion]);
    }

    public function partnerLiquidaciones(Request $request)
    {
        $negocio = $request->user()->negocio;
        if (!$negocio) {
            return response()->json(['status' => 'error', 'message' => 'No se encontró negocio asociado'], 404);
        }

        $liquidaciones = Liquidacion::where('negocio_id', $negocio->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $liquidaciones]);
    }
}
