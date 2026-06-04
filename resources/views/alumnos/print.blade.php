@php
    $logo = null;
    if ($dojo && $dojo->logo) {
        $logoPath = public_path('storage/' . $dojo->logo);
        if (file_exists($logoPath)) {
            $mime = getimagesize($logoPath)['mime'] ?? 'image/jpeg';
            $logo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
    $dojoNombre = $dojo ? $dojo->nombre : 'Todos los Dojos';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alumnos por Grado</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #ccc;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
        }
        .actions {
            padding: 10px 20px;
            text-align: right;
            background: #555;
        }
        .actions button, .actions a {
            background: #fff;
            border: 1px solid #333;
            border-radius: 3px;
            color: #333;
            cursor: pointer;
            display: inline-block;
            font-size: 13px;
            margin-left: 6px;
            padding: 7px 18px;
            text-decoration: none;
        }
        .sheet {
            background: #fff;
            margin: 18px auto;
            padding: 28px 32px;
            width: 960px;
        }

        /* ── Encabezado ── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .header-logo  { width: 90px; vertical-align: middle; }
        .header-logo img { width: 80px; height: auto; }
        .header-center { text-align: center; vertical-align: middle; }
        .header-center .dojo-name { font-size: 22px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .header-center .doc-title { font-size: 14px; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px; }
        .header-right  { text-align: right; vertical-align: top; font-size: 11px; color: #555; width: 140px; }

        /* ── Filtros aplicados ── */
        .filtros {
            background: #f2f2f2;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            padding: 7px 12px;
            margin-bottom: 14px;
            font-size: 11px;
            color: #555;
        }
        .filtros span { margin-right: 16px; }
        .filtros strong { color: #333; }

        /* ── Tabla principal ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }
        .main-table th {
            background: #d9d9d9;
            border-bottom: 2px solid #bbb;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
            text-transform: uppercase;
        }
        .main-table td {
            border-bottom: 1px solid #eee;
            font-size: 12px;
            padding: 8px 10px;
            vertical-align: middle;
        }
        .main-table tr:last-child td { border-bottom: none; }
        .main-table tr:nth-child(even) td { background: #fafafa; }

        /* Badges */
        .b-activo      { background: #2ecc71; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 7px; border-radius: 2px; }
        .b-inactivo    { background: #e74c3c; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 7px; border-radius: 2px; }
        .b-completado  { background: #27ae60; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 7px; border-radius: 2px; }
        .b-en-curso    { background: #f39c12; color: #fff; font-size: 9px; font-weight: bold; padding: 2px 7px; border-radius: 2px; }
        .b-sin-grado   { background: #bbb;    color: #fff; font-size: 9px; font-weight: bold; padding: 2px 7px; border-radius: 2px; }

        /* Barra de puntas */
        .puntas-wrap { display: flex; align-items: center; gap: 5px; }
        .bar-outer { background: #e0e0e0; border-radius: 3px; height: 7px; width: 70px; }
        .bar-inner { border-radius: 3px; height: 7px; }
        .bar-ok     { background: #2ecc71; }
        .bar-parcial{ background: #f39c12; }

        .ninguno { text-align: center; color: #888; padding: 30px 0; font-size: 14px; }

        .total-row td {
            background: #f2f2f2 !important;
            font-weight: bold;
            font-size: 11px;
            color: #555;
            border-top: 2px solid #ccc;
        }

        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .sheet { margin: 0; padding: 16px 20px; width: 100%; }
            @page { size: letter landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button onclick="window.print()">&#128438; Imprimir</button>
    <a href="#" onclick="window.close(); return false;">&#8592; Cerrar</a>
</div>

<div class="sheet">

    {{-- ── Encabezado ── --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo">
                @endif
            </td>
            <td class="header-center">
                <div class="dojo-name">{{ strtoupper($dojoNombre) }}</div>
                <div class="doc-title">Lista de Alumnos por Grado</div>
            </td>
            <td class="header-right">
                Impreso<br>
                {{ now()->format('d/m/Y') }}<br>
                {{ now()->format('g:i a') }}<br>
                Total: {{ $alumnos->count() }} alumno(s)
            </td>
        </tr>
    </table>

    {{-- ── Filtros aplicados ── --}}
    <div class="filtros">
        <span><strong>Dojo:</strong> {{ $dojoNombre }}</span>
        <span><strong>Grado:</strong> {{ $gradoFiltro ? trim(($gradoFiltro->tipo ?? '') . ' ' . ($gradoFiltro->numero ?? '') . ' ' . ($gradoFiltro->nombre ?? '')) : 'Todos' }}</span>
    </div>

    {{-- ── Tabla ── --}}
    @if($alumnos->count())
    <table class="main-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Alumno</th>
                @if($esGlobal && !$dojo)
                <th>Dojo</th>
                @endif
                <th>Ingreso</th>
                <th>Ingreso grado</th>
                <th>Último Grado</th>
                <th>Estado Grado</th>
                <th>Puntas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $i => $alumno)
            @php
                $ug         = $alumno->ultimoGrado;
                $g          = optional($ug)->grado;
                $gradoLabel = $g ? trim(($g->tipo ?? '') . ' ' . ($g->numero ?? '') . ' ' . ($g->nombre ?? '')) : null;
                $completado = $ug ? $ug->isCompletado() : null;
                $puntasReq  = $g ? (int) $g->puntas : 0;
                $puntasObt  = $ug ? $ug->repasos->where('aprobado', 1)->count() : 0;
                $pct        = $puntasReq > 0 ? min(100, round($puntasObt / $puntasReq * 100)) : ($ug ? 100 : 0);
            @endphp
            <tr>
                <td style="color:#aaa; font-size:11px;">{{ $i + 1 }}</td>
                <td style="font-weight:bold;">{{ optional($alumno->person)->first_name ?? '—' }}</td>
                @if($esGlobal && !$dojo)
                <td style="font-size:11px; color:#555;">{{ optional($alumno->dojo)->nombre ?? '—' }}</td>
                @endif
                <td style="white-space:nowrap; font-size:11px;">
                    {{ $alumno->fechaIngreso ? \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') : '—' }}
                </td>
                <td style="white-space:nowrap; font-size:11px;">
                    {{ $ug && $ug->fecha ? \Carbon\Carbon::parse($ug->fecha)->format('d/m/Y') : '—' }}
                </td>
                <td>
                    @if($gradoLabel)
                        {{ $gradoLabel }}
                    @else
                        <span class="b-sin-grado">Sin grado</span>
                    @endif
                </td>
                <td>
                    @if($ug === null)
                        <span class="b-sin-grado">—</span>
                    @elseif($completado)
                        <span class="b-completado">Completado</span>
                    @else
                        <span class="b-en-curso">En curso</span>
                    @endif
                </td>
                <td>
                    @if($ug)
                    <div class="puntas-wrap">
                        <span style="font-size:11px; min-width:32px;">{{ $puntasObt }}/{{ $puntasReq }}</span>
                        <div class="bar-outer">
                            <div class="bar-inner {{ $pct >= 100 ? 'bar-ok' : 'bar-parcial' }}" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                    @else
                        <span style="color:#bbb;">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="{{ ($esGlobal && !$dojo) ? 8 : 7 }}">
                    Total: {{ $alumnos->count() }} alumno(s)
                </td>
            </tr>
        </tfoot>
    </table>
    @else
        <div class="ninguno">No se encontraron alumnos con los filtros seleccionados.</div>
    @endif

</div>
</body>
</html>
