<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
})->name('root');

Route::middleware('tenant')->group(function () {

    Route::get('/ping-tenant', function () {

        $row = DB::selectOne('SELECT current_schema() AS s, current_schemas(true) AS sp');
        return response()->json([
            'ok'     => true,
            'schema' => $row?->s,
            'path'   => $row?->sp, 
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
