<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ApiResponseService;

class EnsureAdminIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $auth = auth()->user();
        if( !$auth->is_admin ) return ApiResponseService::error(message:'No autorizado.', errors:['Usted no tiene permisos como administrador.'], code:401 );
        $request->merge( [ 'apiKey' => $auth ] );
        return $next($request);
    }
}