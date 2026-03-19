<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Negocio;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('negocios as n')
            ->leftJoin('zonas as z', 'n.id_zona_base', '=', 'z.id')
            ->select(
                'n.id', 'n.nombre', 'n.categoria', 'n.logo_url', 'n.logo_url as img', 
                'n.telefono_contacto as telefono', 'n.plan', 
                'z.nombre_colonia as zona_nombre', 'n.modulo_abierto',
                'n.entrega_domicilio', 'n.recolecta_pedidos', 'n.consumo_sucursal'
            )
            ->where('n.estado', 'activo');

        if ($request->has('zona') && $request->zona > 0) {
            $query->where('n.id_zona_base', $request->zona);
        }

        if ($request->has('categoria') && $request->categoria !== '') {
            $query->where('n.categoria', $request->categoria);
        }

        $stores = $query->orderBy('n.modulo_abierto', 'desc')
            ->orderBy('n.plan', 'desc')
            ->orderBy('n.nombre', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $stores
        ]);
    }

    public function show($id)
    {
        $store = DB::table('negocios as n')
            ->leftJoin('zonas as z', 'n.id_zona_base', '=', 'z.id')
            ->select('n.*', 'z.nombre_colonia as zona_nombre')
            ->where('n.id', $id)
            ->first();
        
        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $store
        ]);
    }

    public function getZones()
    {
        $zones = DB::table('zonas')->get();
        return response()->json([
            'status' => 'success',
            'data' => $zones
        ]);
    }
}
