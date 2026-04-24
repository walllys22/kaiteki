<?php

namespace App\Http\Controllers;

use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoRepaso;
use App\Models\AlumnoGradoExamen;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlumnoGradoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Calcula el progreso del grado activo (requiere eager load de repasos y examenes).
     */
    public static function calcularProgreso(AlumnoGrado $alumnoGrado): array
    {
        $grado = $alumnoGrado->grado;
        $puntasRequeridas = $grado ? (int) $grado->puntas : 0;
        $diasRequeridos   = $grado ? (int) $grado->dias   : 0;

        $repasos         = $alumnoGrado->repasos;
        $examenes        = $alumnoGrado->examenes;
        $puntasObtenidas = $repasos->where('aprobado', 1)->count();
        $diasTranscurridos = (int) Carbon::parse($alumnoGrado->fecha)->diffInDays(Carbon::now());

        $cumplePuntas   = $puntasObtenidas >= $puntasRequeridas;
        $cumpleDias     = $diasTranscurridos >= $diasRequeridos;
        $puedeExamen    = $cumplePuntas;   // solo puntas habilitan el examen
        $examenAprobado = $examenes->where('aprobado', 1)->count() > 0;
        $isComplete     = $puedeExamen && $examenAprobado;

        return compact(
            'puntasRequeridas',
            'diasRequeridos',
            'puntasObtenidas',
            'diasTranscurridos',
            'cumplePuntas',
            'cumpleDias',
            'puedeExamen',
            'examenAprobado',
            'isComplete'
        );
    }

    // ─── Nuevo Grado ──────────────────────────────────────────────────────────

    public function storeGrado(Request $request)
    {
        $this->custom_authorize('add_alumnos');

        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'grado_id'  => 'required|exists:grados,id',
            'fecha'     => 'required|date',
            'observacion' => 'nullable|string|max:500',
        ]);

        $alumnoId = (int) $request->alumno_id;

        $activeGrado = AlumnoGrado::with(['grado', 'repasos', 'examenes'])
            ->where('alumno_id', $alumnoId)
            ->where(function ($q) { $q->whereNull('status')->orWhere('status', '0'); })
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($activeGrado) {
            $progress = self::calcularProgreso($activeGrado);

            if (!$progress['isComplete']) {
                return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                    ->with(['message' => 'El alumno tiene un grado en progreso que aún no fue completado. Debe cumplir las puntas, los días requeridos y aprobar el examen final.', 'alert-type' => 'error']);
            }

            // Marcar el grado anterior como completado
            $activeGrado->status = '1';
            $activeGrado->save();
        }

        try {
            AlumnoGrado::create([
                'alumno_id'   => $alumnoId,
                'grado_id'    => $request->grado_id,
                'fecha'       => $request->fecha,
                'observacion' => $request->observacion,
                'status'      => '0',
            ]);

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'Grado registrado exitosamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Repasos (Puntas) ─────────────────────────────────────────────────────

    public function storeRepaso(Request $request)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'alumno_grado_id' => 'required|exists:alumno_grados,id',
            'fecha'           => 'required|date',
            'aprobado'        => 'required|in:0,1',
            'observacion'     => 'nullable|string|max:500',
        ]);

        $alumnoGrado = AlumnoGrado::with(['grado', 'repasos', 'examenes'])->findOrFail($request->alumno_grado_id);

        if ($alumnoGrado->isCompletado()) {
            return redirect()->back()
                ->with(['message' => 'No se puede agregar un repaso a un grado ya completado.', 'alert-type' => 'error']);
        }

        // Bloquear si ya se alcanzó la cantidad de puntas aprobadas requeridas
        $progress = self::calcularProgreso($alumnoGrado);
        if ($progress['cumplePuntas']) {
            return redirect()->back()
                ->with(['message' => 'Ya se cumplió con la cantidad de puntas requeridas. Debe rendir el examen final.', 'alert-type' => 'warning']);
        }

        try {
            AlumnoGradoRepaso::create([
                'alumno_grado_id' => $request->alumno_grado_id,
                'fecha'           => $request->fecha,
                'aprobado'        => $request->aprobado,
                'observacion'     => $request->observacion,
            ]);

            $msg = $request->aprobado == 1 ? 'Repaso registrado como punta aprobada.' : 'Repaso registrado (no aprobado).';

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoGrado->alumno_id])
                ->with(['message' => $msg, 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroyRepaso($id)
    {
        $this->custom_authorize('delete_alumnos');
        $repaso = AlumnoGradoRepaso::findOrFail($id);
        $alumnoId = AlumnoGrado::findOrFail($repaso->alumno_grado_id)->alumno_id;

        try {
            $repaso->delete();

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'Repaso eliminado.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Examen Final ─────────────────────────────────────────────────────────

    public function storeExamen(Request $request)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'alumno_grado_id' => 'required|exists:alumno_grados,id',
            'fecha'           => 'required|date',
            'aprobado'        => 'required|in:0,1',
            'observacion'     => 'nullable|string|max:500',
        ]);

        $alumnoGrado = AlumnoGrado::with(['grado', 'repasos', 'examenes'])->findOrFail($request->alumno_grado_id);

        if ($alumnoGrado->isCompletado()) {
            return redirect()->back()
                ->with(['message' => 'El grado ya fue completado.', 'alert-type' => 'error']);
        }

        $progress = self::calcularProgreso($alumnoGrado);

        if (!$progress['puedeExamen']) {
            $faltan = $progress['puntasRequeridas'] - $progress['puntasObtenidas'];
            return redirect()->back()
                ->with(['message' => "No puede rendir el examen final aún: faltan {$faltan} punta(s) aprobada(s).", 'alert-type' => 'error']);
        }

        try {
            AlumnoGradoExamen::create([
                'alumno_grado_id' => $request->alumno_grado_id,
                'fecha'           => $request->fecha,
                'aprobado'        => $request->aprobado,
                'observacion'     => $request->observacion,
            ]);

            if ($request->aprobado == 1) {
                $alumnoGrado->status = '1';
                $alumnoGrado->save();

                $msg = 'Examen aprobado. El grado ha sido completado.';
                $type = 'success';
            } else {
                $msg = 'Examen aplazado. El alumno puede volver a rendirlo.';
                $type = 'warning';
            }

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoGrado->alumno_id])
                ->with(['message' => $msg, 'alert-type' => $type]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroyExamen($id)
    {
        $this->custom_authorize('delete_alumnos');
        $examen = AlumnoGradoExamen::findOrFail($id);
        $alumnoGrado = AlumnoGrado::findOrFail($examen->alumno_grado_id);
        $alumnoId    = $alumnoGrado->alumno_id;
        $eraAprobado = (bool) $examen->aprobado;

        try {
            $examen->delete();

            // Si el examen eliminado era el que aprobó el grado, revertir estado
            if ($eraAprobado && $alumnoGrado->isCompletado()) {
                $hayOtroAprobado = AlumnoGradoExamen::where('alumno_grado_id', $alumnoGrado->id)
                    ->whereNull('deleted_at')
                    ->where('aprobado', 1)
                    ->exists();

                if (!$hayOtroAprobado) {
                    $alumnoGrado->status = '0';
                    $alumnoGrado->save();
                }
            }

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'Examen eliminado.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }
}
