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

    /**
     * Guarda un nuevo registro en el historial.
     */
    public function store(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'grado_id'  => 'required|exists:grados,id',
            'tipo'      => 'required|in:Repaso,Examen',
            'aprobo'    => 'required|in:Si,No',
            'fecha'     => 'required|date|after:today',
        ]);

        // Lógica: No duplicar grado si aprobo == "Si"
        if ($request->aprobo == 'Si') {
            $existeAprobado = AlumnoHistoriale::where('alumno_id', $request->alumno_id)
                ->where('grado_id', $request->grado_id)
                ->where('aprobo', 'Si')
                ->exists();

            if ($existeAprobado) {
                return back()->with(['message' => 'Este alumno ya aprobó el grado seleccionado.', 'alert-type' => 'error']);
            }
        }

        AlumnoHistoriale::create($request->all());

        return back()->with(['message' => 'Historial registrado correctamente', 'alert-type' => 'success']);
    }

    /**
     * Verifica si un alumno tiene registros en su historial.
     */
    public function checkHistorial($alumno_id)
    {
        $this->custom_authorize('read_alumnos'); // O el permiso adecuado para esta verificación

        $hasHistorial = AlumnoHistoriale::where('alumno_id', $alumno_id)->exists();
        return response()->json(['has_historial' => $hasHistorial]);
    }

    /**
     * Cambia el estado del alumno y registra en el historial.
     */
    public function updateStatus($id)
    {
        $alumno = Alumno::findOrFail($id);
        
        // Alternar estado y definir observación
        if ($alumno->status == 1) {
            $alumno->status = 0;
            $observacion = "Inactivacion";
            $msg = "Inactivo";
        } else {
            $alumno->status = 1;
            $observacion = "Activacion";
            $msg = "Activo";
        }

        $alumno->save();

        // Graba en AlumnoHistoriale.php
        AlumnoHistoriale::create([
            'alumno_id'     => $alumno->id,
            'grado_id'      => $alumno->grado_id,
            'tipo'          => '',
            'aprobo'        => '',
            'fecha'         => date('Y-m-d'),
            'observaciones' => $observacion,
        ]);

        return back()->with(['message' => "El estado del alumno se cambió a $msg.", 'alert-type' => 'success']);
    }
}
