@php
    $person      = $alumno->person;
    $dojo        = $alumno->dojo;
    $logo        = $dojo && $dojo->logo ? asset('storage/' . $dojo->logo) : asset('images/default.jpg');
    $photo       = asset('images/default.jpg');
    if (optional($person)->image) {
        $photoPath = public_path('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp');
        $photo = file_exists($photoPath)
            ? asset('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp')
            : asset('storage/' . $person->image);
    }
    $nombre      = optional($person)->first_name ?? 'Sin nombre';
    $ci          = optional($person)->ci ? (optional($person)->documentType ?? 'CI') . ': ' . $person->ci : 'No registrado';
    $fechaIngreso = $alumno->fechaIngreso ? \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') : 'No registrado';
    $dojoNombre  = optional($dojo)->nombre ?? 'No registrado';
    $activo      = (int) $alumno->status === 1;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial del Alumno - {{ $nombre }}</title>
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
            width: 900px;
        }
        .print-document { width: 100%; border-collapse: collapse; }
        .print-document > thead > tr > td,
        .print-document > tbody > tr > td { padding: 0; }

        /* ── Encabezado ── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .header-logo  { width: 90px; vertical-align: middle; }
        .header-logo img { width: 80px; height: auto; }
        .header-center { text-align: center; vertical-align: middle; }
        .header-center .dojo-name { font-size: 22px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .header-center .doc-title { font-size: 14px; letter-spacing: 2px; text-transform: uppercase; margin-top: 2px; }
        .header-right  { text-align: right; vertical-align: top; font-size: 11px; color: #555; width: 140px; }

        /* ── Secciones ── */
        .section-header {
            background: #d9d9d9;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 1px;
            padding: 6px 10px;
            text-transform: uppercase;
            margin-top: 16px;
        }
        .section-body { padding: 12px 4px 4px; }

        /* ── Datos alumno ── */
        .datos-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .datos-table td { vertical-align: top; }
        .foto-col { width: 100px; padding-right: 14px; }
        .foto-col img { width: 88px; height: 110px; object-fit: cover; border: 1px solid #bbb; }
        .foto-label { font-size: 9px; color: #777; text-transform: uppercase; font-weight: bold; margin-bottom: 3px; }

        .alumno-name-row { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px; }
        .alumno-name { font-size: 19px; font-weight: bold; }
        .badge-activo   { background: #2ecc71; color: #fff; font-size: 11px; font-weight: bold; padding: 3px 12px; border-radius: 3px; }
        .badge-inactivo { background: #e74c3c; color: #fff; font-size: 11px; font-weight: bold; padding: 3px 12px; border-radius: 3px; }

        .info-grid { display: flex; flex-wrap: wrap; gap: 6px; }
        .info-box { background: #f2f2f2; border: 1px solid #e0e0e0; border-radius: 3px; flex: 1; min-width: 130px; padding: 6px 10px; }
        .info-label { font-size: 9px; font-weight: bold; color: #777; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 2px; }
        .info-value { font-size: 12px; color: #222; }

        /* ── Tarjeta por grado ── */
        .grado-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .grado-card-header {
            background: #eaf0fb;
            border-bottom: 1px solid #ddd;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
        }
        .grado-card-header.completado { background: #eafaf1; border-color: #c3e6cb; }
        .grado-card-header.en-curso   { background: #fff8e1; border-color: #ffe082; }

        .grado-titulo { font-size: 14px; font-weight: bold; color: #1a3a5c; }
        .grado-titulo.completado { color: #155724; }
        .grado-titulo.en-curso   { color: #7b5800; }

        .grado-meta { font-size: 11px; color: #666; margin-top: 2px; }

        .badge-completado { background: #2ecc71; color: #fff; font-size: 10px; font-weight: bold; padding: 2px 10px; border-radius: 3px; }
        .badge-en-curso   { background: #f39c12; color: #fff; font-size: 10px; font-weight: bold; padding: 2px 10px; border-radius: 3px; }

        .puntas-inline { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #555; }
        .puntas-bar-wrap { background: #e0e0e0; border-radius: 3px; height: 7px; width: 80px; }
        .puntas-bar { background: #2ecc71; border-radius: 3px; height: 7px; }
        .puntas-bar.partial { background: #f39c12; }

        .grado-card-body { padding: 10px 12px; }

        /* sub-tablas */
        .sub-label {
            font-size: 10px;
            font-weight: bold;
            color: #555;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
            margin-top: 10px;
            border-left: 3px solid #8e44ad;
            padding-left: 6px;
        }
        .sub-label.examen { border-color: #e74c3c; }

        .sub-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .sub-table th {
            background: #f7f7f7;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.4px;
            padding: 5px 8px;
            text-align: left;
            text-transform: uppercase;
            color: #777;
        }
        .sub-table td { border-bottom: 1px solid #f0f0f0; padding: 5px 8px; vertical-align: middle; color: #333; }
        .sub-table tr:last-child td { border-bottom: none; }

        .lbl-punta    { background: #2ecc71; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 2px; }
        .lbl-noaprob  { background: #bbb;    color: #fff; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 2px; }
        .lbl-aprobado { background: #27ae60; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 2px; }
        .lbl-aplazado { background: #e74c3c; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 2px; }
        .lbl-pagado   { background: #27ae60; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 2px; }
        .lbl-pendiente{ background: #e67e22; color: #fff; font-size: 9px; font-weight: bold; padding: 1px 6px; border-radius: 2px; }

        .sin-registros { color: #aaa; font-size: 11px; padding: 6px 8px; }

        .ninguno { text-align: center; color: #888; padding: 18px 0; font-size: 13px; }
        .page-number::before { content: "1"; }
        .page-total::before { content: "1"; }

        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .sheet { margin: 0; padding: 16px 20px; width: 100%; }
            .print-document > thead { display: table-header-group; }
            .print-document > tfoot { display: table-footer-group; }
            .print-document > tbody { display: table-row-group; }
            .header-table { margin-bottom: 12px; }
            .grado-card { page-break-inside: avoid; }
            .page-number::before { content: counter(page); }
            .page-total::before { content: counter(pages); }
            @page { size: letter; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button onclick="window.print()">&#128438; Imprimir</button>
    <a href="#" onclick="window.close(); return false;">&#8592; Cerrar</a>
</div>

<div class="sheet">
    <table class="print-document">
        <thead>
            <tr>
                <td>
                    {{-- ── Encabezado repetido por hoja ── --}}
                    <table class="header-table">
                        <tr>
                            <td class="header-logo"><img src="{{ $logo }}" alt="Logo"></td>
                            <td class="header-center">
                                <div class="dojo-name">{{ strtoupper($dojoNombre) }}</div>
                                <div class="doc-title">Historial del Alumno</div>
                            </td>
                            <td class="header-right">
                                Impreso<br>
                                {{ now()->format('d/m/Y') }}<br>
                                {{ now()->format('g:i a') }}<br>
                                Página <span class="page-number"></span> de <span class="page-total"></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>

    {{-- ── Datos del alumno ── --}}
    <div class="section-header">Alumno</div>
    <div class="section-body">
        <table class="datos-table">
            <tr>
                <td class="foto-col">
                    <div class="foto-label">Foto</div>
                    <img src="{{ $photo }}" alt="Foto">
                </td>
                <td>
                    <div class="alumno-name-row">
                        <div class="alumno-name">{{ $nombre }} - 
                            @if($activo)
                                Activo
                            @else
                                Inactivo
                            @endif
                        </div>
                        
                    </div>
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Dojo</div>
                            <div class="info-value">{{ $dojoNombre }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Documento</div>
                            <div class="info-value">{{ $ci }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Fecha de Ingreso</div>
                            <div class="info-value">{{ $fechaIngreso }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Total de Grados</div>
                            <div class="info-value">{{ $grados->count() }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Historial de Grados ── --}}
    <div class="section-header">Historial del alumno</div>
    <div class="section-body" style="padding-top:10px;">
        @forelse($grados as $i => $ag)
        @php
            $g          = $ag->grado;
            $gradoLabel = $g ? trim(($g->tipo ?? '') . ' ' . ($g->numero ?? '') . ' ' . ($g->nombre ?? '')) : 'Grado sin nombre';
            $completado = $ag->isCompletado();
            $puntasReq  = $g ? (int) $g->puntas : 0;
            $repasos    = $ag->repasos->sortBy('fecha');
            $examenes   = $ag->examenes->sortBy('fecha');
            $puntasObt  = $repasos->where('aprobado', 1)->count();
            $pct        = $puntasReq > 0 ? min(100, round($puntasObt / $puntasReq * 100)) : 100;
        @endphp

        <div class="grado-card">

            {{-- Header del grado --}}
            <div class="grado-card-header {{ $completado ? 'completado' : 'en-curso' }}">
                <div>
                    <div class="grado-titulo {{ $completado ? 'completado' : 'en-curso' }}">
                        {{ $i + 1 }}. {{ $gradoLabel }}
                    </div>
                    <div class="grado-meta">
                        Inicio: {{ \Carbon\Carbon::parse($ag->fecha)->format('d/m/Y') }}
                        @if($ag->observacion)
                            &nbsp;·&nbsp; {{ $ag->observacion }}
                        @endif
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="puntas-inline">
                        Puntas: {{ $puntasObt }}/{{ $puntasReq }}
                        <div class="puntas-bar-wrap">
                            <div class="puntas-bar {{ $pct < 100 ? 'partial' : '' }}" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                    @if($completado)
                        <span class="badge-completado">Completado</span>
                    @else
                        <span class="badge-en-curso">En curso</span>
                    @endif
                </div>
            </div>

            <div class="grado-card-body">

                {{-- Repasos --}}
                <div class="sub-label">Repasos ({{ $repasos->count() }})</div>
                @if($repasos->count())
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Resultado</th>
                            <th style="text-align:right;">Monto</th>
                            <th style="text-align:right;">Pagado</th>
                            <th style="text-align:right;">Saldo</th>
                            <th>Pago</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repasos as $j => $r)
                        @php
                            $monto  = (float) ($r->monto ?? 0);
                            $pagado = (float) ($r->monto_pagado ?? 0);
                            $saldo  = max(0, $monto - $pagado);
                            $esPagado = $monto <= 0 || $pagado >= $monto;
                        @endphp
                        <tr>
                            <td style="color:#aaa;">{{ $j + 1 }}</td>
                            <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }}</td>
                            <td>
                                @if($r->aprobado)
                                    <span class="lbl-punta">Punta</span>
                                @else
                                    <span class="lbl-noaprob">No aprobado</span>
                                @endif
                            </td>
                            <td style="text-align:right;">Bs {{ number_format($monto, 2) }}</td>
                            <td style="text-align:right;">Bs {{ number_format($pagado, 2) }}</td>
                            <td style="text-align:right;">Bs {{ number_format($saldo, 2) }}</td>
                            <td>
                                @if($esPagado)
                                    <span class="lbl-pagado">Pagado</span>
                                @else
                                    <span class="lbl-pendiente">Pendiente</span>
                                @endif
                            </td>
                            <td style="color:#666;">{{ $r->observacion ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="sin-registros">Sin repasos registrados.</div>
                @endif

                {{-- Exámenes --}}
                <div class="sub-label examen">Exámenes ({{ $examenes->count() }})</div>
                @if($examenes->count())
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Resultado</th>
                            <th style="text-align:right;">Monto</th>
                            <th style="text-align:right;">Pagado</th>
                            <th style="text-align:right;">Saldo</th>
                            <th>Pago</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examenes as $j => $ex)
                        @php
                            $monto  = (float) ($ex->monto ?? 0);
                            $pagado = (float) ($ex->monto_pagado ?? 0);
                            $saldo  = max(0, $monto - $pagado);
                            $esPagado = $monto <= 0 || $pagado >= $monto;
                        @endphp
                        <tr>
                            <td style="color:#aaa;">{{ $j + 1 }}</td>
                            <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($ex->fecha)->format('d/m/Y') }}</td>
                            <td>
                                @if($ex->aprobado)
                                    <span class="lbl-aprobado">Aprobado</span>
                                @else
                                    <span class="lbl-aplazado">Aplazado</span>
                                @endif
                            </td>
                            <td style="text-align:right;">Bs {{ number_format($monto, 2) }}</td>
                            <td style="text-align:right;">Bs {{ number_format($pagado, 2) }}</td>
                            <td style="text-align:right;">Bs {{ number_format($saldo, 2) }}</td>
                            <td>
                                @if($esPagado)
                                    <span class="lbl-pagado">Pagado</span>
                                @else
                                    <span class="lbl-pendiente">Pendiente</span>
                                @endif
                            </td>
                            <td style="color:#666;">{{ $ex->observacion ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="sin-registros">Sin exámenes registrados.</div>
                @endif

            </div>{{-- /grado-card-body --}}
        </div>{{-- /grado-card --}}
        @empty
            <div class="ninguno">Sin grados registrados.</div>
        @endforelse
    </div>

                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>
