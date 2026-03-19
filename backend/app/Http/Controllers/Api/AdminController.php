<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Negocio;
use App\Models\Pedido;

class AdminController extends Controller
{
    /**
     * Get global system stats
     */
    public function getDashboard()
    {
        $totalRevenue = Pedido::where('estado', 'entregado')->sum('total') ?? 0;
        $totalOrders = Pedido::count();
        $totalBusinesses = Negocio::count();
        $totalCustomers = User::where('role', 'cliente')->count();
        
        $latestOrders = Pedido::with('negocio')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $pendingBusinesses = Negocio::with('user')
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => [
                    'revenue' => (float)$totalRevenue,
                    'orders' => $totalOrders,
                    'businesses' => $totalBusinesses,
                    'customers' => $totalCustomers,
                    'pending_requests' => $pendingBusinesses->count(),
                ],
                'latest_orders' => $latestOrders,
                'pending_businesses' => $pendingBusinesses
            ]
        ]);
    }

    /**
     * List all businesses
     */
    public function getBusinesses(Request $request)
    {
        $businesses = Negocio::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $businesses->items(),
            'meta' => [
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'total' => $businesses->total(),
            ]
        ]);
    }

    /**
     * Approve or update business
     */
    public function updateBusinessStatus(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,activo,suspendido,rechazado'
        ]);

        $business = Negocio::findOrFail($id);
        $business->estado = $request->estado;
        $business->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del negocio actualizado'
        ]);
    }
}
