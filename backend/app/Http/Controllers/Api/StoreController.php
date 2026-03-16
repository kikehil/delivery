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
        $query = Negocio::where('estado', 'activo');

        if ($request->has('zona')) {
            $query->where('id_zona_base', $request->zona);
        }

        if ($request->has('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        $stores = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $stores
        ]);
    }

    public function show($id)
    {
        $store = Negocio::find($id);
        
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
