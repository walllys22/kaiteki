@extends('voyager::master')

@section('page_title', 'Detalle de Asistencia')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0;">
                        <div class="col-md-6" style="padding: 0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-clipboard-user"></i> Detalle de Asistencia
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px;">
                            <a href="{{ route('voyager.asistencias.index') }}" class="btn btn-warning btn-sm">
                                <i class="voyager-list"></i> Volver
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
        $detalles  = $asistencia->detalles;
        $presentes = $detalles->where('estado', 'asistencia')->count();
        $licencias = $detalles->where('estado', 'licencia')->count();
        $faltas    = $detalles->where('estado', 'falta')->count();
        $total     = $detalles->count();
        $tieneAlumnosActivos = $detalles->contains(fn($det) => $det->alumno && (int) $det->alumno->status === 1 && !$det->alumno->deleted_at);
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">

                {{-- Cabecera --}}
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="alumno-data-card">
                                    <div class="alumno-label">Fecha</div>
                                    <div class="alumno-value" style="font-size:18px; font-weight:700;">
                                        {{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}
                                        <br><small class="text-muted" style="font-size:13px;">
                                            {{ \Carbon\Carbon::parse($asistencia->fecha)->locale('es')->isoFormat('dddd') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alumno-data-card">
                                    <div class="alumno-label">Horario</div>
                                    <div class="alumno-value">
                                        {{ optional($asistencia->horario)->nombre ?: '—' }}
                                        @if(optional($asistencia->horario)->tipo)
                                            <br><small class="text-muted">{{ $asistencia->horario->tipo }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alumno-data-card">
                                    <div class="alumno-label">Dojo / Sucursal</div>
                                    <div class="alumno-value">{{ optional($asistencia->dojo)->nombre ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alumno-data-card">
                                    <div class="alumno-label">Resumen</div>
                                    <div class="alumno-value">
                                        <span class="label label-success">✓ {{ $presentes }} presentes</span>
                                        <span class="label label-warning">📋 {{ $licencias }} licencias</span>
                                        <span class="label label-danger">✗ {{ $faltas }} faltas</span>
                                        <br><small class="text-muted">{{ $total }} alumnos en total</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:5px;">
                            <div class="col-md-12">
                                <div class="alumno-data-card">
                                    <div class="alumno-label">Registrado por</div>
                                    <div class="alumno-value">
                                        <strong>{{ optional($asistencia->register)->name ?: '—' }}</strong>
                                        <br><small class="text-muted">
                                            {{ $asistencia->created_at ? $asistencia->created_at->format('d/m/Y H:i') : '—' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($asistencia->observacion)
                            <div class="row" style="margin-top:5px;">
                                <div class="col-md-12">
                                    <div class="alumno-data-card">
                                        <div class="alumno-label">Observaciones</div>
                                        <div class="alumno-value">{{ $asistencia->observacion }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tabla de asistencia --}}
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-users"></i> Lista de Alumnos
                        </h3>
                    </div>
                    <div class="panel-body">
                        @if(auth()->user()->hasPermission('edit_asistencias'))
                        <form action="{{ route('asistencias.update', $asistencia->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                        @endif

                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover asistencia-table" style="font-size:13px;">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Alumno</th>
                                        <th style="width:300px; text-align:center;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detalles->sortBy(fn($d) => optional(optional($d->alumno)->person)->first_name) as $idx => $det)
                                        @php
                                            $detAlumnoActivo = $det->alumno && (int) $det->alumno->status === 1 && !$det->alumno->deleted_at;
                                        @endphp
                                        <tr>
                                            <td style="vertical-align:middle;">{{ $idx + 1 }}</td>
                                            <td style="vertical-align:middle;">
                                                <strong>{{ optional(optional($det->alumno)->person)->first_name ?? 'Sin nombre' }}</strong>
                                                @if(!$detAlumnoActivo)
                                                    <span class="label label-default" style="margin-left:6px;">Inactivo</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                @if(auth()->user()->hasPermission('edit_asistencias') && $detAlumnoActivo)
                                                    <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                                        <input type="radio" class="estado-radio radio-asistencia" name="estados[{{ $det->id }}]" id="a_{{ $det->id }}" value="asistencia" {{ $det->estado === 'asistencia' ? 'checked' : '' }}>
                                                        <label class="estado-label" for="a_{{ $det->id }}" style="color:#27ae60; border-color:#27ae60;">✓ Asistencia</label>

                                                        <input type="radio" class="estado-radio radio-licencia" name="estados[{{ $det->id }}]" id="l_{{ $det->id }}" value="licencia" {{ $det->estado === 'licencia' ? 'checked' : '' }}>
                                                        <label class="estado-label" for="l_{{ $det->id }}" style="color:#e67e22; border-color:#e67e22;">📋 Licencia</label>

                                                        <input type="radio" class="estado-radio radio-falta" name="estados[{{ $det->id }}]" id="f_{{ $det->id }}" value="falta" {{ $det->estado === 'falta' ? 'checked' : '' }}>
                                                        <label class="estado-label" for="f_{{ $det->id }}" style="color:#c0392b; border-color:#c0392b;">✗ Falta</label>
                                                    </div>
                                                @else
                                                    @if($det->estado === 'asistencia')
                                                        <span class="label label-success" style="font-size:12px; padding:5px 10px;">✓ Asistencia</span>
                                                    @elseif($det->estado === 'licencia')
                                                        <span class="label label-warning" style="font-size:12px; padding:5px 10px;">📋 Licencia</span>
                                                    @else
                                                        <span class="label label-danger" style="font-size:12px; padding:5px 10px;">✗ Falta</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(auth()->user()->hasPermission('edit_asistencias') && $tieneAlumnosActivos)
                            <div class="text-right" style="margin-top:10px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Guardar cambios
                                </button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .alumno-label {
            color: #7a8a9a; font-size: 12px; font-weight: 600;
            letter-spacing: .04em; margin-bottom: 6px; text-transform: uppercase;
        }
        .alumno-value { color: #253443; font-size: 15px; line-height: 1.5; }
        .alumno-data-card {
            background: #fbfcfe; border: 1px solid #edf2f7;
            border-radius: 8px; margin-bottom: 12px; min-height: 70px; padding: 12px 14px;
        }
        .asistencia-table th { background: #f7fafd; }
        .estado-radio { display: none; }
        .estado-label {
            cursor: pointer;
            border: 1.5px solid;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            transition: background .15s, color .15s;
            user-select: none;
        }
        .estado-radio:checked + .estado-label { color: #fff; border-color: transparent; }
        .radio-asistencia:checked + .estado-label { background: #27ae60; }
        .radio-licencia:checked  + .estado-label { background: #e67e22; }
        .radio-falta:checked    + .estado-label { background: #c0392b; }
        .estado-label:hover { opacity: .85; }
    </style>
@stop
