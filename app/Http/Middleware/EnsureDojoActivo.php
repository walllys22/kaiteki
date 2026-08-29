<?php

namespace App\Http\Middleware;

use App\Models\Dojo;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Garantiza que un usuario con rol `administrador` siempre este parado en una
 * sucursal concreta. El rol `admin` queda excluido: ve todo el sistema.
 *
 * El sidebar no ofrece "Todos los dojos": si el usuario todavia no eligio
 * ninguno, se le asigna el primero activo para que nunca vea informacion
 * mezclada de varias sucursales.
 */
class EnsureDojoActivo
{
    public function handle(Request $request, Closure $next)
    {
        // Solo el rol administrador trabaja sobre una sucursal a la vez.
        // El rol admin ve todo y los operadores ya estan atados a su dojo real.
        if (! Auth::check() || ! Auth::user()->usaDojoActivo()) {
            return $next($request);
        }

        $seleccionado = session(User::DOJO_ACTIVO_SESSION_KEY);

        // Revalidar: el dojo elegido pudo darse de baja entre requests.
        if ($seleccionado && Dojo::whereNull('deleted_at')->whereKey($seleccionado)->exists()) {
            return $next($request);
        }

        $primerDojo = Dojo::whereNull('deleted_at')->orderBy('nombre')->first();

        if ($primerDojo) {
            session([User::DOJO_ACTIVO_SESSION_KEY => (int) $primerDojo->id]);
        } else {
            session()->forget(User::DOJO_ACTIVO_SESSION_KEY);
        }

        return $next($request);
    }
}
