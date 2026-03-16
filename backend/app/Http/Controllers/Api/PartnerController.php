<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Pedido;

class PartnerController extends Controller
{
    /**
     * Get socio's business details
     */
    private function getBusiness()
    {
        $user = Auth::user();
        $business = Negocio::where('user_id', $user->id)->first();
        
        if (!$business) {
            abort(404, 'No business found for this partner');
        }
        
        return $business;
    }

    /**
     * Dashboard stats for partner
     */
    public function getDashboard()
    {
        $business = $this->getBusiness();
        
        $pendingOrders = Pedido::where('comercio_id', $business->id)
            ->whereIn('estado', ['pendiente', 'aceptado', 'en_preparacion'])
            ->count();
            
        $todayOrders = Pedido::where('comercio_id', $business->id)
            ->whereDate('created_at', today())
            ->count();
            
        $todayRevenue = Pedido::where('comercio_id', $business->id)
            ->whereDate('created_at', today())
            ->where('estado', 'entregado')
            ->sum('total') ?? 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'business' => $business,
                'stats' => [
                    'pending_orders' => $pendingOrders,
                    'today_orders' => $todayOrders,
                    'today_revenue' => $todayRevenue,
                ]
            ]
        ]);
    }

    /**
     * List products of the business
     */
    public function getProducts()
    {
        $business = $this->getBusiness();
        $products = Producto::where('id_negocio', $business->id)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Toggle product availability
     */
    public function toggleProduct(Request $request, $id)
    {
        $business = $this->getBusiness();
        $product = Producto::where('id', $id)->where('id_negocio', $business->id)->firstOrFail();
        
        $product->disponible = !$product->disponible;
        $product->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product availability updated',
            'data' => $product
        ]);
    }

    /**
     * Store a new product
     */
    public function storeProduct(Request $request)
    {
        $business = $this->getBusiness();
        
        $request->validate([
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'foto_url' => 'nullable|url',
        ]);
        
        $product = Producto::create([
            'id_negocio' => $business->id,
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'descripcion' => $request->descripcion,
            'foto_url' => $request->foto_url ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400',
            'disponible' => true,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Producto creado correctamente',
            'data' => $product
        ], 201);
    }

    /**
     * Update an existing product
     */
    public function updateProduct(Request $request, $id)
    {
        $business = $this->getBusiness();
        $product = Producto::where('id', $id)->where('id_negocio', $business->id)->firstOrFail();
        
        $request->validate([
            'nombre' => 'required|string|max:150',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'foto_url' => 'nullable|url',
        ]);
        
        $product->update($request->only(['nombre', 'precio', 'descripcion', 'foto_url']));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Producto actualizado correctamente',
            'data' => $product
        ]);
    }

    /**
     * Delete a product
     */
    public function deleteProduct($id)
    {
        $business = $this->getBusiness();
        $product = Producto::where('id', $id)->where('id_negocio', $business->id)->firstOrFail();
        
        $product->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Producto eliminado correctamente'
        ]);
    }

    /**
     * Get business settings
     */
    public function getSettings()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->getBusiness()
        ]);
    }

    /**
     * Update business settings
     */
    public function updateSettings(Request $request)
    {
        $business = $this->getBusiness();
        
        $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono_contacto' => 'required|string|max:20',
            'logo_url' => 'nullable|url',
            'banner_url' => 'nullable|url',
            'categoria' => 'required|string|max:100',
        ]);
        
        $business->update($request->only(['nombre', 'telefono_contacto', 'logo_url', 'banner_url', 'categoria']));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Configuración actualizada',
            'data' => $business
        ]);
    }

    /**
     * List orders of the business
     */
    public function getOrders(Request $request)
    {
        $business = $this->getBusiness();
        $status = $request->query('status');
        
        $query = Pedido::where('comercio_id', $business->id)->orderBy('created_at', 'desc');
        
        if ($status) {
            $query->where('estado', $status);
        }
        
        $orders = $query->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $business = $this->getBusiness();
        $order = Pedido::where('id', $id)->where('comercio_id', $business->id)->firstOrFail();
        
        $request->validate([
            'estado' => 'required|in:aceptado,en_preparacion,en_camino,entregado,cancelado'
        ]);
        
        $order->estado = $request->estado;
        $order->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Estado del pedido actualizado',
            'data' => $order
        ]);
    }
}
