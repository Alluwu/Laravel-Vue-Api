<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMigrateAll extends Command
{
    protected $signature = 'tenant:migrate-all';
    protected $description = 'Ejecuta migraciones tenant para todos los schemas';

    public function handle()
    {
        $tenants = DB::table('tenants')->get(); 

        foreach ($tenants as $t) {
            $schema = $t->schema_name;

            Config::set('database.connections.pgsql.schema', $schema . ',public');
            DB::purge('pgsql');
            DB::reconnect('pgsql');
            DB::statement('SET search_path TO ' . $schema . ',public');

            $this->info("Migrando {$schema}...");
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--database' => 'pgsql',
                '--force' => true,
            ]);
        }

        $this->info('Migraciones por-tenant completadas.');
    }
}