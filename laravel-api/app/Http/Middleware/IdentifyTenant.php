<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        
        $host = $request->getHost();        
        $parts = explode('.', $host);

        
        $parts[0] = explode(':', $parts[0])[0];

        $subdomain = $parts[0] ?? null;


        if (!$subdomain || in_array($subdomain, ['www'])) {

            throw new NotFoundHttpException('Tenant no encontrado.');
        }

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            throw new NotFoundHttpException('Tenant no válido.');
        }

 
        $schema = preg_replace('/[^a-zA-Z0-9_]/', '', $tenant->schema);


        DB::purge(); 

        DB::connection()->statement("SET search_path TO {$schema}, public");

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}