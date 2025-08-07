<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use Laravel\Sanctum\PersonalAccessToken;

class EnsureClientToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener el token del header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }

        // Buscar el token en la base de datos
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido'
            ], 401);
        }

        // Verificar que el token pertenece a un cliente
        if ($accessToken->tokenable_type !== Client::class) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido para cliente'
            ], 401);
        }

        // Establecer el cliente autenticado
        $client = $accessToken->tokenable;
        
        if (!$client || !$client->getAttribute('is_active')) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no activo'
            ], 401);
        }

        // Establecer el usuario autenticado (cast del modelo Client a Authenticatable)
        if ($client instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            Auth::setUser($client);
        }
        
        return $next($request);
    }
}
