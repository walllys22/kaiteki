<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Selector de "dojo activo" del sidebar.
 *
 * Solo aplica al rol `administrador`. El rol `admin` ve todo el sistema sin
 * selector, y un operador de sucursal nunca puede cambiar su contexto.
 */
class ContextoDojoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        // Solo el rol administrador cambia de sucursal. El rol admin ve todo y
        // no necesita contexto; los operadores estan atados a su dojo real.
        if (! $user->usaDojoActivo()) {
            abort(403);
        }

        // dojo_id es obligatorio: no existe la opcion "todos los dojos".
        $request->validate([
            'dojo_id' => 'required|exists:dojos,id',
        ], [
            'dojo_id.required' => 'Debe seleccionar una sucursal.',
            'dojo_id.exists' => 'La sucursal seleccionada no es valida.',
        ]);

        // No permitir seleccionar un dojo dado de baja.
        $dojo = Dojo::whereNull('deleted_at')->findOrFail($request->dojo_id);

        session([User::DOJO_ACTIVO_SESSION_KEY => (int) $dojo->id]);

        // Para volver a habilitar la vista global (todas las sucursales), aceptar
        // dojo_id vacio aca y hacer: session()->forget(User::DOJO_ACTIVO_SESSION_KEY);

        return back();
    }
}
