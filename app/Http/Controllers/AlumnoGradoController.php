<?php

namespace App\Http\Controllers;

use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoRepaso;
use App\Models\AlumnoGradoExamen;
use App\Models\Arancele;
use App\Models\AsistenciaAlumno;
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
        $diasTranscurridos = AsistenciaAlumno::where('alumno_id', $alumnoGrado->alumno_id)
            ->where('estado', 'asistencia')
            ->whereHas('asistencia', fn($q) => $q->whereNull('deleted_at')
                ->where('fecha', '>=', $alumnoGrado->fecha))
            ->count();

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

        // Validar que el alumno no tenga este grado ya registrado
        $gradoYaRegistrado = AlumnoGrado::where('alumno_id', $alumnoId)
            ->where('grado_id', $request->grado_id)
            ->whereNull('deleted_at')
            ->exists();

        if ($gradoYaRegistrado) {
            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'El alumno ya tiene este grado registrado.', 'alert-type' => 'error']);
        }

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
                    ->with(['message' => 'El alumno tiene un grado en progreso que aún no fue completado. Debe cumplir las puntas y aprobar el examen final.', 'alert-type' => 'error']);
            }

            // Marcar el grado anterior como completado (caso borde)
            $activeGrado->status = '1';
            $activeGrado->save();
        }

        // Validar fecha contra el último grado completado (independiente del flujo de arriba,
        // porque storeExamen() ya marca el grado como status='1' antes de llegar aquí)
        $ultimoCompletado = AlumnoGrado::where('alumno_id', $alumnoId)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($ultimoCompletado) {
            $fechaExamen = AlumnoGradoExamen::where('alumno_grado_id', $ultimoCompletado->id)
                ->where('aprobado', 1)
                ->whereNull('deleted_at')
                ->orderByDesc('fecha')
                ->value('fecha');

            if ($fechaExamen && $request->fecha < $fechaExamen) {
                $fechaFormateada = Carbon::parse($fechaExamen)->format('d/m/Y');
                return redirect()->back()
                    ->withInput()
                    ->with(['message' => "La fecha de inicio no puede ser anterior al examen final aprobado ({$fechaFormateada}).", 'alert-type' => 'error']);
            }
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
            'monto'           => 'required|numeric|min:0|max:99999999.99',
            'estado_pago'     => 'required|in:pendiente,pagado',
            'observacion'     => 'nullable|string|max:500',
        ]);

        $alumnoGrado = AlumnoGrado::with(['alumno', 'grado', 'repasos', 'examenes'])->findOrFail($request->alumno_grado_id);

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

        // La fecha debe ser posterior al inicio del grado
        if ($request->fecha <= $alumnoGrado->fecha) {
            $fechaFormateada = Carbon::parse($alumnoGrado->fecha)->format('d/m/Y');
            return redirect()->back()
                ->withInput()
                ->with(['message' => "La fecha del repaso debe ser posterior al inicio del grado ({$fechaFormateada}).", 'alert-type' => 'error']);
        }

        // La fecha debe ser posterior al último repaso registrado
        $ultimoRepaso = AlumnoGradoRepaso::where('alumno_grado_id', $alumnoGrado->id)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->value('fecha');

        if ($ultimoRepaso && $request->fecha <= $ultimoRepaso) {
            $fechaFormateada = Carbon::parse($ultimoRepaso)->format('d/m/Y');
            return redirect()->back()
                ->withInput()
                ->with(['message' => "La fecha del repaso debe ser posterior al último repaso ({$fechaFormateada}).", 'alert-type' => 'error']);
        }

        // La fecha también debe ser posterior al último examen registrado
        $ultimoExamen = AlumnoGradoExamen::where('alumno_grado_id', $alumnoGrado->id)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->value('fecha');

        if ($ultimoExamen && $request->fecha <= $ultimoExamen) {
            $fechaFormateada = Carbon::parse($ultimoExamen)->format('d/m/Y');
            return redirect()->back()
                ->withInput()
                ->with(['message' => "La fecha del repaso debe ser posterior al último examen ({$fechaFormateada}).", 'alert-type' => 'error']);
        }

        $dojoId = optional($alumnoGrado->alumno)->dojo_id;

        if (!$dojoId) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'El alumno no tiene un dojo asignado para calcular el arancel del repaso.', 'alert-type' => 'error']);
        }

        $arancelRepaso = Arancele::query()
            ->where('grado_id', $alumnoGrado->grado_id)
            ->where('dojo_id', $dojoId)
            ->where('tipo', 'Repaso')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$arancelRepaso) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Debe registrar un arancel activo de tipo Repaso para este grado y dojo antes de agregar la punta.', 'alert-type' => 'error']);
        }

        try {
            $monto = (float) $request->monto;

            $repaso = AlumnoGradoRepaso::create([
                'alumno_grado_id' => $request->alumno_grado_id,
                'arancel_id'      => $arancelRepaso->id,
                'fecha'           => $request->fecha,
                'aprobado'        => $request->aprobado,
                'monto'           => $monto,
                'monto_pagado'    => $request->estado_pago === 'pagado' ? $monto : 0,
                'observacion'     => $request->observacion,
            ]);

            $msg = $request->aprobado == 1 ? 'Repaso registrado como punta aprobada.' : 'Repaso registrado (no aprobado).';

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoGrado->alumno_id])
                ->with([
                    'message' => $request->estado_pago === 'pagado' ? $msg . ' Pago registrado.' : $msg . ' El pago quedó pendiente.',
                    'alert-type' => 'success',
                ]);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function comprobanteRepaso(int $id)
    {
        $this->custom_authorize('read_alumnos');

        $userDojoId = auth()->user()->dojo_id;

        $repaso = AlumnoGradoRepaso::with([
            'arancel',
            'alumnoGrado.grado',
            'alumnoGrado.alumno.person',
            'alumnoGrado.alumno.dojo',
        ])
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->whereHas('alumnoGrado.alumno', function ($alumnoQuery) use ($userDojoId) {
                    $alumnoQuery->where('dojo_id', $userDojoId);
                });
            })
            ->findOrFail($id);

        $monto = (float) ($repaso->monto ?? 0);
        $pagado = (float) ($repaso->monto_pagado ?? 0);

        if ($monto <= 0 || $pagado < $monto) {
            return redirect()->route('voyager.alumnos.show', ['id' => $repaso->alumnoGrado->alumno_id])
                ->with(['message' => 'El comprobante solo se puede imprimir cuando la punta está pagada.', 'alert-type' => 'warning']);
        }

        return view('alumnos.partials.comprobantePunta', compact('repaso'));
    }

    public function pagarRepaso(Request $request, int $id)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'monto' => 'required|numeric|min:0.01|max:99999999.99',
        ]);

        $userDojoId = auth()->user()->dojo_id;

        $repaso = AlumnoGradoRepaso::with(['alumnoGrado.alumno'])
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->whereHas('alumnoGrado.alumno', function ($alumnoQuery) use ($userDojoId) {
                    $alumnoQuery->where('dojo_id', $userDojoId);
                });
            })
            ->findOrFail($id);

        $monto = (float) $request->monto;

        try {
            $repaso->monto = $monto;
            $repaso->monto_pagado = $monto;
            $repaso->save();

            return redirect()->route('voyager.alumnos.show', ['id' => $repaso->alumnoGrado->alumno_id])
                ->with(['message' => 'Pago de la punta registrado correctamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroyRepaso(int $id)
    {
        $this->custom_authorize('delete_alumnos');
        $repaso = AlumnoGradoRepaso::findOrFail($id);
        $alumnoId = AlumnoGrado::findOrFail($repaso->alumno_grado_id)->alumno_id;

        $ultimoRepaso = AlumnoGradoRepaso::where('alumno_grado_id', $repaso->alumno_grado_id)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->value('id');

        if ($ultimoRepaso !== $repaso->id) {
            return redirect()->back()
                ->with(['message' => 'Solo se puede eliminar el repaso más reciente.', 'alert-type' => 'error']);
        }

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

        // Calcular la fecha mínima permitida: máximo entre inicio del grado, último repaso y último examen
        $ultimoRepaso = AlumnoGradoRepaso::where('alumno_grado_id', $request->alumno_grado_id)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->value('fecha');

        $ultimoExamen = AlumnoGradoExamen::where('alumno_grado_id', $request->alumno_grado_id)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->value('fecha');

        $fechaMinima = max(array_filter([$alumnoGrado->fecha, $ultimoRepaso, $ultimoExamen]));

        if ($request->fecha <= $fechaMinima) {
            $fechaFormateada = Carbon::parse($fechaMinima)->format('d/m/Y');
            $referencia = match(true) {
                $fechaMinima === $ultimoExamen => "examen anterior ({$fechaFormateada})",
                $fechaMinima === $ultimoRepaso => "último repaso ({$fechaFormateada})",
                default                        => "inicio del grado ({$fechaFormateada})",
            };
            return redirect()->back()
                ->withInput()
                ->with(['message' => "La fecha del examen debe ser posterior al {$referencia}.", 'alert-type' => 'error']);
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

    public function destroyExamen(int $id)
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
