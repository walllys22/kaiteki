<?php

namespace App\Http\Controllers;

use App\Models\AlumnoHorario;
use Illuminate\Http\Request;

class AlumnoHorarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'alumno_id'  => 'required|exists:alumnos,id',
            'horario_id' => 'required|exists:horarios,id',
            'observacion' => 'nullable|string|max:500',
        ]);

        $alumnoId  = (int) $request->alumno_id;
        $horarioId = (int) $request->horario_id;
        $this->ensureAlumnoActivo($alumnoId, 'El alumno esta inactivo. No se puede asignar horario.');

        $yaAsignado = AlumnoHorario::where('alumno_id', $alumnoId)
            ->where('horario_id', $horarioId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->exists();

        if ($yaAsignado) {
            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'El alumno ya tiene ese horario asignado actualmente.', 'alert-type' => 'error']);
        }

        try {
            // Dar de baja todos los horarios activos anteriores
            AlumnoHorario::where('alumno_id', $alumnoId)
                ->where('status', '1')
                ->whereNull('deleted_at')
                ->update(['status' => '0']);

            AlumnoHorario::create([
                'alumno_id'  => $alumnoId,
                'horario_id' => $horarioId,
                'status'     => '1',
                'observacion' => $request->observacion,
            ]);

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'Horario asignado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(int $id)
    {
        $this->custom_authorize('delete_alumnos');

        $alumnoHorario = AlumnoHorario::with('alumno')->findOrFail($id);
        $alumnoId = $alumnoHorario->alumno_id;
        $this->ensureAlumnoActivo($alumnoHorario->alumno, 'El alumno esta inactivo. No se puede eliminar horario.');

        if ($alumnoHorario->status != '1') {
            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'No se puede eliminar un horario inactivo.', 'alert-type' => 'error']);
        }

        try {
            $alumnoHorario->delete();

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'Horario eliminado del alumno.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }
}
