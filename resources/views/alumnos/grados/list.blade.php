{{-- Comunicar al JS de read.blade.php si se puede agregar nuevo grado y la fecha mínima --}}
<input type="hidden" id="puede-agregar-grado" value="{{ $puedeAgregarGrado ? '1' : '0' }}">
<input type="hidden" id="active-grado-id" value="{{ $activeGrado ? $activeGrado->id : '' }}">
<input type="hidden" id="min-fecha-grado" value="{{ $minFechaGrado ?? '' }}">

{{-- ══════════════════════════════════════════════════════
     SECCIÓN: GRADO ACTIVO EN PROGRESO
     ══════════════════════════════════════════════════════ --}}
@if($activeGrado && $activeGrado->grado)
    @php
        $g = $activeGrado->grado;
        $gradoLabel = trim(($g->tipo ?? '') . ' ' . ($g->numero ?? '') . ' ' . ($g->nombre ?? ''));
        $repasos  = $activeGrado->repasos->sortByDesc('fecha');
        $examenes = $activeGrado->examenes->sortByDesc('fecha');
        $porcentajePuntas = $progress['puntasRequeridas'] > 0
            ? min(100, round($progress['puntasObtenidas'] / $progress['puntasRequeridas'] * 100))
            : 100;
        $porcentajeDias = $progress['diasRequeridos'] > 0
            ? min(100, round($progress['diasTranscurridos'] / $progress['diasRequeridos'] * 100))
            : 100;
    @endphp

    <div class="panel panel-bordered grado-activo-panel">
        <div class="panel-heading" style="padding: 12px 15px;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                <div>
                    <h4 style="margin:0; font-size:15px; font-weight:700;">
                        <i class="fa-solid fa-award" style="color:#3498db;"></i>
                        Grado en Progreso: <span style="color:#2c3e50;">{{ $gradoLabel ?: 'Sin nombre' }}</span> - <strong>{{ \Carbon\Carbon::parse($activeGrado->fecha)->format('d/m/Y') }}</strong>
                    </h4>
                </div>
                @if($progress['isComplete'])
                    <span class="label label-success" style="font-size:12px; padding:5px 10px;">
                        <i class="fa-solid fa-check-circle"></i> Completado — puede registrar el siguiente grado
                    </span>
                @elseif($progress['puedeExamen'])
                    <span class="label label-warning" style="font-size:12px; padding:5px 10px;">
                        <i class="fa-solid fa-clock"></i> Listo para Examen Final
                    </span>
                @else
                    <span class="label label-default" style="font-size:12px; padding:5px 10px;">
                        <i class="fa-solid fa-spinner"></i> Acumulando puntas
                    </span>
                @endif
            </div>
        </div>

        <div class="panel-body">
            {{-- ── Indicadores de Progreso ── --}}
            <div class="row" style="margin-bottom:15px;">
                <div class="col-md-6">
                    <div class="grado-progress-card">
                        <div class="grado-progress-label">
                            <i class="fa-solid fa-star" style="color:#f39c12;"></i>
                            Puntas (Repasos aprobados)
                            <strong class="pull-right">{{ $progress['puntasObtenidas'] }} / {{ $progress['puntasRequeridas'] }}</strong>
                        </div>
                        <div class="progress" style="margin-bottom:4px; height:12px;">
                            <div class="progress-bar {{ $progress['cumplePuntas'] ? 'progress-bar-success' : 'progress-bar-warning' }}"
                                 role="progressbar"
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
                <div class="col-md-6">
                    <div class="grado-progress-card">
                        <div class="grado-progress-label">
                            <i class="fa-solid fa-calendar-days" style="color:#3498db;"></i>
                            Asistencias desde el grado
                            <strong class="pull-right">{{ $progress['diasTranscurridos'] }} / {{ $progress['diasRequeridos'] }}</strong>
                        </div>
                        <div class="progress" style="margin-bottom:4px; height:12px;">
                            <div class="progress-bar {{ $progress['cumpleDias'] ? 'progress-bar-success' : 'progress-bar-info' }}"
                                 role="progressbar"
                                 style="width:{{ $porcentajeDias }}%; min-width:18px;">
                                {{ $porcentajeDias }}%
                            </div>
                        </div>
                        @if($progress['cumpleDias'])
                            <small class="text-success"><i class="fa-solid fa-check"></i> Referencia cumplida</small>
                        @else
                            <small class="text-muted">
                                {{ $progress['diasTranscurridos'] }} asistencia(s) desde {{ \Carbon\Carbon::parse($activeGrado->fecha)->format('d/m/Y') }}
                                <span class="label label-default" style="font-size:10px; margin-left:4px;">referencia</span>
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Repasos ── --}}
            <div class="row" style="margin-bottom:5px;">
                <div class="col-xs-12">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <h5 style="margin:0; font-weight:600;">
                            <i class="fa-solid fa-repeat" style="color:#8e44ad;"></i> Repasos
                            <small class="text-muted">({{ $repasos->count() }} total · {{ $repasos->where('aprobado', 1)->count() }} aprobados)</small>
                        </h5>
                        @if(!$activeGrado->isCompletado() && !$progress['cumplePuntas'])
                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-add-repaso">
                            <i class="voyager-plus"></i> Agregar Repaso
                        </button>
                        @elseif($progress['cumplePuntas'] && !$activeGrado->isCompletado())
                        <span class="label label-success" style="font-size:11px; padding:4px 8px;">
                            <i class="fa-solid fa-lock"></i> Puntas completas — debe rendir examen
                        </span>
                        @endif
                    </div>

                    @if($repasos->count())
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th style="width:110px;">Fecha</th>
                                    <th style="width:110px; text-align:center;">Resultado</th>
                                    <th>Observación</th>
                                    @if(!$activeGrado->isCompletado())
                                    <th style="width:60px; text-align:center;">Acc.</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($repasos as $repaso)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($repaso->fecha)->format('d/m/Y') }}</td>
                                    <td style="text-align:center;">
                                        @if($repaso->aprobado)
                                            <span class="label label-success"><i class="fa-solid fa-star"></i> Punta</span>
                                        @else
                                            <span class="label label-default">No aprobado</span>
                                        @endif
                                    </td>
                                    <td>{{ $repaso->observacion ?: '—' }}</td>
                                    @if(!$activeGrado->isCompletado())
                                    <td style="text-align:center;">
                                        <a href="#" onclick="deleteItem('{{ route('alumno.grado.repaso.destroy', $repaso->id) }}')"
                                           data-toggle="modal" data-target="#modal-delete"
                                           class="btn btn-danger btn-xs">
                                            <i class="voyager-trash"></i>
                                        </a>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted" style="font-size:13px; margin:0;">Sin repasos registrados aún.</p>
                    @endif
                </div>
            </div>

            {{-- ── Examen Final (visible cuando se cumplen puntas y días) ── --}}
            @if($progress['puedeExamen'] || $examenes->count())
            <hr style="margin:15px 0;">
            <div class="row">
                <div class="col-xs-12">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <h5 style="margin:0; font-weight:600;">
                            <i class="fa-solid fa-graduation-cap" style="color:#e74c3c;"></i> Examen Final
                            @if($progress['examenAprobado'])
                                <span class="label label-success" style="margin-left:6px;"><i class="fa-solid fa-check"></i> Aprobado</span>
                            @endif
                        </h5>
                        @if($progress['puedeExamen'] && !$activeGrado->isCompletado())
                        <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#modal-add-examen">
                            <i class="voyager-plus"></i> Registrar Examen
                        </button>
                        @endif
                    </div>

                    @if(!$progress['puedeExamen'] && !$examenes->count())
                        <p class="text-muted" style="font-size:13px;">
                            Complete las {{ $progress['puntasRequeridas'] }} puntas requeridas para habilitar el examen final
                            (faltan {{ $progress['puntasRequeridas'] - $progress['puntasObtenidas'] }}).
                        </p>
                    @elseif($examenes->count())
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th style="width:110px;">Fecha</th>
                                    <th style="width:120px; text-align:center;">Resultado</th>
                                    <th>Observación</th>
                                    @if(!$activeGrado->isCompletado())
                                    <th style="width:60px; text-align:center;">Acc.</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($examenes as $examen)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($examen->fecha)->format('d/m/Y') }}</td>
                                    <td style="text-align:center;">
                                        @if($examen->aprobado)
                                            <span class="label label-success"><i class="fa-solid fa-check"></i> Aprobado</span>
                                        @else
                                            <span class="label label-danger"><i class="fa-solid fa-xmark"></i> Aplazado</span>
                                        @endif
                                    </td>
                                    <td>{{ $examen->observacion ?: '—' }}</td>
                                    @if(!$activeGrado->isCompletado())
                                    <td style="text-align:center;">
                                        <a href="#" onclick="deleteItem('{{ route('alumno.grado.examen.destroy', $examen->id) }}')"
                                           data-toggle="modal" data-target="#modal-delete"
                                           class="btn btn-danger btn-xs">
                                            <i class="voyager-trash"></i>
                                        </a>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted" style="font-size:13px; margin:0;">Sin intentos de examen registrados aún.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>{{-- /panel-body --}}
    </div>{{-- /panel --}}

    {{-- Modal: Agregar Repaso --}}
    @if(!$activeGrado->isCompletado())
    @php
        $fechaInicioGrado     = $activeGrado->fecha;
        $ultimoRepasoFechaRaw = $repasos->first()  ? $repasos->first()->fecha  : null;
        $ultimoExamenFechaRaw = $examenes->first() ? $examenes->first()->fecha : null;

        // Fecha mínima para repaso: día siguiente al más reciente entre inicio del grado, último repaso y último examen
        $candidatosRepaso = array_filter([$fechaInicioGrado, $ultimoRepasoFechaRaw, $ultimoExamenFechaRaw]);
        $minFechaRepaso   = \Carbon\Carbon::parse(max($candidatosRepaso))->addDay()->format('Y-m-d');
        $defaultFechaRepaso = $minFechaRepaso > date('Y-m-d') ? $minFechaRepaso : date('Y-m-d');

        // Fecha mínima para examen: día siguiente al más reciente entre inicio del grado y último examen
        $candidatosExamen = array_filter([$fechaInicioGrado, $ultimoExamenFechaRaw]);
        $minFechaExamen   = \Carbon\Carbon::parse(max($candidatosExamen))->addDay()->format('Y-m-d');
        $defaultFechaExamen = $minFechaExamen > date('Y-m-d') ? $minFechaExamen : date('Y-m-d');
    @endphp

    <div class="modal fade" tabindex="-1" id="modal-add-repaso" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('alumno.grado.repaso.store') }}" method="POST" class="form-edit-add">
                    @csrf
                    <input type="hidden" name="alumno_grado_id" value="{{ $activeGrado->id }}">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-repeat"></i> Agregar Repaso</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group col-md-6">
                            <label>Fecha del repaso <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control"
                                   value="{{ $defaultFechaRepaso }}"
                                   @if($minFechaRepaso) min="{{ $minFechaRepaso }}" @endif
                                   required>
                            <small class="text-muted">
                                Debe ser posterior al
                                @if($ultimoRepasoFechaRaw || $ultimoExamenFechaRaw)
                                    @php $refRepaso = max(array_filter([$ultimoRepasoFechaRaw, $ultimoExamenFechaRaw])); @endphp
                                    @if($refRepaso > $fechaInicioGrado)
                                        último repaso/examen ({{ \Carbon\Carbon::parse($refRepaso)->format('d/m/Y') }})
                                    @else
                                        inicio del grado ({{ \Carbon\Carbon::parse($fechaInicioGrado)->format('d/m/Y') }})
                                    @endif
                                @else
                                    inicio del grado ({{ \Carbon\Carbon::parse($fechaInicioGrado)->format('d/m/Y') }})
                                @endif
                            </small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Resultado <span class="text-danger">*</span></label>
                            <select name="aprobado" class="form-control" required>
                                <option value="1">✅ Aprobado (cuenta como punta)</option>
                                <option value="0">❌ No aprobado</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Observaciones</label>
                            <textarea name="observacion" class="form-control" rows="2" placeholder="Notas sobre el repaso..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-submit">Guardar Repaso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Registrar Examen Final --}}
    @if($progress['puedeExamen'])
    <div class="modal fade" tabindex="-1" id="modal-add-examen" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('alumno.grado.examen.store') }}" method="POST" class="form-edit-add">
                    @csrf
                    <input type="hidden" name="alumno_grado_id" value="{{ $activeGrado->id }}">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title"><i class="fa-solid fa-graduation-cap"></i> Registrar Examen Final</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" style="font-size:13px;">
                            Grado: <strong>{{ $gradoLabel }}</strong> —
                            Puntas: <strong>{{ $progress['puntasObtenidas'] }}/{{ $progress['puntasRequeridas'] }}</strong> —
                            Días: <strong>{{ $progress['diasTranscurridos'] }}/{{ $progress['diasRequeridos'] }}</strong>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Fecha del examen <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control"
                                   value="{{ $defaultFechaExamen }}"
                                   min="{{ $minFechaExamen }}"
                                   required>
                            <small class="text-muted">
                                Debe ser posterior al
                                @if($ultimoExamenFechaRaw && $ultimoExamenFechaRaw > $fechaInicioGrado)
                                    examen anterior ({{ \Carbon\Carbon::parse($ultimoExamenFechaRaw)->format('d/m/Y') }})
                                @else
                                    inicio del grado ({{ \Carbon\Carbon::parse($fechaInicioGrado)->format('d/m/Y') }})
                                @endif
                            </small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Resultado <span class="text-danger">*</span></label>
                            <select name="aprobado" class="form-control" required>
                                <option value="1">✅ Aprobado</option>
                                <option value="0">❌ Aplazado</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Observaciones</label>
                            <textarea name="observacion" class="form-control" rows="2" placeholder="Notas sobre el examen..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning btn-submit">Registrar Examen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endif

@elseif($activeGrado && !$activeGrado->grado)
    <div class="alert alert-warning">El grado activo no tiene un grado asociado válido.</div>
@endif

{{-- ══════════════════════════════════════════════════════
     SECCIÓN: HISTORIAL DE GRADOS COMPLETADOS
     ══════════════════════════════════════════════════════ --}}
<h5 style="font-weight:700; margin: {{ $activeGrado ? '20px 0 10px' : '0 0 10px' }};">
    <i class="fa-solid fa-list-check" style="color:#27ae60;"></i> Historial de Grados Completados
</h5>

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover historial-grados-table" style="font-size:13px;">
        <thead>
            <tr>
                <th style="width:36px;"></th>
                <th>Grado</th>
                <th style="width:120px;">Fecha Inicio</th>
                <th>Observación</th>
                <th style="width:80px; text-align:center;">Puntas</th>
                <th style="width:90px; text-align:center;">Examen</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                @php
                    $grado        = $item->grado;
                    $gradoLabel   = trim(($grado->tipo ?? '') . ' ' . ($grado->numero ?? '') . ' ' . ($grado->nombre ?? ''));
                    $hRepasos     = $item->repasos->sortByDesc('fecha');
                    $hExamenes    = $item->examenes->sortByDesc('fecha');
                    $hPuntasObt   = $hRepasos->where('aprobado', 1)->count();
                    $hPuntasReq   = $grado ? (int) $grado->puntas : 0;
                    $hExamenFinal = $hExamenes->where('aprobado', 1)->first();
                    $hExamAprobado = $hExamenFinal !== null;
                @endphp

                {{-- Fila principal (clickeable) --}}
                <tr class="historial-row" data-target="#hdetail-{{ $item->id }}"
                    style="cursor:pointer;" title="Click para ver detalle">
                    <td style="text-align:center; vertical-align:middle;">
                        <i class="fa-solid fa-chevron-right historial-chevron" id="chev-{{ $item->id }}"
                           style="color:#aaa; font-size:11px; transition:transform .2s;"></i>
                    </td>
                    <td style="vertical-align:middle;">
                        <strong>{{ $gradoLabel ?: 'Grado no disponible' }}</strong>
                        @if ($grado)
                            <br><small class="text-muted">{{ $grado->tipo ?? '' }} · req. {{ $hPuntasReq }} puntas</small>
                        @endif
                    </td>
                    <td style="vertical-align:middle;">
                        {{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '—' }}
                    </td>
                    <td style="vertical-align:middle;">{{ $item->observacion ?: '—' }}</td>
                    <td style="text-align:center; vertical-align:middle;">
                        <span class="label label-success">{{ $hPuntasObt }}/{{ $hPuntasReq }}</span>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        @if($hExamAprobado)
                            <span class="label label-success"><i class="fa-solid fa-check"></i> Aprobado</span>
                            <br><small class="text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($hExamenFinal->fecha)->format('d/m/Y') }}</small>
                        @else
                            <span class="label label-default">—</span>
                        @endif
                    </td>
                </tr>

                {{-- Fila de detalle (colapsable) --}}
                <tr class="historial-detail-row" id="hdetail-{{ $item->id }}" style="display:none;">
                    <td colspan="6" style="padding:0; border-top:0; background:#f7fafd;">
                        <div style="padding:14px 18px;">
                            <div class="row">

                                {{-- Repasos --}}
                                <div class="col-md-7">
                                    <p style="font-weight:600; margin-bottom:6px; font-size:13px;">
                                        <i class="fa-solid fa-repeat" style="color:#8e44ad;"></i>
                                        Repasos
                                        <small class="text-muted">({{ $hRepasos->count() }} total · {{ $hPuntasObt }} aprobados)</small>
                                    </p>
                                    @if($hRepasos->count())
                                        <table class="table table-condensed" style="font-size:12px; margin-bottom:0;">
                                            <thead>
                                                <tr>
                                                    <th style="width:100px;">Fecha</th>
                                                    <th style="width:100px; text-align:center;">Resultado</th>
                                                    <th>Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($hRepasos as $r)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }}</td>
                                                    <td style="text-align:center;">
                                                        @if($r->aprobado)
                                                            <span class="label label-success"><i class="fa-solid fa-star"></i> Punta</span>
                                                        @else
                                                            <span class="label label-default">No aprobado</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $r->observacion ?: '—' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted" style="font-size:12px;">Sin repasos registrados.</p>
                                    @endif
                                </div>

                                {{-- Exámenes --}}
                                <div class="col-md-5">
                                    <p style="font-weight:600; margin-bottom:6px; font-size:13px;">
                                        <i class="fa-solid fa-graduation-cap" style="color:#e74c3c;"></i>
                                        Examen Final
                                    </p>
                                    @if($hExamenes->count())
                                        <table class="table table-condensed" style="font-size:12px; margin-bottom:0;">
                                            <thead>
                                                <tr>
                                                    <th style="width:100px;">Fecha</th>
                                                    <th style="text-align:center;">Resultado</th>
                                                    <th>Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($hExamenes as $e)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($e->fecha)->format('d/m/Y') }}</td>
                                                    <td style="text-align:center;">
                                                        @if($e->aprobado)
                                                            <span class="label label-success"><i class="fa-solid fa-check"></i> Aprobado</span>
                                                        @else
                                                            <span class="label label-danger"><i class="fa-solid fa-xmark"></i> Aplazado</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $e->observacion ?: '—' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted" style="font-size:12px;">Sin intentos de examen.</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Sin grados completados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($data->count())
    <div class="row">
        <div class="col-md-6">
            <p class="text-muted" style="font-size:12px;">Mostrando {{ $data->firstItem() }}–{{ $data->lastItem() }} de {{ $data->total() }} registros.</p>
        </div>
        <div class="col-md-6 text-right">
            {{ $data->links() }}
        </div>
    </div>
@endif

<style>
    .grado-activo-panel { border-color: #3498db !important; }
    .grado-activo-panel .panel-heading { background: linear-gradient(90deg, #ebf5fb, #fafcfe); border-bottom: 1px solid #d6eaf8; }
    .grado-progress-card { background: #fafcfe; border: 1px solid #edf2f7; border-radius: 6px; padding: 10px 12px; margin-bottom: 10px; }
    .grado-progress-label { font-size: 13px; color: #555; margin-bottom: 6px; }
    .progress { border-radius: 6px; }
    .historial-row:hover td { background: #eef6ff !important; }
    .historial-detail-row td { border-top: 0 !important; }
    .historial-chevron.open { transform: rotate(90deg); color: #3498db !important; }
</style>

<script>
(function() {
    // Usar delegación desde el padre estable (#div-grados-list)
    var container = document.getElementById('div-grados-list') || document;
    container.addEventListener('click', function(e) {
        var row = e.target.closest('.historial-row');
        if (!row) return;

        var targetId = row.getAttribute('data-target');
        var detailRow = document.querySelector(targetId);
        var chevronId = 'chev-' + targetId.replace('#hdetail-', '');
        var chevron   = document.getElementById(chevronId);
        if (!detailRow) return;

        var isOpen = detailRow.style.display !== 'none';
        detailRow.style.display = isOpen ? 'none' : 'table-row';
        if (chevron) chevron.classList.toggle('open', !isOpen);
    });
})();
</script>
