<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantProvision extends Command
{
    protected $signature = 'tenant:provision {schema}';
    protected $description = 'Crea schema (si no existe) y corre migraciones por-tenant';

    public function handle()
    {
        $schema = $this->argument('schema');

        DB::statement('CREATE SCHEMA IF NOT EXISTS "' . str_replace('"','""',$schema) . '"');

        Config::set('database.connections.pgsql.schema', $schema . ',public');
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        DB::statement('SET search_path TO ' . $schema . ',public');

        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'pgsql',
            '--force' => true,
        ]);

        $this->info("✅ Tenant listo en schema {$schema}");
    }
}