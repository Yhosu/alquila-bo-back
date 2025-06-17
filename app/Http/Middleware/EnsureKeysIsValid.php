<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKeysIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $apiKey    = $request->header('x-api-key');
        $secretKey = $request->header('x-secret-key');
            /* Validar api key */
        $now = date('Y-m-d H:i:s');
        $existAuthorization = \App\Models\ApiKey::where( 'api_key', $apiKey )
            ->where( 'secret_key', $secretKey )
            ->where( 'expires_at' , '>=', $now )
            ->where( 'active', 1 )
            ->first();
        if( !$existAuthorization ) return response()->json(['status' => false, 'message' => 'No autorizado'], 401, []);
            /* Validar tenant */
        $request->merge( [ 'apiKeyModel' => $existAuthorization ] );
        return $next($request);
    }
}
