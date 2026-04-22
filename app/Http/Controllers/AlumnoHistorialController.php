<?php

namespace App\Http\Controllers;
use App\Models\Alumno;
use App\Models\Grado;
use App\Models\AlumnoHistoriale;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AlumnoHistorialController extends Controller
{
    /**
     * Muestra el historial del alumno.
     */
    public function show($id)
    {
        $this->custom_authorize('read_alumnos');

        // Buscamos el alumno por el ID recibido
        $dataTypeContent = Alumno::with(['person', 'grado'])->findOrFail($id);
        $grado = Grado::whereNull('deleted_at')->get();
        // Filtramos el historial para que solo muestre los registros de este alumno
        $historial = AlumnoHistoriale::where('alumno_id', $id)->whereNull('deleted_at')->with(['grado'])->get();
        
        return view('alumnos.historial.browse', compact('grado', 'historial', 'dataTypeContent'));
    }

    /**
     * Obtiene la lista filtrada del historial para la petición AJAX.
     */
    public function historialList(Request $request, $id)
    {
        $this->custom_authorize('read_alumnos');

        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);

        $historial = AlumnoHistoriale::where('alumno_id', $id)
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('tipo', 'like', "%$search%")
                          ->orWhere('observaciones', 'like', "%$search%");
                }
            })
            ->with(['grado'])
            ->orderBy('fecha', 'desc')
            ->paginate($paginate);

        return view('alumnos.historial.list', compact('historial'));
    }
}
