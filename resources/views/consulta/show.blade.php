@extends('voyager::master')

@section('page_title', 'Consulta Alumno')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding:0;">
                        <div class="col-md-6" style="padding:0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-magnifying-glass-location"></i> Consulta de Alumno
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top:30px;">
                            <a href="{{ route('consulta.index') }}" class="btn btn-warning btn-sm">
                                <i class="voyager-list"></i> Volver a Consulta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
@php
    $person   = $alumno->person;
    $dojoData = $alumno->dojo;
    $image    = asset('images/default.jpg');
    if (optional($person)->image) {
        $image = asset('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp');
    }
@endphp

<div class="page-content read container-fluid">

    {{-- ── Encabezado del alumno ── --}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered alumno-summary-panel">
                <div class="panel-body">

                    <div class="alert alert-info" style="font-size:12px; margin-bottom:12px;">
                        <i class="fa-solid fa-circle-info"></i>
                        Vista de solo lectura — información del alumno en <strong>{{ optional($dojoData)->nombre ?? 'otro dojo' }}</strong>.
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="alumno-photo-card">
                                <div class="alumno-photo-title">Foto del Alumno</div>
                                <div class="alumno-photo-frame">
                                    @if (optional($person)->image && \Illuminate\Support\Str::endsWith($person->image, ['.jpg', '.jpeg', '.png', '.webp', '.avif']))
                                        <img src="{{ $image }}" alt="{{ optional($person)->first_name }}" class="alumno-photo-img"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="alumno-photo-fallback" style="display:none;">
                                            <i class="fa-solid fa-user-graduate"></i>
                                        </div>
                                    @else
                                        <div class="alumno-photo-fallback">
                                            <i class="fa-solid fa-user-graduate"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="alumno-info-header">
                                <div>
                                    <div class="alumno-label">Alumno</div>
                                    <h3 class="alumno-name">{{ optional($person)->first_name ?: 'Persona no disponible' }}</h3>
                                </div>
                                <div class="alumno-status-wrap">
                                    <span class="label {{ $alumno->status == 1 ? 'label-success' : 'label-warning' }} alumno-status-label">
                                        {{ $alumno->status == 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Dojo</div>
                                        <div class="alumno-value">{{ optional($dojoData)->nombre ?: 'Sin dojo asignado' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Fecha de Ingreso</div>
                                        <div class="alumno-value">{{ $alumno->fechaIngreso ? \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') : 'No registrada' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Documento</div>
                                        <div class="alumno-value">{{ optional($person)->documentType ?: 'N/A' }}: {{ optional($person)->ci ?: 'No registrado' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Género</div>
                                        <div class="alumno-value">{{ optional($person)->gender ?: 'No registrado' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Fecha de Nacimiento</div>
                                        <div class="alumno-value">{{ optional($person)->birth_date ? \Carbon\Carbon::parse($person->birth_date)->format('d/m/Y') : 'No registrada' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Teléfono</div>
                                        <div class="alumno-value">
                                            @if(optional($person)->phone)
                                                +{{ $person->country_code ?: '591' }} {{ $person->phone }}
                                            @else
                                                No registrado
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Correo</div>
                                        <div class="alumno-value">{{ optional($person)->email ?: 'No registrado' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Dirección</div>
                                        <div class="alumno-value">{{ optional($person)->address ?: 'No registrada' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ── Grado en Progreso ── --}}
        <div class="col-md-12">
            <div class="panel panel-bordered" style="border-color:#3498db !important;">
                <div class="panel-heading" style="background:linear-gradient(90deg,#ebf5fb,#fafcfe); border-bottom:1px solid #d6eaf8; padding:12px 15px;">
                    <h4 style="margin:0; font-weight:700;">
                        <i class="fa-solid fa-award" style="color:#3498db;"></i> Grado en Progreso
                    </h4>
                </div>
                <div class="panel-body">
                @if($activeGrado && $activeGrado->grado)
                    @php
                        $g = $activeGrado->grado;
                        $gradoLabel = trim(($g->tipo ?? '') . ' ' . ($g->numero ?? '') . ' ' . ($g->nombre ?? ''));
                        $porcentajePuntas = $progress['puntasRequeridas'] > 0
                            ? min(100, round($progress['puntasObtenidas'] / $progress['puntasRequeridas'] * 100))
                            : 100;
                    @endphp

                    <div class="row">
                        <div class="col-md-4">
                            <p style="font-size:15px; font-weight:700; margin-bottom:4px;">{{ $gradoLabel ?: 'Sin nombre' }}</p>
                            <p class="text-muted" style="font-size:13px; margin:0;">
                                Inicio: <strong>{{ \Carbon\Carbon::parse($activeGrado->fecha)->format('d/m/Y') }}</strong>
                            </p>
                            @if($progress['isComplete'])
                                <span class="label label-success" style="margin-top:6px; display:inline-block;">
                                    <i class="fa-solid fa-check-circle"></i> Completado
                                </span>
                            @elseif($progress['puedeExamen'])
                                <span class="label label-warning" style="margin-top:6px; display:inline-block;">
                                    <i class="fa-solid fa-clock"></i> Listo para Examen
                                </span>
                            @else
                                <span class="label label-default" style="margin-top:6px; display:inline-block;">
                                    <i class="fa-solid fa-spinner"></i> Acumulando puntas
                                </span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div style="background:#fafcfe; border:1px solid #edf2f7; border-radius:6px; padding:10px 12px;">
                                <div style="font-size:13px; color:#555; margin-bottom:6px;">
                                    <i class="fa-solid fa-star" style="color:#f39c12;"></i>
                                    Puntas (Repasos aprobados)
                                    <strong class="pull-right">{{ $progress['puntasObtenidas'] }} / {{ $progress['puntasRequeridas'] }}</strong>
                                </div>
                                <div class="progress" style="height:12px; border-radius:6px; margin-bottom:4px;">
                                    <div class="progress-bar {{ $progress['cumplePuntas'] ? 'progress-bar-success' : 'progress-bar-warning' }}"
                                         style="width:{{ $porcentajePuntas }}%; min-width:18px;">
                                        {{ $porcentajePuntas }}%
                                    </div>
                                </div>
                                @if($progress['cumplePuntas'])
                                    <small class="text-success"><i class="fa-solid fa-check"></i> Puntas completadas</small>
                                @else
                                    <small class="text-muted">Faltan {{ $progress['puntasRequeridas'] - $progress['puntasObtenidas'] }} punta(s)</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:#fafcfe; border:1px solid #edf2f7; border-radius:6px; padding:10px 12px;">
                                <div style="font-size:13px; color:#555; margin-bottom:6px;">
                                    <i class="fa-solid fa-graduation-cap" style="color:#e74c3c;"></i>
                                    Examen Final
                                </div>
                                @if($progress['examenAprobado'])
                                    <span class="label label-success"><i class="fa-solid fa-check"></i> Aprobado</span>
                                @elseif($activeGrado->examenes->count())
                                    <span class="label label-danger"><i class="fa-solid fa-xmark"></i> Aplazado ({{ $activeGrado->examenes->count() }} intento(s))</span>
                                @else
                                    <small class="text-muted">Sin intentos aún</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted" style="margin:0;">Sin grado en progreso actualmente.</p>
                @endif
                </div>
            </div>
        </div>

        {{-- ── Historial de Grados Completados ── --}}
        <div class="col-md-8">
            <div class="panel panel-bordered">
                <div class="panel-heading" style="padding:12px 15px;">
                    <h4 style="margin:0; font-weight:700;">
                        <i class="fa-solid fa-list-check" style="color:#27ae60;"></i> Historial de Grados
                    </h4>
                </div>
                <div class="panel-body" style="padding:0;">
                    @if($gradosCompletados->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed" style="margin:0; font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Grado</th>
                                    <th style="width:110px;">Fecha Inicio</th>
                                    <th style="width:80px; text-align:center;">Puntas</th>
                                    <th style="width:110px; text-align:center;">Examen Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradosCompletados as $ag)
                                @php
                                    $gLabel     = trim(($ag->grado->tipo ?? '') . ' ' . ($ag->grado->numero ?? '') . ' ' . ($ag->grado->nombre ?? ''));
                                    $puntasObt  = $ag->repasos->where('aprobado', 1)->count();
                                    $puntasReq  = $ag->grado ? (int) $ag->grado->puntas : 0;
                                    $examenApro = $ag->examenes->where('aprobado', 1)->first();
                                @endphp
                                <tr>
                                    <td><strong>{{ $gLabel ?: 'Grado no disponible' }}</strong></td>
                                    <td>{{ $ag->fecha ? \Carbon\Carbon::parse($ag->fecha)->format('d/m/Y') : '—' }}</td>
                                    <td style="text-align:center;">
                                        <span class="label label-success">{{ $puntasObt }}/{{ $puntasReq }}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if($examenApro)
                                            <span class="label label-success"><i class="fa-solid fa-check"></i> {{ \Carbon\Carbon::parse($examenApro->fecha)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="label label-default">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div style="padding:16px;">
                            <p class="text-muted" style="margin:0;">Sin grados completados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Condiciones de Salud ── --}}
        <div class="col-md-4">
            <div class="panel panel-bordered">
                <div class="panel-heading" style="padding:12px 15px;">
                    <h4 style="margin:0; font-weight:700;">
                        <i class="fa-solid fa-briefcase-medical" style="color:#e74c3c;"></i> Condiciones de Salud
                    </h4>
                </div>
                <div class="panel-body" style="padding:0;">
                    @if($enfermedades->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed" style="margin:0; font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Condición</th>
                                    <th>Medicamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enfermedades as $enf)
                                <tr>
                                    <td>{{ $enf->nombre ?: '—' }}</td>
                                    <td>{{ $enf->medicamento ?: '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div style="padding:16px;">
                            <p class="text-muted" style="margin:0;">Sin condiciones de salud registradas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@stop
