<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoMensualidad;
use App\Models\AlumnoMensualidadPago;
use App\Models\AlumnoMensualidadPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $ultimoPlan = AlumnoMensualidadPlan::query()
            ->where('alumno_id', $alumno->id)
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
            'descuento' => $mensualidadesResumen->sum('descuento'),
            'pendientes' => $mensualidadesResumen->filter(fn($item) => $item->estadoPago() === 'Pendiente')->count(),
            'parciales' => $mensualidadesResumen->filter(fn($item) => $item->estadoPago() === 'Parcial')->count(),
            'pagadas' => $mensualidadesResumen->filter(fn($item) => in_array($item->estadoPago(), ['Pagado', 'Exonerado']))->count(),
            'total_meses' => $mensualidadesResumen->count(),
        ];
        $resumen['saldo'] = max(0, $resumen['total'] - $resumen['pagado']);

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
            'ultimaMensualidad',
            'ultimoPlan'
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
        $this->ensureAlumnoActivo($alumno, 'El alumno esta inactivo. No se puede configurar mensualidad.');
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

        $ultimaMensualidad = AlumnoMensualidad::with('plan')
            ->where('alumno_id', $alumno->id)
            ->whereNull('deleted_at')
            ->orderByDesc('periodo')
            ->orderByDesc('id')
            ->first();

        if (!$ultimaMensualidad && $alumno->fechaIngreso) {
            $fechaIngreso = Carbon::parse($alumno->fechaIngreso)->startOfDay();

            if ($fechaInicio->lt($fechaIngreso)) {
                return redirect()->back()
                    ->withInput()
                    ->with([
                        'message' => 'La primera mensualidad debe iniciar desde la fecha de ingreso del alumno o una fecha posterior. Fecha de ingreso: ' . $fechaIngreso->format('d/m/Y') . '.',
                        'alert-type' => 'error',
                    ]);
            }
        }

        if ($ultimaMensualidad) {
            $ultimoFinPeriodo = $this->finPeriodoMensualidad($ultimaMensualidad);
            $minFechaInicio = $ultimoFinPeriodo->copy()->addDay();

            if (now()->startOfDay()->lte($ultimoFinPeriodo)) {
                $planUltimaMensualidad = $ultimaMensualidad->plan;
                $tieneCorteProgramado = $planUltimaMensualidad
                    && $planUltimaMensualidad->tipo_generacion === 'fecha_fin'
                    && $planUltimaMensualidad->fecha_fin
                    && now()->startOfDay()->lte(Carbon::parse($planUltimaMensualidad->fecha_fin)->startOfDay());
                $mensaje = $tieneCorteProgramado
                    ? 'No se puede configurar otra mensualidad porque el alumno ya tiene un corte programado hasta el ' . Carbon::parse($planUltimaMensualidad->fecha_fin)->format('d/m/Y') . '.'
                    : 'No se puede configurar otra mensualidad porque la última sigue vigente hasta el ' . $ultimoFinPeriodo->format('d/m/Y') . '.';

                return redirect()->back()
                    ->withInput()
                    ->with([
                        'message' => $mensaje,
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
        $mensualidad->loadMissing('alumno');

        if (!$mensualidad->alumno) {
            return redirect()->back()
                ->with(['message' => 'No se puede registrar pago porque el alumno no está disponible.', 'alert-type' => 'error']);
        }

        if ($mensualidad->status === 'anulado') {
            return redirect()->back()
                ->with(['message' => 'No se puede registrar pago sobre una mensualidad anulada.', 'alert-type' => 'error']);
        }

        $montoPago = round((float) $request->monto, 2);
        $saldoActual = round($mensualidad->saldo(), 2);

        if ($saldoActual <= 0) {
            return redirect()->back()
                ->with(['message' => 'Esta mensualidad ya no tiene saldo pendiente.', 'alert-type' => 'error']);
        }

        if ($montoPago > $saldoActual) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'message' => 'El pago no puede ser mayor al saldo pendiente. Saldo actual: Bs ' . number_format($saldoActual, 2, '.', ',') . '.',
                    'alert-type' => 'error',
                ]);
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
            DB::transaction(function () use ($request, $mensualidad, $montoPago) {
                $mensualidadBloqueada = AlumnoMensualidad::query()
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->findOrFail($mensualidad->id);

                $saldoBloqueado = round($mensualidadBloqueada->saldo(), 2);

                if ($saldoBloqueado <= 0) {
                    throw ValidationException::withMessages([
                        'monto' => 'Esta mensualidad ya no tiene saldo pendiente.',
                    ]);
                }

                if ($montoPago > $saldoBloqueado) {
                    throw ValidationException::withMessages([
                        'monto' => 'El pago no puede ser mayor al saldo pendiente. Saldo actual: Bs ' . number_format($saldoBloqueado, 2, '.', ',') . '.',
                    ]);
                }

                AlumnoMensualidadPago::create([
                    'alumno_mensualidad_id' => $mensualidadBloqueada->id,
                    'alumno_id' => $mensualidadBloqueada->alumno_id,
                    'dojo_id' => $mensualidadBloqueada->dojo_id,
                    'fecha' => $request->fecha,
                    'monto' => $montoPago,
                    'observacion' => $request->observacion,
                ]);

                $mensualidadBloqueada->monto_pagado = round((float) $mensualidadBloqueada->monto_pagado + $montoPago, 2);
                $mensualidadBloqueada->status = $this->resolverStatus($mensualidadBloqueada);
                $mensualidadBloqueada->save();
            });

            return redirect()->route('voyager.alumnos.show', ['id' => $mensualidad->alumno_id])
                ->with(['message' => 'Pago de mensualidad registrado correctamente.', 'alert-type' => 'success']);
        } catch (ValidationException $exception) {
            throw $exception;
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
        $this->ensureAlumnoActivo($plan->alumno, 'El alumno esta inactivo. No se puede pausar ni modificar mensualidades.');

        $ultimaMensualidadPlan = AlumnoMensualidad::query()
            ->where('alumno_id', $plan->alumno_id)
            ->where('alumno_mensualidad_plan_id', $plan->id)
            ->whereNull('deleted_at')
            ->orderByDesc('periodo')
            ->orderByDesc('id')
            ->first();

        $ultimaMensualidadConCorteFecha = false;
        if ($ultimaMensualidadPlan && $ultimaMensualidadPlan->fecha_fin) {
            $finNatural = Carbon::parse($ultimaMensualidadPlan->periodo)
                ->startOfDay()
                ->addMonthNoOverflow()
                ->subDay();
            $fechaFinUltimaMensualidad = Carbon::parse($ultimaMensualidadPlan->fecha_fin)->startOfDay();
            $ultimaMensualidadConCorteFecha = $fechaFinUltimaMensualidad->toDateString() !== $finNatural->toDateString();
        }

        if (($plan->tipo_generacion === 'fecha_fin' || $ultimaMensualidadConCorteFecha) && $request->tipo_corte !== 'fecha') {
            return redirect()->back()
                ->with(['message' => 'Las mensualidades creadas con fecha fin solo se pueden finalizar por fecha, no por mes completo.', 'alert-type' => 'error']);
        }

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

    public function activarPlan(int $id)
    {
        $this->custom_authorize('edit_alumnos');

        $userDojoId = auth()->user()->dojo_id;
        $plan = AlumnoMensualidadPlan::with('alumno')
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);

        $this->ensureAlumnoActivo($plan->alumno, 'El alumno esta inactivo. No se puede activar la mensualidad.');

        if ($plan->tipo_generacion !== 'automatica') {
            return redirect()->back()
                ->with(['message' => 'Solo las mensualidades automáticas se pueden activar nuevamente.', 'alert-type' => 'error']);
        }

        $planActivo = AlumnoMensualidadPlan::query()
            ->where('alumno_id', $plan->alumno_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->where('id', '!=', $plan->id)
            ->exists();

        if ($planActivo) {
            return redirect()->back()
                ->with(['message' => 'El alumno ya tiene una mensualidad activa.', 'alert-type' => 'error']);
        }

        $plan->status = 1;
        $plan->fecha_fin = null;
        $plan->save();

        $this->generarMensualidades($plan->alumno, $plan);

        return redirect()->route('voyager.alumnos.show', ['id' => $plan->alumno_id])
            ->with(['message' => 'Generación automática activada correctamente.', 'alert-type' => 'success']);
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
        $mensualidad->loadMissing('alumno');
        $this->ensureAlumnoActivo($mensualidad->alumno, 'El alumno esta inactivo. No se pueden eliminar mensualidades.');

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

    public function enviarComprobanteWhatsapp(int $id)
    {
        $this->custom_authorize('read_alumnos');

        $userDojoId = Auth::user()->dojo_id;

        $pago = AlumnoMensualidadPago::with([
            'mensualidad.plan',
            'mensualidad.pagos',
            'alumno.person',
            'alumno.dojo',
            'registerUser',
        ])
            ->whereNull('deleted_at')
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->findOrFail($id);

        $person = optional($pago->alumno)->person;
        $phone = $this->normalizeWhatsappPhone(optional($person)->phone);

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'El alumno no tiene un teléfono válido para WhatsApp.'], 422);
        }

        $server = rtrim((string) setting('whatsapp.servidores'), '/');
        $session = (string) setting('whatsapp.session');

        if (!$server || !$session) {
            return response()->json(['success' => false, 'message' => 'Configure el servidor y la sesión de WhatsApp en Ajustes.'], 422);
        }

        if (!env('WHATSAPP_SEND_KEY')) {
            return response()->json(['success' => false, 'message' => 'Configure WHATSAPP_SEND_KEY en el archivo .env.'], 422);
        }

        try {
            $isPdf = true;
            $fileName = 'Comprobante-Mensualidad-' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . '.pdf';
            $path = 'alumnos/mensualidades/comprobantes/' . $fileName;
            $pdf = Pdf::loadView('alumnos.mensualidades.comprobantePago', compact('pago', 'isPdf'))
                ->setPaper('letter');
            $pdfOutput = $pdf->output();

            foreach (glob(public_path('tmp/qr_alumno_' . $pago->id . '_*.png')) as $tmpQr) {
                @unlink($tmpQr);
            }

            Storage::disk('public')->put($path, $pdfOutput);

            $documentUrl = asset('storage/' . $path);

            $alumno = $pago->alumno;
            $periodoInicio = optional($pago->mensualidad)->periodo
                ? Carbon::parse($pago->mensualidad->periodo)->format('d/m/Y')
                : null;
            $periodoFin = $pago->mensualidad && $pago->mensualidad->fecha_fin
                ? Carbon::parse($pago->mensualidad->fecha_fin)->format('d/m/Y')
                : null;
            $periodo = $periodoInicio && $periodoFin ? $periodoInicio . ' al ' . $periodoFin : 'N/A';

            $message = 'Hola ' . optional(optional($alumno)->person)->first_name . ', le enviamos su comprobante de pago de mensualidad.' . "\n"
                . 'Período: ' . $periodo . "\n"
                . 'Monto pagado: Bs ' . number_format((float) $pago->monto, 2, '.', ',') . "\n"
                . 'Gracias por su preferencia.';

            $status = Http::timeout(15)->get($server . '/status?id=' . $session)->json();

            if (!($status['success'] ?? false)) {
                return response()->json(['success' => false, 'message' => 'El servidor de WhatsApp no respondió correctamente.'], 422);
            }

            if (!($status['status'] ?? false)) {
                return response()->json(['success' => false, 'message' => 'WhatsApp está desconectado. Conecte la sesión antes de enviar.'], 422);
            }

            $sendUrl = $server . '/send?id=' . $session . '&token=' . null;
            $response = Http::withHeaders(['X-Api-Key' => env('WHATSAPP_SEND_KEY')])
                ->timeout(25)
                ->post($sendUrl, [
                    'phone'        => '+' . $phone,
                    'text'         => $message,
                    'image_url'    => null,
                    'document_url' => $documentUrl,
                    'file_name'    => $fileName,
                ])
                ->json();
        } catch (\Throwable $e) {
            Log::error('Error enviando comprobante de mensualidad alumno por WhatsApp', [
                'pago_id' => $pago->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo enviar por WhatsApp: ' . $e->getMessage()], 422);
        }

        if (!($response['success'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'WhatsApp respondió que no pudo enviar el comprobante.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Comprobante enviado por WhatsApp correctamente.']);
    }

    private function normalizeWhatsappPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '591')) {
            return strlen($digits) >= 10 ? $digits : null;
        }

        return strlen($digits) >= 7 ? '591' . $digits : null;
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
