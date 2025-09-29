<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetTenantSchema
{
    public function handle(Request $request, Closure $next)
    {
        
        $host = $request->getHost();
        $tenant = app('tenant.resolver')->fromHost($host); 
        if (!$tenant || !$tenant->schema_name) {
            abort(404, 'Tenant no encontrado');
        }

        $schema = $tenant->schema_name;

    
        Config::set('database.connections.pgsql.schema', $schema . ',public');
        DB::purge('pgsql');
        DB::reconnect('pgsql');


        DB::statement('SET search_path TO ' . $schema . ',public');

        app()->instance('tenant.current', $tenant);

        return $next($request);
    }
}
