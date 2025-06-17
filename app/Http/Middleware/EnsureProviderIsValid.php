<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ApiResponseService;

class EnsureProviderIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $auth = auth()->user();
        $providerId = $request->route()->parameter('providerId');
        $providerIdsAccess = $auth->providers->pluck('id')->toArray();
        if( $providerId && !in_array( $providerId, $providerIdsAccess ) ) return ApiResponseService::error(message:'No autorizado.', errors:['Usted no tiene acceso al proveedor seleccionado.'], code:401 );
        $request->merge( [ 'providerIds' => $providerIdsAccess ] );
        $request->merge( [ 'apiKey'      => $auth ] );
        $request->merge( [ 'tenantId'    => $request->header('tenantId') ] );
        $request->merge( [ 'tenantUrl'   => $request->header('tenantUrl') ] );
        return $next($request);
    }
}
