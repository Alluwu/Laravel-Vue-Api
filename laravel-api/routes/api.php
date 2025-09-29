<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;



Route::middleware('tenant')->group(function () {
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});


Route::middleware(['tenant','auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['tenant','auth:sanctum'])->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['tenant','auth:sanctum'])->prefix('usuarios')->group(function () {
    Route::get('/listUsers',          [UsuarioController::class, 'index']);
    Route::post('/addUser',           [UsuarioController::class, 'store']);
    Route::get('/getUser/{id}',       [UsuarioController::class, 'show']);
    Route::put('/updateUser/{id}',    [UsuarioController::class, 'update']);
    Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
});
