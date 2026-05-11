@php
    $total = (float) ($resumen['total'] ?? 0);
    $pagado = (float) ($resumen['pagado'] ?? 0);
    $saldo = (float) ($resumen['saldo'] ?? 0);
    $descuentoTotal = (float) ($resumen['descuento'] ?? 0);
    $planActivo = $plan && (int) $plan->status === 1;
    $ultimaInicio = $ultimaMensualidad ? \Carbon\Carbon::parse($ultimaMensualidad->periodo)->startOfDay() : null;
    $ultimaFin = $ultimaMensualidad
        ? ($ultimaMensualidad->fecha_fin
            ? \Carbon\Carbon::parse($ultimaMensualidad->fecha_fin)->startOfDay()
            : $ultimaInicio->copy()->addMonthNoOverflow()->subDay())
        : null;
    $mensualidadVigente = !$planActivo && $ultimaFin && now()->startOfDay()->lte($ultimaFin);
    $planPausable = $planActivo
        ? $plan
        : (($mensualidadVigente && $ultimaMensualidad && $ultimaMensualidad->plan) ? $ultimaMensualidad->plan : null);
    $minFechaNuevaMensualidad = $ultimaFin ? $ultimaFin->copy()->addDay()->format('Y-m-d') : null;
    $maxFechaNuevaMensualidad = date('Y-m-d');
    $fechaInicioSugerida = $minFechaNuevaMensualidad && date('Y-m-d') < $minFechaNuevaMensualidad
        ? $minFechaNuevaMensualidad
        : date('Y-m-d');
    $alumnoActivo = (int) $alumno->status === 1;
@endphp

<div class="row" style="margin-bottom:12px;">
    <div class="col-md-8">
        @if(!$alumnoActivo)
            <div class="alert alert-warning" style="font-size:12px; padding:8px 10px; margin-bottom:8px;">
                <i class="fa-solid fa-lock"></i>
                Alumno inactivo: mensualidades en modo solo visualizacion. No se pueden configurar, pausar, pagar ni eliminar registros.
            </div>
        @endif
        @if($planActivo)
            <div class="alert alert-info" style="font-size:12px; padding:8px 10px; margin-bottom:8px;">
                <i class="fa-solid fa-calendar-check"></i>
                Mensualidad activa desde <strong>{{ \Carbon\Carbon::parse($plan->fecha_inicio)->format('d/m/Y') }}</strong>:
                Bs <strong>{{ number_format((float) $plan->monto_mensual, 2, '.', ',') }}</strong>
                @if($plan->tipo_generacion === 'fecha_fin' && $plan->fecha_fin)
                    · hasta <strong>{{ \Carbon\Carbon::parse($plan->fecha_fin)->format('d/m/Y') }}</strong>
                @endif
                @if((float) $plan->descuento > 0)
                    · descuento Bs <strong>{{ number_format((float) $plan->descuento, 2, '.', ',') }}</strong>
                @endif
                @if((int) $alumno->status !== 1)
                    <span class="label label-warning" style="margin-left:6px;">Alumno inactivo: no genera nuevos meses</span>
                @endif
            </div>
        @elseif($mensualidadVigente)
            <div class="alert alert-info" style="font-size:12px; padding:8px 10px; margin-bottom:8px;">
                <i class="fa-solid fa-calendar-day"></i>
                Mensualidad vigente hasta <strong>{{ $ultimaFin->format('d/m/Y') }}</strong>.
                Puede pausarse o finalizarse con corte por fecha específica.
            </div>
        @else
            <div class="alert alert-warning" style="font-size:12px; padding:8px 10px; margin-bottom:8px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Este alumno todavía no tiene configuración de mensualidad.
            </div>
        @endif
    </div>
    <div class="col-md-4 text-right">
        @if(auth()->user()->hasPermission('edit_alumnos') && $alumnoActivo)
            @if($planActivo)
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-pausar-mensualidad">
                    <i class="fa-solid fa-pause"></i> Pausar Mensualidad
                </button>
            @else
                @if($mensualidadVigente)
                    @if($planPausable)
                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-pausar-mensualidad" title="La última mensualidad sigue vigente hasta {{ $ultimaFin->format('d/m/Y') }}.">
                            <i class="fa-solid fa-pause"></i> Pausar / finalizar
                        </button>
                    @else
                        <button class="btn btn-default btn-sm" disabled title="La última mensualidad sigue vigente hasta {{ $ultimaFin->format('d/m/Y') }}.">
                            <i class="fa-solid fa-lock"></i> Mensualidad vigente
                        </button>
                    @endif
                @else
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-mensualidad-plan">
                        <i class="voyager-settings"></i> Configurar Mensualidad
                    </button>
                @endif
            @endif
        @endif
    </div>
</div>

<div class="row" style="margin-bottom:12px;">
    <div class="col-md-4 col-sm-6">
        <div class="mensualidad-summary-card deuda">
            <div class="summary-label">Deuda total</div>
            <div class="summary-value">Bs {{ number_format($saldo, 2, '.', ',') }}</div>
            <div class="summary-help">Mensualidades pendientes.</div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="mensualidad-summary-card cobrado">
            <div class="summary-label">Cobrado</div>
            <div class="summary-value">Bs {{ number_format($pagado, 2, '.', ',') }}</div>
            <div class="summary-help">Pagos registrados del alumno.</div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="mensualidad-summary-card total">
            <div class="summary-label">Total a cobrar</div>
            <div class="summary-value">Bs {{ number_format($total, 2, '.', ',') }}</div>
            <div class="summary-help">Mensualidad - descuentos.</div>
        </div>
    </div>
</div>

<div class="alert {{ $saldo > 0 ? 'alert-warning' : 'alert-success' }}" style="font-size:12px; padding:9px 12px; margin-bottom:12px;">
    <strong>Estado económico:</strong>
    @if($saldo > 0)
        el alumno debe <strong>Bs {{ number_format($saldo, 2, '.', ',') }}</strong>.
    @else
        el alumno no tiene deuda pendiente por mensualidades.
    @endif
    <br>
    Meses: <strong>{{ $resumen['total_meses'] ?? 0 }}</strong> generados,
    <strong>{{ $resumen['pendientes'] ?? 0 }}</strong> pendientes,
    <strong>{{ $resumen['parciales'] ?? 0 }}</strong> parciales,
    <strong>{{ $resumen['pagadas'] ?? 0 }}</strong> pagados/exonerados.
    Descuentos acumulados: <strong>Bs {{ number_format($descuentoTotal, 2, '.', ',') }}</strong>.
</div>

<style>
    .mensualidad-summary-card {
        border: 1px solid #e5edf4;
        border-left: 4px solid #95a5a6;
        border-radius: 6px;
        margin-bottom: 8px;
        min-height: 92px;
        padding: 10px 12px;
    }
    .mensualidad-summary-card.deuda { border-left-color: #e74c3c; }
    .mensualidad-summary-card.cobrado { border-left-color: #27ae60; }
    .mensualidad-summary-card.total { border-left-color: #3498db; }
    .summary-label {
        color: #6b7c8f;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .summary-value {
        color: #263645;
        font-size: 20px;
        font-weight: 800;
        margin-top: 3px;
    }
    .summary-help {
        color: #7f8c8d;
        font-size: 11px;
        margin-top: 3px;
    }
    .mensualidad-detail-tab {
        border-bottom: 1px solid transparent;
        color: #337ab7;
        display: inline-block;
        font-size: 12px;
        font-weight: 700;
        margin-top: 5px;
        text-decoration: none;
    }
    .mensualidad-detail-tab:hover,
    .mensualidad-detail-tab:focus {
        border-bottom-color: #337ab7;
        color: #23527c;
        outline: 0;
        text-decoration: none;
    }
    .mensualidad-detail-tab .fa-solid {
        font-size: 11px;
        margin-right: 3px;
    }
    .mensualidad-status-stack {
        display: inline-flex;
        flex-direction: column;
        gap: 4px;
        min-width: 116px;
        text-align: left;
    }
    .mensualidad-periodo-chip {
        border-radius: 3px;
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        padding: 3px 6px;
    }
    .mensualidad-status-stack .fa-solid,
    .mensualidad-periodo-chip .fa-solid {
        font-size: 9px;
        margin-right: 3px;
    }
    .mensualidad-periodo-chip.vigente {
        background: #e8f6ff;
        color: #2471a3;
    }
    .mensualidad-periodo-chip.concluida {
        background: #eef7ee;
        color: #2e7d32;
    }
    .mensualidad-periodo-chip.programada {
        background: #f4f1ff;
        color: #6c5ce7;
    }
    .mensualidad-status-help {
        color: #7f8c8d;
        display: block;
        font-size: 10px;
        line-height: 1.25;
    }
</style>

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover" style="font-size:13px;">
        <thead>
            <tr>
                <th style="width:170px;">Período</th>
                <th style="width:95px; text-align:right;">Monto</th>
                <th style="width:95px; text-align:right;">Desc.</th>
                <th style="width:95px; text-align:right;">Total</th>
                <th style="width:95px; text-align:right;">Pagado</th>
                <th style="width:95px; text-align:right;">Saldo</th>
                <th style="width:140px; text-align:center;">Estado</th>
                <th style="width:160px; min-width:160px; text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                @php
                    $estado = $item->estadoPago();
                    $esUltimaMensualidad = $ultimaMensualidad && (int) $ultimaMensualidad->id === (int) $item->id;
                    $periodoInicio = \Carbon\Carbon::parse($item->periodo)->startOfDay();
                    $periodoFin = $item->fecha_fin
                        ? \Carbon\Carbon::parse($item->fecha_fin)->startOfDay()
                        : $periodoInicio->copy()->addMonthNoOverflow()->subDay();
                    $hoy = now()->startOfDay();
                    if ($hoy->lt($periodoInicio)) {
                        $estadoPeriodo = 'Programada';
                        $estadoPeriodoClase = 'programada';
                        $estadoPeriodoAyuda = 'Inicia el ' . $periodoInicio->format('d/m/Y');
                    } elseif ($hoy->gt($periodoFin)) {
                        $estadoPeriodo = 'Concluida';
                        $estadoPeriodoClase = 'concluida';
                        $estadoPeriodoAyuda = 'Terminó el ' . $periodoFin->format('d/m/Y');
                    } else {
                        $estadoPeriodo = 'Vigente';
                        $estadoPeriodoClase = 'vigente';
                        $estadoPeriodoAyuda = 'Termina el ' . $periodoFin->format('d/m/Y');
                    }
                    $tieneMensualidadAnteriorPendiente = collect($periodosPendientes ?? [])
                        ->contains(fn($periodo) => $periodo < $item->periodo);
                    $puedePagarMensualidad = !$tieneMensualidadAnteriorPendiente;
                    $label = match($estado) {
                        'Pagado', 'Exonerado' => 'label-success',
                        'Parcial' => 'label-warning',
                        'Anulado' => 'label-default',
                        default => 'label-danger',
                    };
                    $estadoIcono = match($estado) {
                        'Pagado', 'Exonerado' => 'fa-circle-check',
                        'Parcial' => 'fa-circle-half-stroke',
                        'Anulado' => 'fa-ban',
                        default => 'fa-clock',
                    };
                    $periodoIcono = match($estadoPeriodo) {
                        'Programada' => 'fa-calendar-plus',
                        'Concluida' => 'fa-calendar-check',
                        default => 'fa-calendar-day',
                    };
                @endphp
                <tr>
                    <td>
                        Desde <small> {{ $periodoInicio->format('d/m/Y') }}</small>
                        <br>
                        Hasta <small class="text-muted">{{ $periodoFin->format('d/m/Y') }}</small>
                        <br>
                        <a href="#detalle-mensualidad-{{ $item->id }}"
                           class="mensualidad-detail-tab"
                           data-toggle="collapse"
                           aria-expanded="false"
                           aria-controls="detalle-mensualidad-{{ $item->id }}">
                            <i class="fa-solid fa-receipt"></i> Ver pagos ({{ $item->pagos->count() }})
                        </a>
                    </td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->monto, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->descuento, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format($item->total(), 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->monto_pagado, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format($item->saldo(), 2, '.', ',') }}</td>
                    <td style="text-align:center;">
                        <span class="mensualidad-status-stack">
                            <span>
                                <span class="label {{ $label }}">
                                    <i class="fa-solid {{ $estadoIcono }}"></i>{{ $estado }}
                                </span>
                            </span>
                            <span class="mensualidad-periodo-chip {{ $estadoPeriodoClase }}">
                                <i class="fa-solid {{ $periodoIcono }}"></i>{{ $estadoPeriodo }}
                            </span>
                            <span class="mensualidad-status-help">{{ $estadoPeriodoAyuda }}</span>
                        </span>
                    </td>
                    <td style="width:160px; min-width:160px;" class="no-sort no-click bread-actions text-right">
                        @if(auth()->user()->hasPermission('edit_alumnos') && $alumnoActivo && $item->status !== 'anulado')
                            @if($item->saldo() > 0 && $puedePagarMensualidad)
                                <button type="button"
                                        class="btn btn-success btn-sm btn-pagar-mensualidad"
                                        title="Registrar pago"
                                        data-toggle="modal"
                                        data-target="#modal-pagar-mensualidad"
                                        data-url="{{ route('alumno.mensualidades.pagar', $item->id) }}"
                                        data-saldo="{{ number_format($item->saldo(), 2, '.', '') }}">
                                    <i class="fa-solid fa-money-bill"></i>
                                </button>
                            @elseif($item->saldo() > 0)
                                <button type="button"
                                        class="btn btn-default btn-sm"
                                        disabled
                                        title="Debe pagar primero mensualidades anteriores.">
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                            @endif
                            @if($esUltimaMensualidad && $item->pagos->isEmpty())
                                <a href="#" onclick="deleteItem('{{ route('alumno.mensualidades.destroy', $item->id) }}')"
                                   data-toggle="modal" data-target="#modal-delete"
                                   class="btn btn-danger btn-sm"
                                   title="Eliminar mensualidad más reciente">
                                    <i class="voyager-trash"></i>
                                </a>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="padding:0; border-top:0;">
                        <div id="detalle-mensualidad-{{ $item->id }}" class="collapse">
                            <div style="background:#f8fafc; border-top:1px solid #e5edf4; padding:10px 12px;">
                                <strong style="font-size:12px;">Pagos / facturas</strong>
                                @if($item->pagos->count())
                                    <div class="list-group" style="margin:8px 0 0;">
                                        @foreach($item->pagos as $pago)
                                            <a href="{{ route('alumno.mensualidades.pago.comprobante', $pago->id) }}"
                                               target="_blank"
                                               class="list-group-item"
                                               style="padding:7px 9px; font-size:12px;"
                                               title="Imprimir comprobante">
                                                <i class="fa-solid fa-print"></i>
                                                {{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}
                                                · Bs {{ number_format((float) $pago->monto, 2, '.', ',') }}
                                                <span class="pull-right">#{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted" style="font-size:12px; margin-top:6px;">Sin pagos registrados.</div>
                                @endif
                                @if($item->observacion)
                                    <div style="font-size:12px; margin-top:8px;">
                                        <strong>Observación:</strong> {{ $item->observacion }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Sin mensualidades generadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $data->links() }}

@if(auth()->user()->hasPermission('edit_alumnos') && $alumnoActivo)
<form id="form-mensualidad-plan" action="{{ route('alumno.mensualidades.plan.store') }}" method="POST" class="form-edit-add">
    @csrf
    <div class="modal fade" tabindex="-1" id="modal-mensualidad-plan" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-calendar-days"></i> Configurar Mensualidad</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Tipo de generación <span class="text-danger">*</span></label>
                            <select name="tipo_generacion" id="tipo-generacion-mensualidad" class="form-control" required>
                                <option value="automatica" {{ old('tipo_generacion', 'automatica') === 'automatica' ? 'selected' : '' }}>Automática mensual</option>
                                <option value="fecha_fin" {{ old('tipo_generacion') === 'fecha_fin' ? 'selected' : '' }}>Con fecha fin</option>
                            </select>
                            <small class="text-muted">
                                Automática genera mensualidades completas desde la fecha de inicio. Con fecha fin genera un rango cerrado y prorratea el último tramo si corresponde.
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Inicio de cobro <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="fecha_inicio"
                                   id="fecha-inicio-mensualidad"
                                   class="form-control"
                                   @if($minFechaNuevaMensualidad) min="{{ $minFechaNuevaMensualidad }}" @endif
                                   max="{{ $maxFechaNuevaMensualidad }}"
                                   value="{{ old('fecha_inicio', $planActivo ? \Carbon\Carbon::parse($plan->fecha_inicio)->format('Y-m-d') : $fechaInicioSugerida) }}"
                                   required>
                            @if($minFechaNuevaMensualidad)
                                <small class="text-muted">
                                    El último mes generado termina el {{ $ultimaFin->format('d/m/Y') }}. La nueva mensualidad debe iniciar desde el {{ \Carbon\Carbon::parse($minFechaNuevaMensualidad)->format('d/m/Y') }}.
                                </small>
                            @endif
                        </div>
                        <div class="form-group col-md-6" id="grupo-fecha-fin-plan-mensualidad">
                            <label>Fecha fin</label>
                            <input type="date"
                                   name="fecha_fin"
                                   id="fecha-fin-plan-mensualidad"
                                   class="form-control"
                                   min="{{ old('fecha_inicio', $fechaInicioSugerida) }}"
                                   value="{{ old('fecha_fin') }}">
                            <small class="text-muted">
                                Obligatoria solo cuando se usa generación con fecha fin.
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Monto mensual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">Bs</span>
                                <input type="number"
                                       name="monto_mensual"
                                       class="form-control"
                                       min="0"
                                       max="99999999.99"
                                       step="0.01"
                                       value="{{ old('monto_mensual', $planActivo ? $plan->monto_mensual : '') }}"
                                       required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Descuento</label>
                            <div class="input-group">
                                <span class="input-group-addon">Bs</span>
                                <input type="number" name="descuento" class="form-control" min="0" step="0.01"
                                       value="{{ old('descuento', $planActivo ? $plan->descuento : 0) }}">
                            </div>
                        </div>
                    </div>
                    <div id="resumen-generacion-mensualidad" class="well well-sm" style="display:none; font-size:12px;">
                        <div><strong>Rango:</strong> <span data-field="rango"></span></div>
                        <div><strong>Tramos:</strong> <span data-field="tramos"></span></div>
                        <div><strong>Días cobrados:</strong> <span data-field="dias"></span></div>
                        <div><strong>Monto calculado:</strong> Bs <span data-field="monto"></span></div>
                        <div><strong>Descuento calculado:</strong> Bs <span data-field="descuento"></span></div>
                        <div style="background:#eef8ff; border:1px solid #9bd2f5; border-radius:4px; color:#14506f; font-size:15px; font-weight:800; margin:8px 0; padding:8px 10px;">
                            Total a generar: Bs <span data-field="total"></span>
                        </div>
                        <div data-field="detalle" style="margin-top:6px;"></div>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea name="observacion" class="form-control" rows="2">{{ old('observacion', $planActivo ? $plan->observacion : '') }}</textarea>
                    </div>
                    <p class="text-muted" style="font-size:12px; margin:0;">
                        Al guardar, el sistema genera automáticamente las mensualidades desde esa fecha, manteniendo el mismo día cada mes.
                        Si hubo un corte anterior, los meses ya generados conservan su monto y la nueva configuración continúa desde la nueva fecha.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-submit">Guardar Configuración</button>
                </div>
            </div>
        </div>
    </div>
</form>

@if($planPausable)
<form id="form-pausar-mensualidad" action="{{ route('alumno.mensualidades.plan.pausar', $planPausable->id) }}" method="POST" class="form-edit-add">
    @csrf
    @method('PUT')
    <div class="modal fade" tabindex="-1" id="modal-pausar-mensualidad" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-pause"></i> Pausar / finalizar Mensualidad</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" style="font-size:12px;">
                        Al pausar o finalizar, no se generarán nuevas mensualidades. Los meses ya generados conservarán sus saldos y pagos.
                        @if($ultimaMensualidad)
                            <br>
                            Último período generado:
                            <strong>{{ $ultimaInicio->format('d/m/Y') }}</strong>
                            al <strong>{{ $ultimaFin->format('d/m/Y') }}</strong>.
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Tipo de corte <span class="text-danger">*</span></label>
                        <select name="tipo_corte" id="tipo-corte-mensualidad" class="form-control" required>
                            <option value="mes_completo">Mes completo</option>
                            @if($ultimaMensualidad)
                            <option value="fecha">Cortar en una fecha específica</option>
                            @endif
                        </select>
                        <small class="text-muted">
                            Si elige fecha específica, se recalcula proporcionalmente el último mes generado.
                        </small>
                    </div>
                    @if($ultimaMensualidad)
                    <div class="form-group" id="grupo-fecha-corte-mensualidad" style="display:none;">
                        <label>Fecha de corte <span class="text-danger">*</span></label>
                        <input type="date"
                               name="fecha_corte"
                               class="form-control"
                               id="fecha-corte-mensualidad"
                               min="{{ $ultimaInicio->format('Y-m-d') }}"
                               max="{{ $ultimaFin->format('Y-m-d') }}"
                               data-inicio="{{ $ultimaInicio->format('Y-m-d') }}"
                               data-fin="{{ $ultimaFin->format('Y-m-d') }}"
                               data-monto="{{ number_format((float) $ultimaMensualidad->monto, 2, '.', '') }}"
                               data-descuento="{{ number_format((float) $ultimaMensualidad->descuento, 2, '.', '') }}"
                               data-pagado="{{ number_format((float) $ultimaMensualidad->monto_pagado, 2, '.', '') }}"
                               value="{{ date('Y-m-d') >= $ultimaInicio->format('Y-m-d') && date('Y-m-d') <= $ultimaFin->format('Y-m-d') ? date('Y-m-d') : $ultimaFin->format('Y-m-d') }}">
                        <small class="text-muted">
                            Debe estar dentro del último período generado.
                        </small>
                        <div id="resumen-corte-mensualidad" class="well well-sm" style="display:none; margin-top:10px; margin-bottom:0; font-size:12px;">
                            <div><strong>Días a cobrar:</strong> <span data-field="dias"></span></div>
                            <div><strong>Monto proporcional:</strong> Bs <span data-field="monto"></span></div>
                            <div><strong>Descuento proporcional:</strong> Bs <span data-field="descuento"></span></div>
                            <div style="background:#fff8e1; border:1px solid #f1c40f; border-radius:4px; color:#7a4f01; font-size:15px; font-weight:800; margin:8px 0; padding:8px 10px;">
                                Total a cobrar: Bs <span data-field="total"></span>
                            </div>
                            <div><strong>Pagado:</strong> Bs <span data-field="pagado"></span></div>
                            <div><strong>Saldo estimado:</strong> Bs <span data-field="saldo"></span></div>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Motivo / observación</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Ej. pausa temporal, cambio de plan, viaje..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-submit">Pausar / finalizar</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endif

<script>
    (function() {
        var $form = $('#form-mensualidad-plan');
        var $tipo = $('#tipo-generacion-mensualidad');
        var $fechaInicio = $('#fecha-inicio-mensualidad');
        var $fechaFin = $('#fecha-fin-plan-mensualidad');
        var $grupoFechaFin = $('#grupo-fecha-fin-plan-mensualidad');
        var $resumen = $('#resumen-generacion-mensualidad');
        var $monto = $form.find('input[name="monto_mensual"]');
        var $descuento = $form.find('input[name="descuento"]');

        function parseDate(value) {
            if (!value) {
                return null;
            }

            var parts = value.split('-');
            return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }

        function formatDate(date) {
            var day = String(date.getDate()).padStart(2, '0');
            var month = String(date.getMonth() + 1).padStart(2, '0');
            return day + '/' + month + '/' + date.getFullYear();
        }

        function money(value) {
            return Number(value || 0).toFixed(2);
        }

        function daysInMonth(year, monthIndex) {
            return new Date(year, monthIndex + 1, 0).getDate();
        }

        function addMonthNoOverflow(date) {
            var year = date.getFullYear();
            var month = date.getMonth() + 1;

            if (month > 11) {
                month = 0;
                year += 1;
            }

            var day = Math.min(date.getDate(), daysInMonth(year, month));
            return new Date(year, month, day);
        }

        function diffDays(start, end) {
            var msDia = 24 * 60 * 60 * 1000;
            return Math.floor((end - start) / msDia) + 1;
        }

        function finAutomatico(inicio) {
            var hoy = parseDate($fechaInicio.attr('max'));
            var cursor = new Date(inicio.getTime());
            var fin = new Date(addMonthNoOverflow(cursor).getTime());
            fin.setDate(fin.getDate() - 1);

            while (hoy && addMonthNoOverflow(cursor) <= hoy) {
                cursor = addMonthNoOverflow(cursor);
                fin = new Date(addMonthNoOverflow(cursor).getTime());
                fin.setDate(fin.getDate() - 1);
            }

            return fin;
        }

        function calcularGeneracion() {
            if (!$form.length || !$resumen.length) {
                return;
            }

            var inicio = parseDate($fechaInicio.val());
            var fin = $tipo.val() === 'fecha_fin' ? parseDate($fechaFin.val()) : (inicio ? finAutomatico(inicio) : null);
            var montoMensual = Number($monto.val() || 0);
            var descuentoMensual = Number($descuento.val() || 0);

            if (!inicio || !fin || fin < inicio || montoMensual < 0 || descuentoMensual < 0) {
                $resumen.hide();
                return;
            }

            var cursor = new Date(inicio.getTime());
            var tramos = [];
            var totalDias = 0;
            var totalMonto = 0;
            var totalDescuento = 0;

            while (cursor <= fin && tramos.length < 60) {
                var siguienteInicio = addMonthNoOverflow(cursor);
                var finMes = new Date(siguienteInicio.getTime());
                finMes.setDate(finMes.getDate() - 1);

                var finTramo = fin < finMes ? fin : finMes;
                var diasMes = diffDays(cursor, finMes);
                var diasCobrados = diffDays(cursor, finTramo);
                var factor = diasMes > 0 ? diasCobrados / diasMes : 1;
                var montoTramo = montoMensual * factor;
                var descuentoTramo = descuentoMensual * factor;

                tramos.push({
                    inicio: new Date(cursor.getTime()),
                    fin: new Date(finTramo.getTime()),
                    dias: diasCobrados,
                    diasMes: diasMes,
                    monto: montoTramo,
                    descuento: descuentoTramo
                });

                totalDias += diasCobrados;
                totalMonto += montoTramo;
                totalDescuento += descuentoTramo;
                cursor = siguienteInicio;
            }

            if (!tramos.length) {
                $resumen.hide();
                return;
            }

            var detalle = tramos.map(function(tramo) {
                var totalTramo = Math.max(0, tramo.monto - tramo.descuento);
                return '<div>' + formatDate(tramo.inicio) + ' al ' + formatDate(tramo.fin) +
                    ': ' + tramo.dias + '/' + tramo.diasMes + ' dias, Bs ' + money(totalTramo) + '</div>';
            }).join('');

            $resumen.find('[data-field="rango"]').text(formatDate(inicio) + ' al ' + formatDate(fin));
            $resumen.find('[data-field="tramos"]').text(tramos.length);
            $resumen.find('[data-field="dias"]').text(totalDias);
            $resumen.find('[data-field="monto"]').text(money(totalMonto));
            $resumen.find('[data-field="descuento"]').text(money(totalDescuento));
            $resumen.find('[data-field="total"]').text(money(Math.max(0, totalMonto - totalDescuento)));
            $resumen.find('[data-field="detalle"]').html(detalle);
            $resumen.show();
        }

        function toggleTipoGeneracion() {
            var usaFechaFin = $tipo.val() === 'fecha_fin';
            $grupoFechaFin.toggle(usaFechaFin);
            $fechaFin.prop('required', usaFechaFin);
            $fechaFin.prop('disabled', !usaFechaFin);

            if (!usaFechaFin) {
                $fechaFin.val('');
            }

            if ($fechaInicio.val()) {
                $fechaFin.attr('min', $fechaInicio.val());
            }

            calcularGeneracion();
        }

        $tipo.off('change.generacionMensualidad').on('change.generacionMensualidad', toggleTipoGeneracion);
        $fechaInicio.off('change.generacionMensualidad input.generacionMensualidad').on('change.generacionMensualidad input.generacionMensualidad', function() {
            if ($fechaInicio.val()) {
                $fechaFin.attr('min', $fechaInicio.val());
            }
            if ($fechaFin.val() && $fechaInicio.val() && parseDate($fechaFin.val()) < parseDate($fechaInicio.val())) {
                $fechaFin.val('');
            }
            calcularGeneracion();
        });
        $fechaFin.add($monto).add($descuento)
            .off('change.generacionMensualidad input.generacionMensualidad')
            .on('change.generacionMensualidad input.generacionMensualidad', calcularGeneracion);

        toggleTipoGeneracion();
    })();

    (function() {
        var $tipo = $('#tipo-corte-mensualidad');
        var $grupoFecha = $('#grupo-fecha-corte-mensualidad');
        var $fechaCorte = $('#fecha-corte-mensualidad');
        var $resumen = $('#resumen-corte-mensualidad');

        function parseDate(value) {
            if (!value) {
                return null;
            }

            var parts = value.split('-');
            return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }

        function money(value) {
            return Number(value || 0).toFixed(2);
        }

        function calcularResumenCorte() {
            if (!$fechaCorte.length || !$resumen.length || $tipo.val() !== 'fecha') {
                $resumen.hide();
                return;
            }

            var inicio = parseDate($fechaCorte.data('inicio'));
            var fin = parseDate($fechaCorte.data('fin'));
            var corte = parseDate($fechaCorte.val());

            if (!inicio || !fin || !corte || corte < inicio || corte > fin) {
                $resumen.hide();
                return;
            }

            var msDia = 24 * 60 * 60 * 1000;
            var diasPeriodo = Math.floor((fin - inicio) / msDia) + 1;
            var diasCobrados = Math.floor((corte - inicio) / msDia) + 1;
            var factor = diasPeriodo > 0 ? diasCobrados / diasPeriodo : 1;
            var monto = Number($fechaCorte.data('monto') || 0) * factor;
            var descuento = Number($fechaCorte.data('descuento') || 0) * factor;
            var pagado = Number($fechaCorte.data('pagado') || 0);
            var total = Math.max(0, monto - descuento);
            var saldo = Math.max(0, total - pagado);

            $resumen.find('[data-field="dias"]').text(diasCobrados + ' de ' + diasPeriodo);
            $resumen.find('[data-field="monto"]').text(money(monto));
            $resumen.find('[data-field="descuento"]').text(money(descuento));
            $resumen.find('[data-field="total"]').text(money(total));
            $resumen.find('[data-field="pagado"]').text(money(pagado));
            $resumen.find('[data-field="saldo"]').text(money(saldo));
            $resumen.show();
        }

        function toggleFechaCorte() {
            var usaFecha = $tipo.val() === 'fecha';
            $grupoFecha.toggle(usaFecha);
            $grupoFecha.find('input[name="fecha_corte"]').prop('required', usaFecha);
            calcularResumenCorte();
        }

        $tipo.off('change.mensualidad').on('change.mensualidad', toggleFechaCorte);
        $fechaCorte.off('change.mensualidad input.mensualidad').on('change.mensualidad input.mensualidad', calcularResumenCorte);
        toggleFechaCorte();
    })();
</script>

<form id="form-pagar-mensualidad" action="#" method="POST" class="form-edit-add">
    @csrf
    @method('PUT')
    <div class="modal fade" tabindex="-1" id="modal-pagar-mensualidad" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-money-bill"></i> Registrar Pago de Mensualidad</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Fecha de pago <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Monto pagado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">Bs</span>
                                <input type="number" name="monto" id="mensualidad-pago-monto" class="form-control" min="0.01" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea name="observacion" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-submit">Guardar Pago</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endif
