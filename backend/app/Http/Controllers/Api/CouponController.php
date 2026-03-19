<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string'
        ]);

        $codigo = strtoupper(trim($request->codigo));
        $cupon = Cupon::where('codigo', $codigo)
            ->where('estado', 'activo')
            ->first();

        if (!$cupon) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Este código no es válido o ya expiró'
            ]);
        }

        if ($cupon->usos_actuales >= $cupon->limite_uso) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Este cupón ha agotado su límite de uso'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'codigo' => $cupon->codigo,
            'tipo' => $cupon->tipo,
            'valor' => (float)$cupon->valor
        ]);
    }
}
