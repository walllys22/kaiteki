<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoMensualidad;
use App\Models\AlumnoMensualidadPago;
use App\Models\AlumnoMensualidadPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoMensualidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list(int $alumnoId)
    {
        $this->custom_authorize('read_alumnos');

        $search = request('search');
        $paginate = request('paginate') ?? 10;
        $alumno = $this->findAlumno($alumnoId);
        $plan = AlumnoMensualidadPlan::query()
            ->where('alumno_id', $alumno->id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($plan && (int) $alumno->status === 1) {
            $this->generarMensualidades($alumno, $plan);
        }

        $mensualidadesQuery = AlumnoMensualidad::with(['pagos' => function ($query) {
                $query->whereNull('deleted_at')->orderByDesc('fecha')->orderByDesc('id');
            }])
            ->where('alumno_id', $alumno->id)
            ->whereNull('deleted_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('periodo', 'like', "%$search%")
                        ->orWhere('status', 'like', "%$search%")
                        ->orWhere('observacion', 'like', "%$search%");
                });
            });

        $mensualidadesResumen = (clone $mensualidadesQuery)->get();
        $periodosPendientes = AlumnoMensualidad::query()
            ->where('alumno_id', $alumno->id)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'anulado')
            ->orderBy('periodo')
            ->get()
            ->filter(fn($item) => $item->saldo() > 0)
            ->pluck('periodo')
            ->values();

        $resumen = [
            'total' => $mensualidadesResumen->sum(fn($item) => $item->total()),
            'pagado' => $mensualidadesResumen->sum('monto_pagado'),
        ];
        $resumen['saldo'] = max(0, $resumen['total'] - $resumen['pagado']);

        $data = $mensualidadesQuery
            ->orderByDesc('periodo')
            ->paginate($paginate);

        return view('alumnos.mensualidades.list', compact('alumno', 'plan', 'data', 'resumen', 'periodosPendientes'));
    }

    public function storePlan(Request $request)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'monto_mensual' => 'required|numeric|min:0|max:99999999.99',
            'descuento' => 'nullable|numeric|min:0|max:99999999.99',
            'beca' => 'nullable|numeric|min:0|max:99999999.99',
            'fecha_inicio' => 'required|date',
            'observacion' => 'nullable|string|max:500',
        ]);

        $alumno = $this->findAlumno((int) $request->alumno_id);
        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();

        if ((float) $request->descuento + (float) $request->beca > (float) $request->monto_mensual) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'La suma de descuento y beca no puede ser mayor a la mensualidad.', 'alert-type' => 'error']);
        }

        try {
            DB::transaction(function () use ($request, $alumno, $fechaInicio) {
                AlumnoMensualidadPlan::query()
                    ->where('alumno_id', $alumno->id)
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->update(['status' => 0]);

                $plan = AlumnoMensualidadPlan::create([
                    'alumno_id' => $alumno->id,
                    'dojo_id' => $alumno->dojo_id,
                    'monto_mensual' => (float) $request->monto_mensual,
                    'descuento' => (float) ($request->descuento ?? 0),
                    'beca' => (float) ($request->beca ?? 0),
                    'fecha_inicio' => $fechaInicio->toDateString(),
                    'observacion' => $request->observacion,
                    'status' => 1,
                ]);

                if ((int) $alumno->status === 1) {
                    $this->generarMensualidades($alumno, $plan);
                }
            });

            return redirect()->route('voyager.alumnos.show', ['id' => $alumno->id])
                ->with(['message' => 'Configuración de mensualidad guardada correctamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function pagar(Request $request, int $id)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0.01|max:99999999.99',
            'observacion' => 'nullable|string|max:500',
        ]);

        $mensualidad = $this->findMensualidad($id);

        if ($mensualidad->status === 'anulado') {
            return redirect()->back()
                ->with(['message' => 'No se puede registrar pago sobre una mensualidad anulada.', 'alert-type' => 'error']);
        }

        $mensualidadAnteriorPendiente = AlumnoMensualidad::query()
            ->where('alumno_id', $mensualidad->alumno_id)
            ->whereNull('deleted_at')
            ->whereDate('periodo', '<', $mensualidad->periodo)
            ->where('status', '!=', 'anulado')
            ->get()
            ->first(fn($item) => $item->saldo() > 0);

        if ($mensualidadAnteriorPendiente) {
            $periodo = Carbon::parse($mensualidadAnteriorPendiente->periodo)->format('d/m/Y');
            return redirect()->back()
                ->with(['message' => "Debe pagar primero la mensualidad anterior pendiente ({$periodo}).", 'alert-type' => 'error']);
        }

        try {
            DB::transaction(function () use ($request, $mensualidad) {
                AlumnoMensualidadPago::create([
                    'alumno_mensualidad_id' => $mensualidad->id,
                    'alumno_id' => $mensualidad->alumno_id,
                    'dojo_id' => $mensualidad->dojo_id,
                    'fecha' => $request->fecha,
                    'monto' => (float) $request->monto,
                    'observacion' => $request->observacion,
                ]);

                $mensualidad->monto_pagado = (float) $mensualidad->monto_pagado + (float) $request->monto;
                $mensualidad->status = $this->resolverStatus($mensualidad);
                $mensualidad->save();
            });

            return redirect()->route('voyager.alumnos.show', ['id' => $mensualidad->alumno_id])
                ->with(['message' => 'Pago de mensualidad registrado correctamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function mora(Request $request, int $id)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'mora' => 'required|numeric|min:0|max:99999999.99',
            'observacion' => 'nullable|string|max:500',
        ]);

        $mensualidad = $this->findMensualidad($id);

        if ($mensualidad->status === 'anulado') {
            return redirect()->back()
                ->with(['message' => 'No se puede agregar mora a una mensualidad anulada.', 'alert-type' => 'error']);
        }

        $mensualidad->mora = (float) $mensualidad->mora + (float) $request->mora;
        if ($request->observacion) {
            $mensualidad->observacion = trim(($mensualidad->observacion ? $mensualidad->observacion . "\n" : '') . $request->observacion);
        }
        $mensualidad->status = $this->resolverStatus($mensualidad);
        $mensualidad->save();

        return redirect()->route('voyager.alumnos.show', ['id' => $mensualidad->alumno_id])
            ->with(['message' => 'Mora agregada correctamente.', 'alert-type' => 'success']);
    }

    public function comprobantePago(int $id)
    {
        $this->custom_authorize('read_alumnos');

        $userDojoId = auth()->user()->dojo_id;

        $pago = AlumnoMensualidadPago::with([
            'mensualidad.plan',
            'alumno.person',
            'alumno.dojo',
        ])
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);

        return view('alumnos.mensualidades.comprobantePago', compact('pago'));
    }

    private function generarMensualidades(Alumno $alumno, AlumnoMensualidadPlan $plan): void
    {
        $periodo = Carbon::parse($plan->fecha_inicio)->startOfDay();
        $periodoFinal = now()->startOfDay();

        while ($periodo <= $periodoFinal) {
            $exists = AlumnoMensualidad::query()
                ->where('alumno_id', $alumno->id)
                ->whereDate('periodo', $periodo->toDateString())
                ->exists();

            if (!$exists) {
                $mensualidad = new AlumnoMensualidad([
                    'alumno_id' => $alumno->id,
                    'dojo_id' => $alumno->dojo_id,
                    'alumno_mensualidad_plan_id' => $plan->id,
                    'periodo' => $periodo->toDateString(),
                    'monto' => (float) $plan->monto_mensual,
                    'descuento' => (float) $plan->descuento,
                    'beca' => (float) $plan->beca,
                    'mora' => 0,
                    'monto_pagado' => 0,
                    'observacion' => $plan->observacion,
                    'status' => 'pendiente',
                ]);
                $mensualidad->status = $this->resolverStatus($mensualidad);
                $mensualidad->save();
            }

            $periodo->addMonthNoOverflow();
        }
    }

    private function findAlumno(int $id): Alumno
    {
        $userDojoId = auth()->user()->dojo_id;

        return Alumno::query()
            ->with('dojo')
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);
    }

    private function findMensualidad(int $id): AlumnoMensualidad
    {
        $userDojoId = auth()->user()->dojo_id;

        return AlumnoMensualidad::query()
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);
    }

    private function resolverStatus(AlumnoMensualidad $mensualidad): string
    {
        if ($mensualidad->status === 'anulado') {
            return 'anulado';
        }

        if ($mensualidad->total() <= 0) {
            return 'exonerado';
        }

        if ((float) $mensualidad->monto_pagado >= $mensualidad->total()) {
            return 'pagado';
        }

        if ((float) $mensualidad->monto_pagado > 0) {
            return 'parcial';
        }

        return 'pendiente';
    }
}
