<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            if ($request->is('administrador/*') || $request->is('administrador')) {
                if (Auth::guard('admin')->check()) {
                    return route('administrador.loginAdministrador');
                }
            }

            return route('login');
        }

        return null;
    }
}
