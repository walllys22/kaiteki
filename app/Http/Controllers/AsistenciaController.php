<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\AsistenciaAlumno;
use App\Models\AlumnoHorario;
use App\Models\Horario;
use App\Models\Dojo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_asistencias');
        return view('asistencias.browse');
    }

    public function list()
    {
        $this->custom_authorize('browse_asistencias');

        $search     = request('search');
        $paginate   = request('paginate', 10);
        $userDojoId = auth()->user()->dojo_id;

        $data = Asistencia::with(['dojo', 'horario', 'detalles', 'register'])
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->whereHas('horario', fn($hq) => $hq->where('nombre', 'like', "%$search%")
                            ->orWhere('tipo', 'like', "%$search%"))
                        ->orWhere('fecha', 'like', "%$search%");
                });
            })
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($paginate);

        return view('asistencias.list', compact('data'));
    }

    public function create()
    {
        $this->custom_authorize('add_asistencias');

        $userDojoId = auth()->user()->dojo_id;

        $dojos = $userDojoId
            ? Dojo::where('id', $userDojoId)->whereNull('deleted_at')->get()
            : Dojo::whereNull('deleted_at')->orderBy('nombre')->get();

        $horarios = Horario::with('dojo')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->orderBy('nombre')
            ->get();

        return view('asistencias.create', compact('dojos', 'horarios', 'userDojoId'));
    }

    public function loadAlumnos(Request $request)
    {
        $this->custom_authorize('add_asistencias');

        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha'      => 'required|date',
            'dojo_id'    => 'required|exists:dojos,id',
        ]);

        $userDojoId = auth()->user()->dojo_id;
        // Branch user: always use their own dojo; global admin: use submitted dojo_id
        $dojoId    = $userDojoId ?? $request->dojo_id;
        $horarioId = $request->horario_id;
        $fecha     = $request->fecha;

        $yaExiste = Asistencia::where('horario_id', $horarioId)
            ->where('fecha', $fecha)
            ->where('dojo_id', $dojoId)
            ->whereNull('deleted_at')
            ->exists();

        if ($yaExiste) {
            return response()->json(['error' => 'Ya existe una lista de asistencia para ese horario y fecha.'], 422);
        }

        $alumnos = AlumnoHorario::with(['alumno.person'])
            ->where('horario_id', $horarioId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->whereHas('alumno', function ($q) use ($dojoId) {
                $q->where('status', 1)
                  ->whereNull('deleted_at')
                  ->where('dojo_id', $dojoId);
            })
            ->get()
            ->map(fn($ah) => $ah->alumno)
            ->filter()
            ->sortBy(fn($a) => optional($a->person)->first_name)
            ->values();

        return response()->json(['alumnos' => $alumnos->map(fn($a) => [
            'id'         => $a->id,
            'first_name' => optional($a->person)->first_name ?? 'Sin nombre',
        ])]);
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_asistencias');

        $request->validate([
            'horario_id'   => 'required|exists:horarios,id',
            'fecha'        => 'required|date',
            'dojo_id'      => 'required|exists:dojos,id',
            'observacion'  => 'nullable|string|max:500',
            'estados'      => 'required|array|min:1',
            'estados.*'    => 'required|in:asistencia,licencia,falta',
        ]);

        $userDojoId = auth()->user()->dojo_id;
        $dojoId     = $userDojoId ?? $request->dojo_id;

        $yaExiste = Asistencia::where('horario_id', $request->horario_id)
            ->where('fecha', $request->fecha)
            ->where('dojo_id', $dojoId)
            ->whereNull('deleted_at')
            ->exists();

        if ($yaExiste) {
            return redirect()->back()->withInput()
                ->with(['message' => 'Ya existe una lista de asistencia para ese horario y fecha.', 'alert-type' => 'error']);
        }

        $alumnoIds = collect(array_keys($request->estados))->map(fn($id) => (int) $id)->unique()->values();
        $alumnosActivosDelHorario = AlumnoHorario::query()
            ->where('horario_id', $request->horario_id)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->whereIn('alumno_id', $alumnoIds)
            ->whereHas('alumno', function ($query) use ($dojoId) {
                $query->where('dojo_id', $dojoId)
                    ->where('status', 1)
                    ->whereNull('deleted_at');
            })
            ->distinct('alumno_id')
            ->count('alumno_id');

        if ($alumnosActivosDelHorario !== $alumnoIds->count()) {
            return redirect()->back()->withInput()
                ->with(['message' => 'La asistencia solo puede registrarse para alumnos activos del horario seleccionado.', 'alert-type' => 'error']);
        }

        try {
            DB::beginTransaction();

            $asistencia = Asistencia::create([
                'dojo_id'     => $dojoId,
                'horario_id'  => $request->horario_id,
                'fecha'       => $request->fecha,
                'observacion' => $request->observacion,
            ]);

            foreach ($request->estados as $alumnoId => $estado) {
                AsistenciaAlumno::create([
                    'asistencia_id' => $asistencia->id,
                    'alumno_id'     => $alumnoId,
                    'estado'        => $estado,
                ]);
            }

            DB::commit();

            return redirect()->route('voyager.asistencias.index')
                ->with(['message' => 'Asistencia registrada exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function show(int $id)
    {
        $this->custom_authorize('read_asistencias');

        $userDojoId  = auth()->user()->dojo_id;
        $asistencia  = Asistencia::with(['dojo', 'horario', 'detalles.alumno.person', 'register'])
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return view('asistencias.read', compact('asistencia'));
    }

    public function update(Request $request, int $id)
    {
        $this->custom_authorize('edit_asistencias');

        $request->validate([
            'estados'   => 'required|array|min:1',
            'estados.*' => 'required|in:asistencia,licencia,falta',
        ]);

        $userDojoId = auth()->user()->dojo_id;
        $asistencia = Asistencia::when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $detalleIds = collect(array_keys($request->estados))->map(fn($id) => (int) $id)->unique()->values();
        $hayAlumnoInactivo = AsistenciaAlumno::query()
            ->where('asistencia_id', $asistencia->id)
            ->whereIn('id', $detalleIds)
            ->whereHas('alumno', function ($query) {
                $query->where('status', '!=', 1)
                    ->orWhereNotNull('deleted_at');
            })
            ->exists();

        if ($hayAlumnoInactivo) {
            return redirect()->back()
                ->with(['message' => 'No se puede modificar asistencia de alumnos inactivos. Solo visualizacion.', 'alert-type' => 'error']);
        }

        foreach ($request->estados as $detalleId => $estado) {
            AsistenciaAlumno::where('id', $detalleId)
                ->where('asistencia_id', $asistencia->id)
                ->update(['estado' => $estado]);
        }

        return redirect()->route('voyager.asistencias.show', $asistencia->id)
            ->with(['message' => 'Asistencia actualizada correctamente.', 'alert-type' => 'success']);
    }

    public function destroy(int $id)
    {
        $this->custom_authorize('delete_asistencias');

        $asistencia = Asistencia::findOrFail($id);

        try {
            $asistencia->delete();
            return redirect()->route('voyager.asistencias.index')
                ->with(['message' => 'Asistencia eliminada.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }
}
