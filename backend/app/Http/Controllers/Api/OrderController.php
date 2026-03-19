<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Zona;
use App\Models\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'cupon' => 'nullable|string',
        ]);

        try {
            $comercio_id = $request->comercio_id;
            $negocio = Negocio::findOrFail($comercio_id);
            
            if ($negocio->estado !== 'activo' || !$negocio->modulo_abierto) {
                return response()->json([
                    'status' => 'error',
                    'message' => '⚠️ El negocio se encuentra cerrado actualmente. Tu pedido no puede ser procesado.'
                ], 422);
            }

            $subtotal_real = 0.0;
            $processed_items = [];

            foreach ($request->items as $item) {
                if (!isset($item['id'])) continue;
                $qty = max(1, intval($item['qty'] ?? 1));
                
                $producto = Producto::where('id', $item['id'])
                    ->where('id_negocio', $comercio_id)
                    ->where('disponible', true)
                    ->first();
                
                if ($producto) {
                    $base_price = floatval($producto->precio);
                    $comps_price = 0.0;
                    $processed_comps = [];
                    
                    if (isset($item['complementos']) && is_array($item['complementos'])) {
                        $db_comps = $producto->complementos ?: [];
                        foreach ($item['complementos'] as $c_req) {
                            foreach ($db_comps as $c_db) {
                                if (isset($c_db['nombre']) && isset($c_req['nombre']) && $c_db['nombre'] === $c_req['nombre']) {
                                    $c_price = floatval($c_db['precio'] ?? 0);
                                    $comps_price += $c_price;
                                    $processed_comps[] = ['nombre' => $c_db['nombre'], 'precio' => $c_price];
                                    break;
                                }
                            }
                        }
                    }
                    
                    $item_total = ($base_price + $comps_price) * $qty;
                    $subtotal_real += $item_total;
                    $processed_items[] = [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'qty' => $qty,
                        'precio' => $base_price,
                        'complementos' => $processed_comps,
                        'instrucciones' => substr(strip_tags($item['instrucciones'] ?? ''), 0, 255)
                    ];
                } else {
                     return response()->json(['status' => 'error', 'message' => '⚠️ Un producto de tu carrito ya no está disponible.'], 422);
                }
            }

            if (empty($processed_items)) {
                return response()->json(['status' => 'error', 'message' => 'El carrito está vacío.'], 422);
            }

            $envio_real = 0.0;
            $modalidad = $request->modalidad ?? 'delivery';
            if ($modalidad === 'delivery') {
                $zona = Zona::where('nombre_colonia', $request->cliente_zona)->first();
                if ($zona) $envio_real = floatval($zona->costo_envio);
            }

            $descuento_real = 0.0;
            $cupon_codigo = $request->cupon;
            if ($cupon_codigo) {
                $cupon = DB::transaction(function () use ($cupon_codigo) {
                    return Cupon::where('codigo', strtoupper($cupon_codigo))
                        ->where('estado', 'activo')
                        ->whereColumn('usos_actuales', '<', 'limite_uso')
                        ->lockForUpdate()
                        ->first();
                });
                if ($cupon) {
                    $descuento_real = ($cupon->tipo === 'fijo') ? floatval($cupon->valor) : ($subtotal_real * ($cupon->valor / 100));
                    $cupon->increment('usos_actuales');
                } else {
                    $cupon_codigo = null;
                }
            }

            $total_real = max(0, $subtotal_real + $envio_real - $descuento_real);

            $pedido = Pedido::create([
                'comercio_id' => $comercio_id,
                'cliente_zona' => $request->cliente_zona,
                'items_json' => $processed_items,
                'subtotal' => $subtotal_real,
                'descuento' => $descuento_real,
                'envio' => $envio_real,
                'total' => $total_real,
                'cupon' => $cupon_codigo,
                'instrucciones' => $request->instrucciones,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                'modalidad' => $modalidad,
                'estado' => 'pendiente'
            ]);

            try {
                DB::statement("INSERT INTO stats_pedidos (id_negocio, fecha, cantidad) VALUES (?, CURDATE(), 1) ON DUPLICATE KEY UPDATE cantidad = cantidad + 1", [$comercio_id]);
            } catch (\Exception $e) {
                Log::warning('stats_pedidos insert failed: ' . $e->getMessage());
            }

            try {
                $webhookUrl = env('N8N_WEBHOOK_URL', 'https://n8n-n8n.amv1ou.easypanel.host/webhook/pideloya');
                Http::timeout(5)->post($webhookUrl, [
                    'comercio_id' => $comercio_id,
                    'negocio_nombre' => $negocio->nombre,
                    'cliente_zona' => $pedido->cliente_zona,
                    'order_id' => $pedido->id,
                    'subtotal' => $subtotal_real,
                    'descuento' => $descuento_real,
                    'envio' => $envio_real,
                    'total' => $total_real,
                    'cupon' => $cupon_codigo,
                    'metodo_entrega' => $modalidad,
                    'whatsapp_pedidos' => $negocio->whatsapp_pedidos ?: $negocio->telefono_contacto,
                    'items' => $processed_items,
                    'instrucciones' => $pedido->instrucciones
                ]);
            } catch (\Exception $e) {
                Log::warning('n8n webhook failed: ' . $e->getMessage());
            }

            return response()->json(['status' => 'success', 'order_id' => $pedido->id], 201);

        } catch (\Exception $e) {
            Log::error("Order error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function status($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) return response()->json(['status' => 'error'], 404);

        return response()->json([
            'status' => 'success',
            'estado' => $pedido->estado,
            'repartidor_nombre' => $pedido->repartidor_nombre,
            'repartidor_telefono' => $pedido->repartidor_telefono,
            'updated_at' => $pedido->updated_at,
            'metodo_entrega' => $pedido->modalidad
        ]);
    }
}
