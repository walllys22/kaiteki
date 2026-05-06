<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoEnfermedad;
use App\Models\AlumnoGrado;
use App\Models\Dojo;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_consulta');

        $userDojoId = auth()->user()->dojo_id;

        $dojos = Dojo::whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, fn($q) => $q->where('id', '!=', $userDojoId))
            ->orderBy('nombre')
            ->get();

        return view('consulta.browse', compact('dojos', 'userDojoId'));
    }

    public function searchAlumnos(Request $request)
    {
        $this->custom_authorize('browse_consulta');

        $request->validate([
            'dojo_id' => 'required|exists:dojos,id',
            'q'       => 'nullable|string|max:100',
        ]);

        $userDojoId = auth()->user()->dojo_id;

        if ($userDojoId && (int) $request->dojo_id === (int) $userDojoId) {
            return response()->json(['error' => 'No puede consultar su propio dojo.'], 403);
        }

        $q = $request->q;

        $alumnos = Alumno::with(['person', 'ultimoGrado.grado'])
            ->where('dojo_id', $request->dojo_id)
            ->whereNull('deleted_at')
            ->when($q, function ($query, $q) {
                $query->whereHas('person', fn($pq) =>
                    $pq->where('first_name', 'like', "%$q%")
                       ->orWhere('ci', 'like', "%$q%")
                );
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($alumnos->map(fn($a) => [
            'id'     => $a->id,
            'nombre' => optional($a->person)->first_name ?? 'Sin nombre',
            'ci'     => optional($a->person)->ci ?? '—',
            'status' => $a->status,
            'grado'  => ($a->ultimoGrado && $a->ultimoGrado->grado)
                ? trim(($a->ultimoGrado->grado->tipo ?? '') . ' ' . ($a->ultimoGrado->grado->numero ?? '') . ' ' . ($a->ultimoGrado->grado->nombre ?? ''))
                : 'Sin grado',
        ]));
    }

    public function show(int $id)
    {
        $this->custom_authorize('browse_consulta');

        $userDojoId = auth()->user()->dojo_id;

        $alumno = Alumno::with(['person', 'dojo'])
            ->whereNull('deleted_at')
            ->when($userDojoId, fn($q) => $q->where('dojo_id', '!=', $userDojoId))
            ->findOrFail($id);

        // Grado activo en progreso
        $activeGrado = AlumnoGrado::with(['grado', 'repasos', 'examenes'])
            ->where('alumno_id', $alumno->id)
            ->where(fn($q) => $q->whereNull('status')->orWhere('status', '0'))
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        $progress = null;
        if ($activeGrado) {
            $progress = AlumnoGradoController::calcularProgreso($activeGrado);
        }

        // Historial de grados completados
        $gradosCompletados = AlumnoGrado::with(['grado', 'repasos', 'examenes'])
            ->where('alumno_id', $alumno->id)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->get();

        // Condiciones de salud
        $enfermedades = AlumnoEnfermedad::where('alumno_id', $alumno->id)
            ->orderByDesc('id')
            ->get();

        return view('consulta.show', compact(
            'alumno',
            'activeGrado',
            'progress',
            'gradosCompletados',
            'enfermedades'
        ));
    }
}
