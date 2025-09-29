<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rutas web. Sin HomeController. Se deja una raíz pública y un grupo
| multi-tenant con rutas de prueba en closures (JSON).
|
*/


Route::get('/', function () {
    return view('welcome'); 
})->name('root');


Route::middleware('tenant')->group(function () {


    Route::get('/ping-tenant', function () {
        $schema = optional(DB::selectOne("select current_schema as s"))->s;
        return response()->json([
            'ok'     => true,
            'schema' => $schema,
            'host'   => request()->getHost(),
        ]);
    })->name('tenant.ping');


    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Bienvenido al tenant',
            'host'    => request()->getHost(),
        ]);
    })->name('tenant.dashboard');
});


Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});