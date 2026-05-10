<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoMensualidad;
use App\Models\AlumnoMensualidadPago;
use App\Models\AlumnoMensualidadPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlumnoMensualidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('comprobantePagoPublic');
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
            'mora' => $mensualidadesResumen->sum('mora'),
            'descuento' => $mensualidadesResumen->sum('descuento'),
            'pendientes' => $mensualidadesResumen->filter(fn($item) => $item->estadoPago() === 'Pendiente')->count(),
            'parciales' => $mensualidadesResumen->filter(fn($item) => $item->estadoPago() === 'Parcial')->count(),
            'pagadas' => $mensualidadesResumen->filter(fn($item) => in_array($item->estadoPago(), ['Pagado', 'Exonerado']))->count(),
            'total_meses' => $mensualidadesResumen->count(),
        ];
        $resumen['saldo'] = max(0, $resumen['total'] - $resumen['pagado']);
        $resumen['deuda_mensualidad'] = $mensualidadesResumen->sum(function ($item) {
            return max(0, (float) $item->monto - (float) $item->descuento - (float) $item->monto_pagado);
        });
        $resumen['mora_pendiente'] = max(0, $resumen['saldo'] - $resumen['deuda_mensualidad']);

        $data = $mensualidadesQuery
            ->orderByDesc('periodo')
            ->paginate($paginate);

        $ultimaMensualidad = AlumnoMensualidad::with('plan')
            ->where('alumno_id', $alumno->id)
            ->whereNull('deleted_at')
            ->orderByDesc('periodo')
            ->orderByDesc('id')
            ->first();

        return view('alumnos.mensualidades.list', compact(
            'alumno',
            'plan',
            'data',
            'resumen',
            'periodosPendientes',
            'ultimaMensualidad'
        ));
    }

    public function storePlan(Request $request)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'monto_mensual' => 'required|numeric|min:0|max:99999999.99',
            'descuento' => 'nullable|numeric|min:0|max:99999999.99',
            'fecha_inicio' => 'required|date',
            'tipo_generacion' => 'required|in:automatica,fecha_fin',
            'fecha_fin' => 'nullable|required_if:tipo_generacion,fecha_fin|date',
            'observacion' => 'nullable|string|max:500',
        ]);

        $alumno = $this->findAlumno((int) $request->alumno_id);
        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFinPlan = $request->tipo_generacion === 'fecha_fin'
            ? Carbon::parse($request->fecha_fin)->startOfDay()
            : null;

        if ($request->tipo_generacion !== 'fecha_fin' && $request->filled('fecha_fin')) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'La fecha fin solo se puede usar cuando el tipo de generación es "Con fecha fin".', 'alert-type' => 'error']);
        }

        if ($fechaInicio->gt(now()->startOfDay())) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'message' => 'La mensualidad no puede iniciar con una fecha adelantada. Debe iniciar hoy o en una fecha anterior.',
                    'alert-type' => 'error',
                ]);
        }

        if ($fechaFinPlan) {
            if ($fechaFinPlan->lt($fechaInicio)) {
                return redirect()->back()
                    ->withInput()
                    ->with(['message' => 'La fecha fin no puede ser menor a la fecha de inicio.', 'alert-type' => 'error']);
            }
        }

        $planActivo = AlumnoMensualidadPlan::query()
            ->where('alumno_id', $alumno->id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->exists();

        if ($planActivo) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Debe pausar la mensualidad actual antes de configurar una nueva.', 'alert-type' => 'error']);
        }

        $ultimaMensualidad = AlumnoMensualidad::query()
            ->where('alumno_id', $alumno->id)
            ->whereNull('deleted_at')
            ->orderByDesc('periodo')
            ->orderByDesc('id')
            ->first();

        if ($ultimaMensualidad) {
            $ultimoFinPeriodo = $this->finPeriodoMensualidad($ultimaMensualidad);
            $minFechaInicio = $ultimoFinPeriodo->copy()->addDay();

            if (now()->startOfDay()->lte($ultimoFinPeriodo)) {
                return redirect()->back()
                    ->withInput()
                    ->with([
                        'message' => 'No se puede configurar otra mensualidad porque la última sigue vigente hasta el ' . $ultimoFinPeriodo->format('d/m/Y') . '.',
                        'alert-type' => 'error',
                    ]);
            }

            if ($fechaInicio->lt($minFechaInicio)) {
                return redirect()->back()
                    ->withInput()
                    ->with([
                        'message' => 'La nueva mensualidad debe iniciar después del último mes generado. Fecha mínima: ' . $minFechaInicio->format('d/m/Y') . '.',
                        'alert-type' => 'error',
                    ]);
            }
        }

        if ((float) $request->descuento > (float) $request->monto_mensual) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'El descuento no puede ser mayor a la mensualidad.', 'alert-type' => 'error']);
        }

        try {
            DB::transaction(function () use ($request, $alumno, $fechaInicio, $fechaFinPlan) {
                $plan = AlumnoMensualidadPlan::create([
                    'alumno_id' => $alumno->id,
                    'dojo_id' => $alumno->dojo_id,
                    'monto_mensual' => (float) $request->monto_mensual,
                    'descuento' => (float) ($request->descuento ?? 0),
                    'fecha_inicio' => $fechaInicio->toDateString(),
                    'fecha_fin' => $fechaFinPlan ? $fechaFinPlan->toDateString() : null,
                    'tipo_generacion' => $request->tipo_generacion,
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

    public function pausarPlan(Request $request, int $id)
    {
        $this->custom_authorize('edit_alumnos');

        $request->validate([
            'tipo_corte' => 'required|in:mes_completo,fecha',
            'fecha_corte' => 'nullable|required_if:tipo_corte,fecha|date',
            'observacion' => 'nullable|string|max:500',
        ]);

        $userDojoId = auth()->user()->dojo_id;

        $plan = AlumnoMensualidadPlan::with('alumno')
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);

        $mensualidadVigente = AlumnoMensualidad::query()
            ->where('alumno_id', $plan->alumno_id)
            ->where('alumno_mensualidad_plan_id', $plan->id)
            ->whereNull('deleted_at')
            ->get()
            ->first(function ($mensualidad) {
                $inicio = Carbon::parse($mensualidad->periodo)->startOfDay();
                $fin = $this->finPeriodoMensualidad($mensualidad);

                return now()->startOfDay()->betweenIncluded($inicio, $fin);
            });

        if ((int) $plan->status !== 1 && !$mensualidadVigente) {
            return redirect()->back()
                ->with(['message' => 'Esta mensualidad ya no está vigente para pausar o finalizar.', 'alert-type' => 'error']);
        }

        try {
            DB::transaction(function () use ($request, $plan) {
                $ultimaMensualidad = AlumnoMensualidad::query()
                    ->where('alumno_id', $plan->alumno_id)
                    ->where('alumno_mensualidad_plan_id', $plan->id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('periodo')
                    ->orderByDesc('id')
                    ->first();

                if ($request->tipo_corte === 'fecha' && $ultimaMensualidad) {
                    $inicioPeriodo = Carbon::parse($ultimaMensualidad->periodo)->startOfDay();
                    $finPeriodo = $this->finPeriodoMensualidad($ultimaMensualidad);
                    $fechaCorte = Carbon::parse($request->fecha_corte)->startOfDay();

                    if ($fechaCorte->lt($inicioPeriodo) || $fechaCorte->gt($finPeriodo)) {
                        throw ValidationException::withMessages([
                            'fecha_corte' => 'La fecha de corte debe estar dentro del último mes generado (' .
                                $inicioPeriodo->format('d/m/Y') . ' al ' . $finPeriodo->format('d/m/Y') . ').',
                        ]);
                    }

                    $diasPeriodo = $inicioPeriodo->diffInDays($finPeriodo) + 1;
                    $diasCobrados = $inicioPeriodo->diffInDays($fechaCorte) + 1;
                    $factor = $diasPeriodo > 0 ? $diasCobrados / $diasPeriodo : 1;

                    $montoOriginal = (float) $ultimaMensualidad->monto;
                    $descuentoOriginal = (float) $ultimaMensualidad->descuento;

                    $ultimaMensualidad->monto = round($montoOriginal * $factor, 2);
                    $ultimaMensualidad->descuento = round($descuentoOriginal * $factor, 2);
                    $ultimaMensualidad->fecha_fin = $fechaCorte->toDateString();
                    $ultimaMensualidad->status = $this->resolverStatus($ultimaMensualidad);
                    $ultimaMensualidad->observacion = trim(
                        ($ultimaMensualidad->observacion ? $ultimaMensualidad->observacion . "\n" : '') .
                        'Corte proporcional al ' . $fechaCorte->format('d/m/Y') .
                        " ({$diasCobrados}/{$diasPeriodo} dias). " .
                        'Monto original Bs ' . number_format($montoOriginal, 2, '.', ',') . '.'
                    );
                    $ultimaMensualidad->save();
                }

                $plan->status = 0;
                if ($request->tipo_corte === 'fecha' && isset($fechaCorte)) {
                    $plan->fecha_fin = $fechaCorte->toDateString();
                }
                if ($request->observacion) {
                    $plan->observacion = trim(($plan->observacion ? $plan->observacion . "\n" : '') . 'Pausa: ' . $request->observacion);
                }
                $plan->save();
            });
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return redirect()->route('voyager.alumnos.show', ['id' => $plan->alumno_id])
            ->with(['message' => 'Mensualidad pausada correctamente. No se generarán nuevos meses hasta configurar una nueva mensualidad.', 'alert-type' => 'success']);
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
                ->with(['message' => 'No se puede editar mora en una mensualidad anulada.', 'alert-type' => 'error']);
        }

        if ($mensualidad->saldo() <= 0) {
            return redirect()->back()
                ->with(['message' => 'No se puede editar mora en una mensualidad que ya está pagada.', 'alert-type' => 'error']);
        }

        $mensualidad->mora = (float) $request->mora;
        if ($request->observacion) {
            $mensualidad->observacion = trim(($mensualidad->observacion ? $mensualidad->observacion . "\n" : '') . $request->observacion);
        }
        $mensualidad->status = $this->resolverStatus($mensualidad);
        $mensualidad->save();

        return redirect()->route('voyager.alumnos.show', ['id' => $mensualidad->alumno_id])
            ->with(['message' => 'Mora actualizada correctamente.', 'alert-type' => 'success']);
    }

    public function comprobantePago(int $id)
    {
        $this->custom_authorize('read_alumnos');

        $userDojoId = auth()->user()->dojo_id;

        $pago = AlumnoMensualidadPago::with([
            'mensualidad.plan',
            'mensualidad.pagos',
            'alumno.person',
            'alumno.dojo',
            'registerUser',
        ])
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);

        return view('alumnos.mensualidades.comprobantePago', compact('pago'));
    }

    public function destroy(int $id)
    {
        $this->custom_authorize('delete_alumnos');

        $mensualidad = $this->findMensualidad($id);

        $ultimaMensualidadId = AlumnoMensualidad::query()
            ->where('alumno_id', $mensualidad->alumno_id)
            ->whereNull('deleted_at')
            ->orderByDesc('periodo')
            ->orderByDesc('id')
            ->value('id');

        if ((int) $ultimaMensualidadId !== (int) $mensualidad->id) {
            return redirect()->back()
                ->with(['message' => 'Solo se puede eliminar la mensualidad generada más reciente.', 'alert-type' => 'error']);
        }

        if ($mensualidad->pagos()->whereNull('deleted_at')->exists()) {
            return redirect()->back()
                ->with(['message' => 'No se puede eliminar una mensualidad que ya tiene pagos registrados.', 'alert-type' => 'error']);
        }

        try {
            $alumnoId = $mensualidad->alumno_id;
            $plan = $mensualidad->plan;

            DB::transaction(function () use ($mensualidad, $plan) {
                $mensualidad->delete();

                if (!$plan || $plan->tipo_generacion !== 'fecha_fin') {
                    return;
                }

                $ultimaMensualidadPlan = AlumnoMensualidad::query()
                    ->where('alumno_id', $mensualidad->alumno_id)
                    ->where('alumno_mensualidad_plan_id', $plan->id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('periodo')
                    ->orderByDesc('id')
                    ->first();

                if ($ultimaMensualidadPlan) {
                    $plan->fecha_fin = $this->finPeriodoMensualidad($ultimaMensualidadPlan)->toDateString();
                    $plan->status = Carbon::parse($plan->fecha_fin)->startOfDay()->gt(now()->startOfDay()) ? 1 : 0;
                } else {
                    $plan->fecha_fin = null;
                    $plan->status = 0;
                }

                $plan->save();
            });

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoId])
                ->with(['message' => 'Mensualidad eliminada correctamente.', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function comprobantePagoPublic(int $id)
    {
        $pago = AlumnoMensualidadPago::with([
            'mensualidad.plan',
            'mensualidad.pagos',
            'alumno.person',
            'alumno.dojo',
            'registerUser',
        ])
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return view('alumnos.mensualidades.comprobantePago', compact('pago'));
    }

    private function generarMensualidades(Alumno $alumno, AlumnoMensualidadPlan $plan): void
    {
        $periodo = Carbon::parse($plan->fecha_inicio)->startOfDay();
        $periodoFinal = now()->startOfDay();

        if ($plan->tipo_generacion === 'fecha_fin' && $plan->fecha_fin) {
            $periodoFinal = Carbon::parse($plan->fecha_fin)->startOfDay();
        }

        while ($periodo <= $periodoFinal) {
            $finMes = $periodo->copy()->addMonthNoOverflow()->subDay()->startOfDay();
            $fechaFinMensualidad = $finMes;

            if ($plan->tipo_generacion === 'fecha_fin' && $plan->fecha_fin) {
                $finPlan = Carbon::parse($plan->fecha_fin)->startOfDay();
                $fechaFinMensualidad = $finPlan->lt($finMes) ? $finPlan : $finMes;
            }

            $diasMes = $periodo->diffInDays($finMes) + 1;
            $diasCobrados = $periodo->diffInDays($fechaFinMensualidad) + 1;
            $factor = $diasMes > 0 ? $diasCobrados / $diasMes : 1;
            $monto = round((float) $plan->monto_mensual * $factor, 2);
            $descuento = round((float) $plan->descuento * $factor, 2);

            $mensualidadExistente = AlumnoMensualidad::withTrashed()
                ->where('alumno_id', $alumno->id)
                ->whereDate('periodo', $periodo->toDateString())
                ->first();

            if (!$mensualidadExistente) {
                $mensualidad = new AlumnoMensualidad([
                    'alumno_id' => $alumno->id,
                    'dojo_id' => $alumno->dojo_id,
                    'alumno_mensualidad_plan_id' => $plan->id,
                    'periodo' => $periodo->toDateString(),
                    'fecha_fin' => $fechaFinMensualidad->toDateString(),
                    'monto' => $monto,
                    'descuento' => $descuento,
                    'mora' => 0,
                    'monto_pagado' => 0,
                    'observacion' => $plan->observacion,
                    'status' => 'pendiente',
                ]);
                $mensualidad->status = $this->resolverStatus($mensualidad);
                $mensualidad->save();
            } elseif ($mensualidadExistente->trashed()) {
                $mensualidadExistente->fill([
                    'dojo_id' => $alumno->dojo_id,
                    'alumno_mensualidad_plan_id' => $plan->id,
                    'fecha_fin' => $fechaFinMensualidad->toDateString(),
                    'monto' => $monto,
                    'descuento' => $descuento,
                    'mora' => 0,
                    'monto_pagado' => 0,
                    'observacion' => $plan->observacion,
                    'status' => 'pendiente',
                    'deleteUser_id' => null,
                    'deleteRole' => null,
                    'deleteObservation' => null,
                ]);
                $mensualidadExistente->restore();
                $mensualidadExistente->status = $this->resolverStatus($mensualidadExistente);
                $mensualidadExistente->save();
            }

            $periodo->addMonthNoOverflow();
        }

        if ($plan->tipo_generacion === 'fecha_fin' && $plan->fecha_fin) {
            $plan->status = 0;
            $plan->save();
        }
    }

    private function finPeriodoMensualidad(AlumnoMensualidad $mensualidad): Carbon
    {
        if ($mensualidad->fecha_fin) {
            return Carbon::parse($mensualidad->fecha_fin)->startOfDay();
        }

        return Carbon::parse($mensualidad->periodo)
            ->startOfDay()
            ->addMonthNoOverflow()
            ->subDay();
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
