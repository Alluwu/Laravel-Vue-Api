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
    
        $hostHeader = $request->header('host') ?? $request->getHost();
        $host = explode(':', $hostHeader)[0]; // quita el puerto si viene

        $parts = explode('.', $host);
        $subdomain = null;

        if (count($parts) >= 2) {

            $subdomain = $parts[0];
        }

        if (!$subdomain || in_array(strtolower($subdomain), ['www'])) {
            throw new NotFoundHttpException('Tenant no encontrado (subdominio ausente).');
        }


        $tenant = Tenant::where('subdomain', $subdomain)->first();
        if (!$tenant) {
            throw new NotFoundHttpException("Tenant '{$subdomain}' no existe.");
        }


        $schema = preg_replace('/[^a-zA-Z0-9_]/', '', $tenant->schema);
        if ($schema === '') {
            throw new NotFoundHttpException('Schema inválido.');
        }

        DB::purge(); 
        DB::connection()->statement("SET search_path TO {$schema}, public");

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
