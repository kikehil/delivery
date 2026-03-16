<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    /**
     * Get active promotions for the frontend carousel.
     */
    public function index()
    {
        $promotions = Promocion::where('activo', true)
            ->orderBy('orden', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $promotions
        ]);
    }

    /**
     * Admin: List all promotions.
     */
    public function adminIndex()
    {
        $promotions = Promocion::orderBy('orden', 'asc')->get();
        return response()->json(['data' => $promotions]);
    }

    /**
     * Admin: Create promotion.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string',
            'subtitulo' => 'nullable|string',
            'tag_text' => 'nullable|string',
            'boton_text' => 'string',
            'imagen_url' => 'required|string',
            'link_url' => 'nullable|string',
            'orden' => 'integer',
            'color_fondo' => 'string'
        ]);

        $promotion = Promocion::create($validated);
        return response()->json(['data' => $promotion]);
    }

    /**
     * Admin: Update promotion.
     */
    public function update(Request $request, $id)
    {
        $promotion = Promocion::findOrFail($id);
        
        $validated = $request->validate([
            'titulo' => 'string',
            'subtitulo' => 'nullable|string',
            'tag_text' => 'nullable|string',
            'boton_text' => 'string',
            'imagen_url' => 'string',
            'link_url' => 'nullable|string',
            'orden' => 'integer',
            'activo' => 'boolean',
            'color_fondo' => 'string'
        ]);

        $promotion->update($validated);
        return response()->json(['data' => $promotion]);
    }

    /**
     * Admin: Delete promotion.
     */
    public function destroy($id)
    {
        $promotion = Promocion::findOrFail($id);
        $promotion->delete();
        return response()->json(['message' => 'Promoción eliminada']);
    }
}
