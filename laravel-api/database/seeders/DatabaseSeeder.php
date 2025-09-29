<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
          $empresa1 = Tenant::create([
            'id' => 'empresa1', // este será el schema en PostgreSQL
        ]);
        $empresa1->domains()->create([
            'domain' => 'empresa1.com',
        ]);

        // Tenant Empresa 2
        $empresa2 = Tenant::create([
            'id' => 'empresa2', // este será otro schema
        ]);
        $empresa2->domains()->create([
            'domain' => 'empresa2.com',
        ]);
    }
}
