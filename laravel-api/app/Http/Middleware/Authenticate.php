<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
   
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'message' => 'No autenticado. Token inválido o ausente.'
        ], 401));
    }

  
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
