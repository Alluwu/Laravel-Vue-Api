<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
    
        $host  = $request->getHost();        
        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? null;

        if (!$subdomain || $subdomain === 'www' || $subdomain === 'localhost') {
  
            $subdomain = $request->header('X-Tenant-Subdomain') ?: $subdomain;
        }

        if (!$subdomain) {
            throw new HttpException(404, 'No se pudo determinar el subdominio del tenant.');
        }

        $tenant = DB::table('tenants')->where('subdomain', $subdomain)->first();
        if (!$tenant) {
            throw new HttpException(404, 'Tenant no encontrado para subdominio: ' . $subdomain);
        }


        $schema = $tenant->schema;

        if (!preg_match('/^[A-Za-z0-9_]+$/', $schema)) {
            throw new HttpException(400, 'Nombre de schema inválido.');
        }

       
        $quotedSchema = '"' . str_replace('"', '""', $schema) . '"';
        $searchPath = ($schema === 'public') ? 'public' : ($quotedSchema . ',public');

     
        Config::set('database.connections.pgsql.schema', $schema === 'public' ? 'public' : $schema . ',public');
        DB::purge('pgsql');
        DB::reconnect('pgsql');


        DB::statement('SET search_path TO ' . $searchPath);

       
        app()->instance('tenant.current', $tenant);

        return $next($request);
    }
}
