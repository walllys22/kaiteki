@php
    $total = (float) ($resumen['total'] ?? 0);
    $pagado = (float) ($resumen['pagado'] ?? 0);
    $saldo = (float) ($resumen['saldo'] ?? 0);
    $planActivo = $plan && (int) $plan->status === 1;
@endphp

<div class="row" style="margin-bottom:12px;">
    <div class="col-md-8">
        @if($planActivo)
            <div class="alert alert-info" style="font-size:12px; padding:8px 10px; margin-bottom:8px;">
                <i class="fa-solid fa-calendar-check"></i>
                Mensualidad activa desde <strong>{{ \Carbon\Carbon::parse($plan->fecha_inicio)->format('d/m/Y') }}</strong>:
                Bs <strong>{{ number_format((float) $plan->monto_mensual, 2, '.', ',') }}</strong>
                @if((float) $plan->descuento > 0)
                    · descuento Bs <strong>{{ number_format((float) $plan->descuento, 2, '.', ',') }}</strong>
                @endif
                @if((float) $plan->beca > 0)
                    · beca Bs <strong>{{ number_format((float) $plan->beca, 2, '.', ',') }}</strong>
                @endif
                @if((int) $alumno->status !== 1)
                    <span class="label label-warning" style="margin-left:6px;">Alumno inactivo: no genera nuevos meses</span>
                @endif
            </div>
        @else
            <div class="alert alert-warning" style="font-size:12px; padding:8px 10px; margin-bottom:8px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Este alumno todavía no tiene configuración de mensualidad.
            </div>
        @endif
    </div>
    <div class="col-md-4 text-right">
        @if(auth()->user()->hasPermission('edit_alumnos'))
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-mensualidad-plan">
                <i class="voyager-settings"></i> Configurar Mensualidad
            </button>
        @endif
    </div>
</div>

<div class="alert alert-success" style="font-size:12px; padding:8px 10px; margin-bottom:12px;">
    Total a cobrar: <strong>Bs {{ number_format($total, 2, '.', ',') }}</strong>
    &nbsp;·&nbsp; Cobrado: <strong>Bs {{ number_format($pagado, 2, '.', ',') }}</strong>
    &nbsp;·&nbsp; Saldo/Mora: <strong>Bs {{ number_format($saldo, 2, '.', ',') }}</strong>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover" style="font-size:13px;">
        <thead>
            <tr>
                <th style="width:105px;">Fecha</th>
                <th style="width:95px; text-align:right;">Monto</th>
                <th style="width:95px; text-align:right;">Desc.</th>
                <th style="width:95px; text-align:right;">Beca</th>
                <th style="width:95px; text-align:right;">Mora</th>
                <th style="width:95px; text-align:right;">Total</th>
                <th style="width:95px; text-align:right;">Pagado</th>
                <th style="width:95px; text-align:right;">Saldo</th>
                <th style="width:95px; text-align:center;">Estado</th>
                <th>Pagos / Observación</th>
                <th style="width:105px; text-align:center;">Acc.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                @php
                    $estado = $item->estadoPago();
                    $tieneMensualidadAnteriorPendiente = collect($periodosPendientes ?? [])
                        ->contains(fn($periodo) => $periodo < $item->periodo);
                    $puedePagarMensualidad = !$tieneMensualidadAnteriorPendiente;
                    $label = match($estado) {
                        'Pagado', 'Exonerado' => 'label-success',
                        'Parcial' => 'label-warning',
                        'Anulado' => 'label-default',
                        default => 'label-danger',
                    };
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->periodo)->format('d/m/Y') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->monto, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->descuento, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->beca, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->mora, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format($item->total(), 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format((float) $item->monto_pagado, 2, '.', ',') }}</td>
                    <td style="text-align:right;">Bs {{ number_format($item->saldo(), 2, '.', ',') }}</td>
                    <td style="text-align:center;">
                        <span class="label {{ $label }}">{{ $estado }}</span>
                    </td>
                    <td>
                        @if($item->pagos->count())
                            <small>
                                @foreach($item->pagos->take(3) as $pago)
                                    <a href="{{ route('alumno.mensualidades.pago.comprobante', $pago->id) }}"
                                       target="_blank"
                                       class="label label-info"
                                       title="Imprimir comprobante">
                                        {{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }} · Bs {{ number_format((float) $pago->monto, 2, '.', ',') }}
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                @endforeach
                            </small>
                        @else
                            <small class="text-muted">Sin pagos.</small>
                        @endif
                        @if($item->observacion)
                            <br><small class="text-muted">{{ $item->observacion }}</small>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if(auth()->user()->hasPermission('edit_alumnos') && $item->status !== 'anulado')
                            @if($item->saldo() > 0 && $puedePagarMensualidad)
                                <button type="button"
                                        class="btn btn-success btn-xs btn-pagar-mensualidad"
                                        title="Registrar pago"
                                        data-toggle="modal"
                                        data-target="#modal-pagar-mensualidad"
                                        data-url="{{ route('alumno.mensualidades.pagar', $item->id) }}"
                                        data-saldo="{{ number_format($item->saldo(), 2, '.', '') }}">
                                    <i class="fa-solid fa-money-bill"></i>
                                </button>
                            @elseif($item->saldo() > 0)
                                <button type="button"
                                        class="btn btn-default btn-xs"
                                        disabled
                                        title="Debe pagar primero mensualidades anteriores.">
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                            @endif
                            <button type="button"
                                    class="btn btn-warning btn-xs btn-mora-mensualidad"
                                    title="Agregar mora"
                                    data-toggle="modal"
                                    data-target="#modal-mora-mensualidad"
                                    data-url="{{ route('alumno.mensualidades.mora', $item->id) }}">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </button>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">Sin mensualidades generadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $data->links() }}

@if(auth()->user()->hasPermission('edit_alumnos'))
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
                        <div class="form-group col-md-6">
                            <label>Inicio de cobro <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="fecha_inicio"
                                   class="form-control"
                                   value="{{ old('fecha_inicio', $planActivo ? \Carbon\Carbon::parse($plan->fecha_inicio)->format('Y-m-d') : date('Y-m-d')) }}"
                                   required>
                        </div>
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
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Descuento</label>
                            <div class="input-group">
                                <span class="input-group-addon">Bs</span>
                                <input type="number" name="descuento" class="form-control" min="0" step="0.01"
                                       value="{{ old('descuento', $planActivo ? $plan->descuento : 0) }}">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Beca</label>
                            <div class="input-group">
                                <span class="input-group-addon">Bs</span>
                                <input type="number" name="beca" class="form-control" min="0" step="0.01"
                                       value="{{ old('beca', $planActivo ? $plan->beca : 0) }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea name="observacion" class="form-control" rows="2">{{ old('observacion', $planActivo ? $plan->observacion : '') }}</textarea>
                    </div>
                    <p class="text-muted" style="font-size:12px; margin:0;">
                        Al guardar, el sistema genera automáticamente las mensualidades desde esa fecha, manteniendo el mismo día cada mes.
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

<form id="form-mora-mensualidad" action="#" method="POST" class="form-edit-add">
    @csrf
    @method('PUT')
    <div class="modal fade" tabindex="-1" id="modal-mora-mensualidad" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-triangle-exclamation"></i> Agregar Mora</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Monto de mora <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Bs</span>
                            <input type="number" name="mora" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Motivo de la mora..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-submit">Agregar Mora</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endif
