<?php

namespace App\Http\Middleware;

use App\Models\DojoMensualidad;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckDojoMensualidad
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Usuario global (admin / administrador): nunca se bloquea, aunque tenga
        // seleccionado en el sidebar un dojo con la mensualidad SaaS vencida.
        if (Auth::user()->isGlobal()) {
            return $next($request);
        }

        $dojoId = Auth::user()->getRawOriginal('dojo_id');

        if (!$dojoId) {
            return $next($request);
        }

        $vigente = DojoMensualidad::where('dojo_id', $dojoId)
            ->where('fecha_fin', '>=', Carbon::today())
            ->whereNull('deleted_at')
            ->exists();

        if (!$vigente) {
            return redirect()->route('errors', ['id' => 402]);
        }

        return $next($request);
    }
}
