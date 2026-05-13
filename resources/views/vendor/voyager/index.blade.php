@extends('voyager::master')

@php
    use App\Models\Alumno;
    use App\Models\AlumnoGradoExamen;
    use App\Models\AlumnoGradoRepaso;
    use App\Models\AlumnoMensualidad;
    use App\Models\AlumnoMensualidadPago;
    use App\Models\Dojo;
    use Illuminate\Support\Collection;

    $user = auth()->user();
    $userDojoId = $user->dojo_id;
    $dojos = Dojo::query()
        ->whereNull('deleted_at')
        ->where('status', 1)
        ->orderBy('nombre')
        ->get();

    $selectedDojoId = $userDojoId ?: request('dojo_id');
    if (!$selectedDojoId && $dojos->isNotEmpty()) {
        $selectedDojoId = $dojos->first()->id;
    }

    if (!$userDojoId && $selectedDojoId && !$dojos->contains('id', (int) $selectedDojoId)) {
        $selectedDojoId = $dojos->first()->id ?? null;
    }

    $selectedDojo = $selectedDojoId
        ? $dojos->firstWhere('id', (int) $selectedDojoId)
        : null;

    $money = fn($value) => 'Bs ' . number_format((float) $value, 2, '.', ',');
    $debtOf = fn($monto, $pagado) => max(0, (float) $monto - (float) $pagado);
    $today = now();
    $monthStart = now()->startOfMonth()->toDateString();
    $monthEnd = now()->endOfMonth()->toDateString();

    $empty = collect();

    $alumnos = $selectedDojoId
        ? Alumno::with('person')
            ->where('dojo_id', $selectedDojoId)
            ->whereNull('deleted_at')
            ->get()
        : $empty;

    $repasos = $selectedDojoId
        ? AlumnoGradoRepaso::with([
            'alumnoGrado.alumno.person',
            'alumnoGrado.grado',
            'arancel',
        ])
            ->whereNull('deleted_at')
            ->whereHas('alumnoGrado.alumno', function ($query) use ($selectedDojoId) {
                $query->where('dojo_id', $selectedDojoId);
            })
            ->get()
        : $empty;

    $examenes = $selectedDojoId
        ? AlumnoGradoExamen::with([
            'alumnoGrado.alumno.person',
            'alumnoGrado.grado',
            'arancel',
        ])
            ->whereNull('deleted_at')
            ->whereHas('alumnoGrado.alumno', function ($query) use ($selectedDojoId) {
                $query->where('dojo_id', $selectedDojoId);
            })
            ->get()
        : $empty;

    $mensualidades = $selectedDojoId
        ? AlumnoMensualidad::with(['alumno.person', 'pagos' => function ($query) {
            $query->whereNull('deleted_at')->orderByDesc('fecha')->orderByDesc('id');
        }])
            ->where('dojo_id', $selectedDojoId)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'anulado')
            ->get()
        : $empty;

    $pagosMensualidades = $selectedDojoId
        ? AlumnoMensualidadPago::with(['alumno.person', 'mensualidad'])
            ->where('dojo_id', $selectedDojoId)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
        : $empty;

    $repasoTotal = $repasos->sum('monto');
    $repasoPagado = $repasos->sum('monto_pagado');
    $repasoSaldo = $repasos->sum(fn($item) => $debtOf($item->monto, $item->monto_pagado));

    $examenTotal = $examenes->sum('monto');
    $examenPagado = $examenes->sum('monto_pagado');
    $examenSaldo = $examenes->sum(fn($item) => $debtOf($item->monto, $item->monto_pagado));

    $mensualidadTotal = $mensualidades->sum(fn($item) => $item->total());
    $mensualidadPagado = $mensualidades->sum('monto_pagado');
    $mensualidadSaldo = $mensualidades->sum(fn($item) => $item->saldo());
    $arancelTotal = $repasoTotal + $examenTotal;

    $totalCobrar = $repasoTotal + $examenTotal + $mensualidadTotal;
    $percent = fn($value, $total) => $total > 0 ? min(100, round(((float) $value / (float) $total) * 100, 1)) : 0;
    $arancelAreaPercent = $percent($arancelTotal, $totalCobrar);
    $mensualidadAreaPercent = $percent($mensualidadTotal, $totalCobrar);
    $repasoArancelPercent = $percent($repasoTotal, $arancelTotal);
    $examenArancelPercent = $percent($examenTotal, $arancelTotal);
    $dashboardCharts = [
        [
            'label' => 'Repasos / Puntas',
            'total' => $repasoTotal,
            'pagado' => $repasoPagado,
            'saldo' => $repasoSaldo,
            'count' => $repasos->count(),
        ],
        [
            'label' => 'Examenes',
            'total' => $examenTotal,
            'pagado' => $examenPagado,
            'saldo' => $examenSaldo,
            'count' => $examenes->count(),
        ],
        [
            'label' => 'Mensualidades',
            'total' => $mensualidadTotal,
            'pagado' => $mensualidadPagado,
            'saldo' => $mensualidadSaldo,
            'count' => $mensualidades->count(),
        ],
    ];

    $repasosMes = $repasos->filter(fn($item) => $item->fecha >= $monthStart && $item->fecha <= $monthEnd);
    $examenesMes = $examenes->filter(fn($item) => $item->fecha >= $monthStart && $item->fecha <= $monthEnd);
    $mensualidadesMes = $mensualidades->filter(function ($item) use ($monthStart, $monthEnd) {
        $periodo = optional($item->periodo)->toDateString() ?: $item->periodo;
        return $periodo >= $monthStart && $periodo <= $monthEnd;
    });

    $deudasExamenes = $examenes
        ->filter(fn($item) => $debtOf($item->monto, $item->monto_pagado) > 0)
        ->sortByDesc(fn($item) => $debtOf($item->monto, $item->monto_pagado))
        ->take(8)
        ->values();

    $deudasMensualidades = $mensualidades
        ->filter(fn($item) => $item->saldo() > 0)
        ->sortByDesc(fn($item) => $item->saldo())
        ->take(10)
        ->values();

    $alumnosConDeuda = $mensualidades
        ->filter(fn($item) => $item->saldo() > 0)
        ->groupBy('alumno_id')
        ->map(function (Collection $items) {
            $first = $items->first();
            return [
                'alumno' => $first->alumno,
                'total' => $items->sum(fn($item) => $item->saldo()),
                'meses' => $items->count(),
            ];
        })
        ->sortByDesc('total')
        ->take(8)
        ->values();
@endphp

@section('page_header')
    <div class="page-content container-fluid dashboard-kaiteki-header">
        <div class="row">
            <div class="col-md-8">
                <h1 class="page-title">
                    <i class="voyager-dashboard"></i> Panel general del sistema
                </h1>
                <p class="text-muted">
                    Resumen economico y operativo de {{ $selectedDojo ? $selectedDojo->nombre : 'la dojo seleccionado' }}.
                </p>
            </div>
            <div class="col-md-4">
                @if ($userDojoId)
                    <div class="dojo-fixed-box">
                        <span class="text-muted">Dojo Asignado</span>
                        <strong>{{ optional($selectedDojo)->nombre ?? 'Sin Dojo' }}</strong>
                    </div>
                @else
                    <form method="GET" action="{{ url('/admin') }}" class="dojo-selector-form">
                        <label for="dashboard-dojo-id">DOJO</label>
                        <div class="input-group">
                            <select name="dojo_id" id="dashboard-dojo-id" class="form-control" onchange="this.form.submit()">
                                @foreach ($dojos as $dojo)
                                    <option value="{{ $dojo->id }}" @selected((int) $selectedDojoId === (int) $dojo->id)>
                                        {{ $dojo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">
                                    <i class="voyager-search"></i>
                                </button>
                            </span>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid dashboard-kaiteki">
        @include('voyager::alerts')

        @if (!$selectedDojo)
            <div class="alert alert-warning">
                No hay una dojo activo para mostrar informacion. Registra o activa un dojo primero.
            </div>
        @else
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-heading bg-primary text-white">
                            <h3 class="panel-title"><i class="voyager-dollar"></i> Deudas de mensualidad por alumno</h3>
                        </div>
                        <div class="panel-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Meses pendientes</th>
                                            <th class="text-right">Saldo</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($alumnosConDeuda as $deuda)
                                            <tr>
                                                <td>{{ optional(optional($deuda['alumno'])->person)->first_name ?? 'Sin alumno' }}</td>
                                                <td>{{ $deuda['meses'] }}</td>
                                                <td class="text-right text-danger">{{ $money($deuda['total']) }}</td>
                                                <td class="text-right">
                                                    @if ($deuda['alumno'])
                                                        <a href="{{ route('voyager.alumnos.show', ['id' => $deuda['alumno']->id]) }}" class="btn btn-xs btn-default">
                                                            Ver
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="empty-state">No hay deudas de mensualidad para este dojo.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-heading bg-info text-white">
                            <h3 class="panel-title"><i class="voyager-warning"></i> Examenes pendientes de pago</h3>
                        </div>
                        <div class="panel-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Grado</th>
                                            <th>Fecha</th>
                                            <th class="text-right">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($deudasExamenes as $examen)
                                            <tr>
                                                <td>{{ optional(optional(optional($examen->alumnoGrado)->alumno)->person)->first_name ?? 'Sin alumno' }}</td>
                                                <td>{{ optional(optional($examen->alumnoGrado)->grado)->nombre ?? 'Sin grado' }}</td>
                                                <td>{{ $examen->fecha ? \Carbon\Carbon::parse($examen->fecha)->format('d/m/Y') : '-' }}</td>
                                                <td class="text-right text-danger">{{ $money($debtOf($examen->monto, $examen->monto_pagado)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="empty-state">No hay examenes pendientes.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="panel panel-bordered">
                        <div class="panel-heading bg-warning text-white">
                            <h3 class="panel-title"><i class="voyager-calendar"></i> Mensualidades pendientes</h3>
                        </div>
                        <div class="panel-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Periodo</th>
                                            <th>Estado</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Pagado</th>
                                            <th class="text-right">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($deudasMensualidades as $mensualidad)
                                            <tr>
                                                <td>{{ optional(optional($mensualidad->alumno)->person)->first_name ?? 'Sin alumno' }}</td>
                                                <td>
                                                    {{ optional($mensualidad->periodo)->format('d/m/Y') }}
                                                    @if ($mensualidad->fecha_fin)
                                                        - {{ optional($mensualidad->fecha_fin)->format('d/m/Y') }}
                                                    @endif
                                                </td>
                                                <td>{{ $mensualidad->estadoPago() }}</td>
                                                <td class="text-right">{{ $money($mensualidad->total()) }}</td>
                                                <td class="text-right text-success">{{ $money($mensualidad->monto_pagado) }}</td>
                                                <td class="text-right text-danger">{{ $money($mensualidad->saldo()) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="empty-state">No hay mensualidades pendientes.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="panel panel-bordered">
                        <div class="panel-heading bg-success text-white">
                            <h3 class="panel-title"><i class="voyager-receipt"></i> Ultimos pagos de mensualidad</h3>
                        </div>
                        <div class="panel-body no-padding">
                            <div class="table-responsive">
                                <table class="table table-hover dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Fecha</th>
                                            <th class="text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pagosMensualidades as $pago)
                                            <tr>
                                                <td>{{ optional(optional($pago->alumno)->person)->first_name ?? 'Sin alumno' }}</td>
                                                <td>{{ $pago->fecha ? \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') : '-' }}</td>
                                                <td class="text-right text-success">{{ $money($pago->monto) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="empty-state">No hay pagos recientes de mensualidad.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .dashboard-kaiteki-header .page-title {
            margin-bottom: 4px;
        }

        .dojo-fixed-box,
        .dojo-selector-form {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px;
        }

        .dojo-fixed-box span,
        .dojo-fixed-box strong,
        .dojo-selector-form label {
            display: block;
        }

        .dojo-fixed-box strong {
            margin-top: 4px;
            font-size: 16px;
        }

        .dashboard-kaiteki .panel {
            border-radius: 6px;
            overflow: hidden;
        }

        .metric-card .panel-body {
            min-height: 126px;
        }

        .metric-label,
        .metric-help {
            display: block;
            color: #6b7280;
        }

        .metric-value {
            display: block;
            margin: 10px 0 8px;
            font-size: 26px;
            line-height: 1.1;
            color: #111827;
        }

        .metric-total {
            border-top: 4px solid #2563eb;
        }

        .metric-aranceles {
            border-top: 4px solid #2563eb;
        }

        .metric-mensualidades {
            border-top: 4px solid #f59e0b;
        }

        .summary-panel .panel-title {
            font-weight: 600;
        }

        .chart-panel .panel-body {
            padding: 18px;
        }

        .donut-grid,
        .donut-card,
        .donut-legend,
        .chart-row-header,
        .chart-row-values {
            display: flex;
            gap: 16px;
        }

        .donut-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 12px;
        }

        .donut-card {
            align-items: center;
            min-height: 220px;
            padding: 18px;
            border: 1px solid #edf2f7;
            border-radius: 6px;
            background: #fbfdff;
        }

        .donut-chart {
            position: relative;
            flex: 0 0 178px;
            width: 178px;
            height: 178px;
            border-radius: 50%;
            box-shadow: inset 0 0 0 1px rgba(17, 24, 39, 0.05);
        }

        .donut-total {
            background:
                conic-gradient(
                    #2563eb 0 var(--aranceles),
                    #f59e0b var(--aranceles) 100%
                );
        }

        .donut-arancel {
            background:
                conic-gradient(
                    #2563eb 0 var(--repasos),
                    #7c3aed var(--repasos) 100%
                );
        }

        .donut-empty {
            background: #e5e7eb !important;
        }

        .donut-hole {
            position: absolute;
            inset: 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .donut-hole span,
        .donut-copy p,
        .chart-row-header span,
        .chart-row-values {
            color: #6b7280;
            font-size: 12px;
        }

        .donut-hole strong {
            display: block;
            margin-top: 3px;
            font-size: 28px;
            color: #111827;
            line-height: 1;
        }

        .donut-copy {
            min-width: 0;
        }

        .donut-copy h4 {
            margin: 0 0 6px;
            font-weight: 700;
            color: #111827;
        }

        .donut-legend {
            flex-direction: column;
            gap: 7px;
            margin-top: 12px;
            color: #4b5563;
        }

        .legend-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            margin-right: 5px;
            border-radius: 50%;
        }

        .arancel-dot {
            background: #2563eb;
        }

        .repaso-dot {
            background: #2563eb;
        }

        .examen-dot {
            background: #7c3aed;
        }

        .mensualidad-dot {
            background: #f59e0b;
        }

        .chart-row {
            padding: 12px 0;
            border-top: 1px solid #f1f5f9;
        }

        .chart-row:first-child {
            border-top: 0;
        }

        .chart-row-header {
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .chart-row-values {
            justify-content: space-between;
            margin-top: 6px;
        }

        @media (max-width: 991px) {
            .donut-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .donut-card,
            .chart-row-header,
            .chart-row-values {
                flex-direction: column;
                align-items: flex-start;
            }

            .donut-chart {
                align-self: center;
            }
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-row:last-of-type {
            border-bottom: 0;
        }

        .summary-footer {
            margin-top: 12px;
            padding: 10px;
            border-radius: 4px;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
        }

        .dashboard-table {
            margin-bottom: 0;
        }

        .dashboard-table th {
            white-space: nowrap;
            color: #4b5563;
            font-size: 12px;
            text-transform: uppercase;
        }

        .dashboard-table td {
            vertical-align: middle !important;
        }

        .empty-state {
            padding: 24px !important;
            text-align: center;
            color: #6b7280;
        }

        .no-padding {
            padding: 0 !important;
        }
    </style>
@stop
