@extends('voyager::master')

@php
    use App\Models\Alumno;
    use App\Models\AlumnoGradoExamen;
    use App\Models\AlumnoGradoRepaso;
    use App\Models\AlumnoMensualidad;
    use App\Models\AlumnoMensualidadPago;
    use App\Models\Dojo;
    use App\Models\DojoMensualidad;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Collection;

    $user = auth()->user();
    $userDojoId = $user->dojo_id;
    $dojos = Dojo::query()
        ->whereNull('deleted_at')
        ->where('status', 1)
        ->orderBy('nombre')
        ->get();

    $selectedDojoId = auth()->user()->isGlobal() ? (request('dojo_id') ?: $userDojoId) : $userDojoId;
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

    // Mensualidades del dojo (SaaS billing) — solo para branch users
    $dojoMensualidadVigente = null;
    $dojoMensualidadSaldo   = 0;
    if ($userDojoId) {
        $dojoMensualidades = DojoMensualidad::where('dojo_id', $userDojoId)
            ->orderBy('fecha_fin', 'desc')
            ->get();
        $dojoMensualidadVigente = $dojoMensualidades->first(fn($m) => $m->isVigente());
        $dojoMensualidadSaldo   = $dojoMensualidades
            ->filter(fn($m) => $m->isVigente())
            ->sum(fn($m) => $m->saldo());
    }

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
    <div class="page-content container-fluid dk-header">
        <div class="dk-header-inner">
            <div class="dk-header-left">
                <div class="dk-header-title">Panel general</div>
                <div class="dk-header-sub">
                    @if($selectedDojo)
                        <span class="dk-dojo-badge">
                            <i class="voyager-location"></i>
                            {{ $selectedDojo->nombre }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="dk-header-right">
                @if ($userDojoId)
                    <div class="dk-dojo-fixed">
                        <i class="voyager-location"></i>
                        <strong>{{ optional($selectedDojo)->nombre ?? 'Sin Dojo' }}</strong>
                    </div>
                @else
                    <form method="GET" action="{{ url('/admin') }}" class="dk-dojo-form">
                        <select name="dojo_id" class="dk-dojo-select" onchange="this.form.submit()">
                            @foreach ($dojos as $dojo)
                                <option value="{{ $dojo->id }}" @selected((int) $selectedDojoId === (int) $dojo->id)>
                                    {{ $dojo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="page-content container-fluid dk-body">
    @include('voyager::alerts')

    @if (!$selectedDojo)
        <div class="alert alert-warning">
            No hay un dojo activo para mostrar información. Registra o activa un dojo primero.
        </div>
    @else

        {{-- ── STAT CARDS ── --}}
        <div class="dk-stats">
            <div class="dk-stat">
                <div class="dk-stat-icon dk-stat-blue">
                    <i class="voyager-people"></i>
                </div>
                <div class="dk-stat-body">
                    <div class="dk-stat-value">{{ $alumnos->count() }}</div>
                    <div class="dk-stat-label">Alumnos activos</div>
                </div>
            </div>

            <div class="dk-stat">
                <div class="dk-stat-icon dk-stat-amber">
                    <i class="voyager-calendar"></i>
                </div>
                <div class="dk-stat-body">
                    <div class="dk-stat-value">{{ $money($mensualidadSaldo) }}</div>
                    <div class="dk-stat-label">Deuda mensualidades</div>
                </div>
            </div>

            <div class="dk-stat">
                <div class="dk-stat-icon dk-stat-red">
                    <i class="voyager-dollar"></i>
                </div>
                <div class="dk-stat-body">
                    <div class="dk-stat-value">{{ $money($examenSaldo) }}</div>
                    <div class="dk-stat-label">Deuda exámenes</div>
                </div>
            </div>

            <div class="dk-stat">
                <div class="dk-stat-icon dk-stat-green">
                    <i class="voyager-check"></i>
                </div>
                <div class="dk-stat-body">
                    <div class="dk-stat-value">{{ $money($pagosMensualidades->sum('monto')) }}</div>
                    <div class="dk-stat-label">Pagos recientes</div>
                </div>
            </div>
        </div>

        {{-- ── MENSUALIDAD DEL SISTEMA (solo branch users) ── --}}
        @if($userDojoId)
        <div class="dk-card" style="margin-bottom: 20px; border-left: 4px solid {{ $dojoMensualidadVigente ? '#22a7f0' : '#e74c3c' }};">
            <div class="dk-card-header" style="background: {{ $dojoMensualidadVigente ? '#f0f8ff' : '#fff5f5' }}; display:flex; justify-content:space-between; align-items:center; padding: 12px 18px;">
                <span style="font-weight:700; font-size:13px; color:#555;">
                    <i class="fa fa-file-invoice-dollar" style="color: {{ $dojoMensualidadVigente ? '#22a7f0' : '#e74c3c' }};"></i>
                    Mensualidad del Sistema
                </span>
                <span class="label label-{{ $dojoMensualidadVigente ? 'success' : 'danger' }}" style="font-size:12px; padding:4px 12px;">
                    {{ $dojoMensualidadVigente ? 'Con servicio' : 'Con corte' }}
                </span>
            </div>
            <div style="padding: 14px 18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; gap:30px; flex-wrap:wrap;">
                    @if($dojoMensualidadVigente)
                        <div>
                            <div style="font-size:10px; color:#888; text-transform:uppercase; font-weight:600;">Vigente hasta</div>
                            <div style="font-size:16px; font-weight:700; color:#22a7f0;">{{ $dojoMensualidadVigente->fecha_fin->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div style="font-size:10px; color:#888; text-transform:uppercase; font-weight:600;">Saldo pendiente</div>
                            <div style="font-size:16px; font-weight:700; color: {{ $dojoMensualidadSaldo > 0 ? '#e74c3c' : '#27ae60' }};">
                                Bs {{ number_format($dojoMensualidadSaldo, 2) }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:10px; color:#888; text-transform:uppercase; font-weight:600;">Estado pago</div>
                            <div style="font-size:14px; font-weight:600; margin-top:2px;">
                                @php $ep = $dojoMensualidadVigente->estadoPago(); @endphp
                                <span class="label label-{{ $ep === 'Pagado' ? 'success' : 'warning' }}">{{ $ep }}</span>
                            </div>
                        </div>
                    @else
                        <div>
                            <div style="font-size:13px; color:#e74c3c;">No tiene una mensualidad vigente. Contacte al administrador.</div>
                        </div>
                    @endif
                </div>
                @if(auth()->user()->hasPermission('browse_dojo_mensualidades'))
                    <a href="{{ route('mensualidades.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-list"></i> Ver historial
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ── DEUDAS MENSUALIDAD POR ALUMNO ── --}}
        <div class="dk-card">
            <div class="dk-card-header dk-accent-blue">
                <span class="dk-card-title"><i class="voyager-dollar"></i> Deudas de mensualidad por alumno</span>
                <span class="dk-badge">{{ $alumnosConDeuda->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="dk-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Meses</th>
                            <th class="text-right">Saldo total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alumnosConDeuda as $deuda)
                            <tr>
                                <td>{{ optional(optional($deuda['alumno'])->person)->first_name ?? 'Sin alumno' }}</td>
                                <td><span class="dk-pill dk-pill-amber">{{ $deuda['meses'] }} {{ $deuda['meses'] === 1 ? 'mes' : 'meses' }}</span></td>
                                <td class="text-right"><strong class="dk-debt">{{ $money($deuda['total']) }}</strong></td>
                                <td class="text-right">
                                    @if ($deuda['alumno'])
                                        <a href="{{ route('voyager.alumnos.show', ['id' => $deuda['alumno']->id]) }}" class="dk-btn-sm">Ver</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="dk-empty">Sin deudas de mensualidad</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── EXAMENES PENDIENTES ── --}}
        <div class="dk-card">
            <div class="dk-card-header dk-accent-red">
                <span class="dk-card-title"><i class="voyager-warning"></i> Exámenes pendientes de pago</span>
                <span class="dk-badge">{{ $deudasExamenes->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="dk-table">
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
                                <td class="text-right"><strong class="dk-debt">{{ $money($debtOf($examen->monto, $examen->monto_pagado)) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="dk-empty">Sin exámenes pendientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── MENSUALIDADES + ÚLTIMOS PAGOS ── --}}
        <div class="row">
            <div class="col-md-7">
                <div class="dk-card">
                    <div class="dk-card-header dk-accent-amber">
                        <span class="dk-card-title"><i class="voyager-calendar"></i> Mensualidades pendientes</span>
                        <span class="dk-badge">{{ $deudasMensualidades->count() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="dk-table">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Período</th>
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
                                        <td class="dk-period">
                                            {{ optional($mensualidad->periodo)->format('d/m/Y') }}
                                            @if ($mensualidad->fecha_fin)
                                                <span class="dk-sep">→</span>{{ optional($mensualidad->fecha_fin)->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td><span class="dk-pill dk-pill-amber">{{ $mensualidad->estadoPago() }}</span></td>
                                        <td class="text-right">{{ $money($mensualidad->total()) }}</td>
                                        <td class="text-right dk-paid">{{ $money($mensualidad->monto_pagado) }}</td>
                                        <td class="text-right"><strong class="dk-debt">{{ $money($mensualidad->saldo()) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="dk-empty">Sin mensualidades pendientes</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="dk-card">
                    <div class="dk-card-header dk-accent-green">
                        <span class="dk-card-title"><i class="voyager-check"></i> Últimos pagos de mensualidad</span>
                        <span class="dk-badge">{{ $pagosMensualidades->count() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="dk-table">
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
                                        <td class="text-right dk-paid"><strong>{{ $money($pago->monto) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="dk-empty">Sin pagos recientes</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>
@stop

@section('css')
<style>
    /* ── VARIABLES ── */
    :root {
        --dk-bg:      #f4f6f9;
        --dk-card:    #ffffff;
        --dk-border:  #e8ecf1;
        --dk-text:    #1e2535;
        --dk-muted:   #6b7a99;
        --dk-blue:    #3b82f6;
        --dk-amber:   #f59e0b;
        --dk-red:     #ef4444;
        --dk-green:   #22c55e;
        --dk-radius:  10px;
    }

    /* ── HEADER ── */
    .dk-header { padding: 18px 20px 14px; }
    .dk-header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .dk-header-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dk-text);
        line-height: 1.2;
    }
    .dk-header-sub { margin-top: 4px; }
    .dk-dojo-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--dk-muted);
    }

    .dk-dojo-form { margin: 0; }
    .dk-dojo-select {
        padding: 7px 32px 7px 12px;
        border: 1px solid var(--dk-border);
        border-radius: 7px;
        background: #fff;
        font-size: 13px;
        color: var(--dk-text);
        min-width: 200px;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7a99' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }
    .dk-dojo-select:focus { outline: none; border-color: var(--dk-blue); }

    .dk-dojo-fixed {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--dk-muted);
        padding: 7px 12px;
        border: 1px solid var(--dk-border);
        border-radius: 7px;
        background: #fff;
    }
    .dk-dojo-fixed strong { color: var(--dk-text); }

    /* ── BODY ── */
    .dk-body { padding: 0 20px 24px; }

    /* ── STAT CARDS ── */
    .dk-stats {
        display: flex;
        gap: 14px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .dk-stat {
        flex: 1;
        min-width: 160px;
        background: var(--dk-card);
        border: 1px solid var(--dk-border);
        border-radius: var(--dk-radius);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .dk-stat-icon {
        width: 42px; height: 42px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .dk-stat-blue   { background: rgba(59,130,246,.12); color: var(--dk-blue); }
    .dk-stat-amber  { background: rgba(245,158,11,.12); color: var(--dk-amber); }
    .dk-stat-red    { background: rgba(239,68,68,.12);  color: var(--dk-red); }
    .dk-stat-green  { background: rgba(34,197,94,.12);  color: var(--dk-green); }

    .dk-stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dk-text);
        line-height: 1.2;
        white-space: nowrap;
    }
    .dk-stat-label {
        font-size: 11px;
        color: var(--dk-muted);
        margin-top: 2px;
    }

    /* ── CARDS ── */
    .dk-card {
        background: var(--dk-card);
        border: 1px solid var(--dk-border);
        border-radius: var(--dk-radius);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .dk-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 11px 16px;
        border-bottom: 1px solid var(--dk-border);
        border-left: 3px solid transparent;
    }
    .dk-accent-blue   { border-left-color: var(--dk-blue); }
    .dk-accent-amber  { border-left-color: var(--dk-amber); }
    .dk-accent-red    { border-left-color: var(--dk-red); }
    .dk-accent-green  { border-left-color: var(--dk-green); }

    .dk-card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--dk-text);
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .dk-card-title i { color: var(--dk-muted); font-size: 15px; }

    .dk-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px; height: 22px;
        padding: 0 7px;
        border-radius: 999px;
        background: #f1f5f9;
        color: var(--dk-muted);
        font-size: 11px;
        font-weight: 600;
    }

    /* ── TABLE ── */
    .dk-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .dk-table thead th {
        padding: 9px 16px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--dk-muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--dk-border);
        white-space: nowrap;
    }
    .dk-table tbody td {
        padding: 10px 16px;
        font-size: 13px;
        color: var(--dk-text);
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .dk-table tbody tr:last-child td { border-bottom: none; }
    .dk-table tbody tr:hover td { background: #fafbfd; }

    .dk-debt  { color: var(--dk-red); }
    .dk-paid  { color: var(--dk-green); }
    .dk-period { font-size: 12px; white-space: nowrap; }
    .dk-sep { margin: 0 3px; color: var(--dk-muted); }

    .dk-empty {
        text-align: center;
        padding: 28px 16px !important;
        color: var(--dk-muted);
        font-size: 13px;
    }

    .dk-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 500;
    }
    .dk-pill-amber { background: rgba(245,158,11,.12); color: #b45309; }

    .dk-btn-sm {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 5px;
        background: #f1f5f9;
        color: var(--dk-muted);
        font-size: 11px;
        font-weight: 500;
        text-decoration: none;
        transition: .15s;
    }
    .dk-btn-sm:hover { background: var(--dk-blue); color: #fff; text-decoration: none; }

    @media (max-width: 767px) {
        .dk-stats { flex-direction: column; }
        .dk-stat  { min-width: unset; }
    }
</style>
@stop
