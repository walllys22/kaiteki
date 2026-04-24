<?php

namespace App\Http\Controllers;
use App\Models\Alumno;
use App\Models\Grado;
use App\Models\AlumnoHistoriale;
use App\Models\ArancelAlumno;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Muestra los pagos del alumno.
     */
    public function showPagos($id)
    {
        $this->custom_authorize('read_alumnos');

        // Buscamos el alumno con su relación de persona y dojo
        $dataTypeContent = Alumno::with(['person', 'dojo'])->findOrFail($id);
        
        return view('alumnos.pagos.browse', compact('dataTypeContent'));
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
     * Obtiene la lista filtrada de aranceles (pagos) para la petición AJAX.
     */
    public function arancelAlumnoList(Request $request, $id)
    {
        $this->custom_authorize('read_alumnos');

        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);

        $aranceles = ArancelAlumno::where('alumno_id', $id)
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('monto', 'like', "%$search%")
                          ->orWhereHas('arancel', function($q) use ($search) {
                              $q->where('nombre', 'like', "%$search%");
                          });
                }
            })
            ->with(['arancel'])
            ->orderBy('id', 'desc')
            ->paginate($paginate);

        return view('alumnos.pagos.list', compact('aranceles'));
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
            'grado_id'      => null,
            'tipo'          => '',
            'aprobo'        => '',
            'fecha'         => date('Y-m-d'),
            'observaciones' => $observacion,
        ]);

        return back()->with(['message' => "El estado del alumno se cambió a $msg.", 'alert-type' => 'success']);
    }

    /**
     * Verifica si una persona ya está registrada como alumno activo o en la tabla de Dojos.
     */
    public function checkRegistration(Request $request, $person_id)
    {
        $exclude_id = $request->id; // Obtenemos el ID actual si estamos editando
        $dojo_id = $request->dojo_id; // El Dojo seleccionado en el formulario
    
        // 1. Verificamos si la persona ya tiene un registro en el Dojo seleccionado.
        // Independientemente de si está activo o inactivo, bloqueamos para evitar duplicados en el mismo Dojo.
        $alumnoMismoDojo = Alumno::with('dojo')
            ->where('person_id', $person_id)
            ->where('dojo_id', $dojo_id)
            ->when($exclude_id, function ($q) use ($exclude_id) {
                return $q->where('id', '!=', $exclude_id);
            })
            ->first();
    
        if ($alumnoMismoDojo) {
            return response()->json(['status' => 'exists', 'dojo' => $alumnoMismoDojo->dojo->nombre ?? 'N/A']);
        }

        // 2. Verificamos si el alumno está ACTIVO (status = 1) en cualquier otro dojo.
        // Si el alumno está activo en otro lugar, bloqueamos según el requerimiento.
        $alumnoActivoOtroDojo = Alumno::with('dojo')
            ->where('person_id', $person_id)
            ->where('status', 1)
            ->when($exclude_id, function ($q) use ($exclude_id) {
                return $q->where('id', '!=', $exclude_id);
            })
            ->first();

        if ($alumnoActivoOtroDojo) {
            return response()->json(['status' => 'other_dojo', 'dojo' => $alumnoActivoOtroDojo->dojo->nombre ?? 'N/A']);
        }

        // 3. NUEVA REGLA: Verificar si la persona es responsable del Dojo seleccionado.
        // Un responsable de un Dojo no puede ser alumno en su propio Dojo.
        $dojoResponsable = \App\Models\Dojo::where('person_id', $person_id)
                                            ->where('id', $dojo_id)
                                            ->first();

        if ($dojoResponsable) {
            return response()->json(['status' => 'responsible_same_dojo', 'dojo' => $dojoResponsable->nombre ?? 'N/A']);
        }
        
        // Nota: Si el alumno está inactivo (status = 0) en otros dojos, o si es encargado en la tabla Dojo,
        // la validación permitirá el registro devolviendo 'ok'.
        return response()->json(['status' => 'ok']);
    }

    /**
     * Genera un reporte en PDF de los alumnos filtrados.
     */
    public function print(Request $request)
    {
        $this->custom_authorize('read_alumnos');

        $search = $request->input('search');
        $dojo_id = $request->input('dojo_id');

        $query = Alumno::with(['person', 'dojo', 'grado', 'horario']);

        if ($dojo_id) {
            $query->where('dojo_id', $dojo_id);
        }

        if ($search) {
            $query->whereHas('person', function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%");
            });
        }

        $alumnos = $query->orderBy('id', 'desc')->get();
        $dojo = $dojo_id ? \App\Models\Dojo::find($dojo_id) : null;

        // Si no hay dojo seleccionado pero los resultados pertenecen a uno solo, lo cargamos para el logo
        if (!$dojo && $alumnos->isNotEmpty() && $alumnos->pluck('dojo_id')->unique()->count() === 1) {
            $dojo = $alumnos->first()->dojo;
        }


        return view('alumnos.print', compact('alumnos', 'search', 'dojo'));
    }
}
