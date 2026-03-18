<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Negocio;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = Auth::guard('api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'cliente', // Por defecto cliente
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado correctamente',
            'user' => $user
        ], 201);
    }

    /**
     * Handle partner (business) registration.
     */
    public function registerPartner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100', // Nombre del dueño
            'business_name' => 'required|string|max:150',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'phone' => 'required|string|max:20',
            'category' => 'required|string|max:100',
            'address' => 'nullable|string',
            'horarios' => 'nullable|array',
            'entrega_domicilio' => 'boolean',
            'recolecta_pedidos' => 'boolean',
            'consumo_sucursal' => 'boolean',
            'acepta_efectivo' => 'boolean',
            'acepta_tarjeta' => 'boolean',
            'acepta_transferencia' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'socio',
            ]);

            $business = Negocio::create([
                'user_id' => $user->id,
                'nombre' => $request->business_name,
                'categoria' => $request->category,
                'telefono_contacto' => $request->phone,
                'direccion' => $request->address,
                'horarios' => $request->horarios,
                'entrega_domicilio' => $request->has('entrega_domicilio') ? $request->entrega_domicilio : true,
                'recolecta_pedidos' => $request->has('recolecta_pedidos') ? $request->recolecta_pedidos : true,
                'consumo_sucursal' => $request->has('consumo_sucursal') ? $request->consumo_sucursal : true,
                'acepta_efectivo' => $request->has('acepta_efectivo') ? $request->acepta_efectivo : true,
                'acepta_tarjeta' => $request->has('acepta_tarjeta') ? $request->acepta_tarjeta : false,
                'acepta_transferencia' => $request->has('acepta_transferencia') ? $request->acepta_transferencia : false,
                'estado' => 'pendiente',
                'plan' => 'esencial',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Tu solicitud ha sido enviada. El administrador revisará tu negocio pronto.',
                'user' => $user,
                'business' => $business
            ], 201);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => 'No se pudo completar el registro', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the authenticated user.
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
