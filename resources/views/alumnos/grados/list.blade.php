{{-- Comunicar al JS de read.blade.php si se puede agregar nuevo grado --}}
<input type="hidden" id="puede-agregar-grado" value="{{ $puedeAgregarGrado ? '1' : '0' }}">
<input type="hidden" id="active-grado-id" value="{{ $activeGrado ? $activeGrado->id : '' }}">

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
                <h4 style="margin:0; font-size:15px; font-weight:700;">
                    <i class="fa-solid fa-award" style="color:#3498db;"></i>
                    Grado en Progreso: <span style="color:#2c3e50;">{{ $gradoLabel ?: 'Sin nombre' }}</span>
                </h4>
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
                            Días de práctica
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
                            <small class="text-success"><i class="fa-solid fa-check"></i> Días cumplidos</small>
                        @else
                            <small class="text-muted">Faltan {{ $progress['diasRequeridos'] - $progress['diasTranscurridos'] }} día(s) (desde {{ \Carbon\Carbon::parse($activeGrado->fecha)->format('d/m/Y') }})</small>
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
                        @if(!$activeGrado->isCompletado())
                        <button class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-add-repaso">
                            <i class="voyager-plus"></i> Agregar Repaso
                        </button>
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
                                           data-toggle="modal" data-target="#modal_delete_confirm"
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
                        <p class="text-muted" style="font-size:13px;">Complete las puntas y los días requeridos para habilitar el examen final.</p>
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
                                           data-toggle="modal" data-target="#modal_delete_confirm"
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
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
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
    <table class="table table-bordered table-hover" style="font-size:13px;">
        <thead>
            <tr>
                <th style="width:60px; text-align:center;">ID</th>
                <th>Grado</th>
                <th style="width:120px;">Fecha Inicio</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                @php
                    $grado = $item->grado;
                    $gradoLabel = trim(($grado->tipo ?? '') . ' ' . ($grado->numero ?? '') . ' ' . ($grado->nombre ?? ''));
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $item->id }}</td>
                    <td>
                        <strong>{{ $gradoLabel ?: 'Grado no disponible' }}</strong>
                        @if ($grado)
                            <br>
                            <small class="text-muted">
                                Puntas: <strong>{{ $grado->puntas ?? 0 }}</strong> ·
                                Días: <strong>{{ $grado->dias ?? 0 }}</strong>
                            </small>
                        @endif
                    </td>
                    <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $item->observacion ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Sin grados completados.</td>
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
</style>
