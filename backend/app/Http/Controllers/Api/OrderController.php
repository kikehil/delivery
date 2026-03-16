<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'comercio_id' => 'required|exists:negocios,id',
            'items' => 'required|array',
            'cliente_zona' => 'required|string',
            'metodo_pago' => 'nullable|string',
            'modalidad' => 'nullable|string',
            'instrucciones' => 'nullable|string',
        ]);

        try {
            $negocio = Negocio::findOrFail($request->comercio_id);
            
            if ($negocio->estado !== 'activo') {
                return response()->json(['message' => 'El negocio no está aceptando pedidos actualmente.'], 422);
            }

            $items = $request->items;
            $subtotal = 0;
            
            foreach ($items as $item) {
                $subtotal += ($item['precio'] * $item['qty']);
            }

            $envio = 0;
            $zona = \App\Models\Zona::where('nombre_colonia', $request->cliente_zona)->first();
            if ($zona) {
                $envio = $zona->costo_envio;
            }

            $total = $subtotal + $envio;

            $pedido = Pedido::create([
                'comercio_id' => $request->comercio_id,
                'cliente_zona' => $request->cliente_zona,
                'items_json' => $items,
                'subtotal' => $subtotal,
                'envio' => $envio,
                'total' => $total,
                'instrucciones' => $request->instrucciones,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'modalidad' => $request->modalidad ?? 'delivery',
                'estado' => 'pendiente'
            ]);

            // --- Trigger n8n Automation ---
            try {
                $webhookUrl = config('services.n8n.webhook_url') ?: 'https://n8n-n8n.amv1ou.easypanel.host/webhook/pideloya';
                
                Http::timeout(5)->post($webhookUrl, [
                    'order_id' => $pedido->id,
                    'negocio_nombre' => $negocio->nombre,
                    'whatsapp_negocio' => $negocio->telefono_contacto,
                    'cliente_zona' => $pedido->cliente_zona,
                    'subtotal' => $pedido->subtotal,
                    'envio' => $pedido->envio,
                    'total' => $pedido->total,
                    'items' => $items,
                    'instrucciones' => $pedido->instrucciones
                ]);
            } catch (\Exception $webhookEx) {
                \Log::error("Failed to send order to n8n: " . $webhookEx->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido procesado con éxito',
                'data' => $pedido
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }
}
