@extends('voyager::master')

@section('page_title', 'Mensualidades')

@section('page_header')
@stop

@section('css')
<style>
    @media (max-width: 768px) {
        .dojo-summary-panel {
            align-items: flex-start !important;
        }
        .dojo-summary-panel > div {
            min-width: 0;
        }
        .dojo-summary-panel h4,
        .dojo-summary-panel span {
            white-space: normal;
            word-break: break-word;
        }
    }
</style>
@stop

@section('content')
<div class="page-content container-fluid">

    {{-- Selector de dojo (solo admin global) --}}
    @if(!auth()->user()->dojo_id)
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 14px 18px;">
                        <form method="GET" action="{{ route('mensualidades.index') }}" class="form-inline" style="gap:10px; display:flex; align-items:center;">
                            <label style="margin-right:8px; font-weight:600;">Sucursal:</label>
                            <select name="dojo_id" class="form-control" style="min-width:220px;">
                                <option value="">— Seleccionar sucursal —</option>
                                @foreach($dojos as $d)
                                    <option value="{{ $d->id }}" @selected($dojoId == $d->id)>{{ $d->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm" style="margin-left:8px;">
                                <i class="fa fa-search"></i> Ver
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($dojo)
        {{-- Header dojo --}}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="border-left: 4px solid #22a7f0; margin-bottom: 16px;">
                    <div class="panel-body dojo-summary-panel" style="padding: 14px 20px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                        @if($dojo->logo)
                            <img src="{{ filter_var($dojo->logo, FILTER_VALIDATE_URL) ? $dojo->logo : Voyager::image($dojo->logo) }}" style="width:52px; height:52px; object-fit:contain; border-radius:6px; border:1px solid #e5e9ef;">
                        @else
                            <div style="width:52px; height:52px; background:#f0f4f8; border-radius:6px; display:flex; align-items:center; justify-content:center; border:1px solid #e1e8ed;">
                                <i class="voyager-home" style="font-size:22px; color:#b0bec5;"></i>
                            </div>
                        @endif
                        <div>
                            <h4 style="margin:0 0 2px; font-weight:700;">{{ $dojo->nombre }}</h4>
                            <span style="color:#888; font-size:12px;">
                                <i class="fa fa-map-marker" style="color:#22a7f0;"></i> {{ optional($dojo->ciudad)->nombre ?? '—' }}
                                @if($dojo->phone)
                                    &nbsp;·&nbsp; <i class="fa fa-phone" style="color:#22a7f0;"></i> {{ $dojo->phone }}
                                @endif
                            </span>
                        </div>
                        @if(!auth()->user()->dojo_id)
                            <div style="margin-left:auto;">
                                <a href="{{ route('voyager.dojos.show', $dojo->id) }}" class="btn btn-default btn-sm">
                                    <i class="voyager-eye"></i> Ver dojo
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen --}}
        @php
            $totalPagado  = $mensualidades->sum('monto_pagado');
            $totalSaldo   = $mensualidades->sum(fn($m) => $m->saldo());
            $vigente      = $mensualidades->first(fn($m) => $m->isVigente());
            $primeraFecha = $mensualidades->sortBy('fecha_inicio')->first()?->fecha_inicio;
        @endphp
        <div class="row" style="margin-bottom: 4px;">
            <div class="col-md-3">
                <div class="panel panel-bordered text-center" style="padding: 14px 10px; margin-bottom:16px; border-top: 3px solid #22a7f0;">
                    <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:600;">Cliente desde</div>
                    <div style="font-size:20px; font-weight:700; margin-top:4px; color:#22a7f0;">
                        {{ $primeraFecha ? $primeraFecha->format('d/m/Y') : '—' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-bordered text-center" style="padding: 14px 10px; margin-bottom:16px; border-top: 3px solid #27ae60;">
                    <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:600;">Total pagado</div>
                    <div style="font-size:20px; font-weight:700; color:#27ae60; margin-top:4px;">Bs {{ number_format($totalPagado, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-bordered text-center" style="padding: 14px 10px; margin-bottom:16px; border-top: 3px solid #e74c3c;">
                    <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:600;">Saldo pendiente</div>
                    <div style="font-size:20px; font-weight:700; color:#e74c3c; margin-top:4px;">Bs {{ number_format($totalSaldo, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-bordered text-center" style="padding: 14px 10px; margin-bottom:16px; border-top: 3px solid {{ $vigente ? '#22a7f0' : '#e74c3c' }};">
                    <div style="font-size:11px; color:#888; text-transform:uppercase; font-weight:600;">Estado actual</div>
                    <div style="font-size:16px; font-weight:700; margin-top:4px;">
                        @if($vigente)
                            <span class="label label-success" style="font-size:13px; padding:5px 12px;">Con servicio</span>
                        @else
                            <span class="label label-danger" style="font-size:13px; padding:5px 12px;">Con corte</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Historial mensualidades --}}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title" style="margin:0;">
                            <i class="voyager-dollar"></i> Historial de Mensualidades
                        </h3>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        @if($mensualidades->isEmpty())
                            <div class="text-center" style="padding:40px 0; color:#aaa;">
                                <i class="voyager-dollar" style="font-size:48px;"></i>
                                <p style="margin-top:12px;">No hay mensualidades registradas.</p>
                            </div>
                        @else
                            <table class="table table-hover" style="margin-bottom:0;">
                                <thead style="background:#f8f9fa;">
                                    <tr>
                                        <th style="border-top:none;">Fecha inicio</th>
                                        <th style="border-top:none;">Fecha fin</th>
                                        <th style="text-align:right; border-top:none;">Monto</th>
                                        <th style="text-align:right; border-top:none;">Pagado</th>
                                        <th style="text-align:right; border-top:none;">Saldo</th>
                                        <th style="text-align:center; border-top:none;">Estado</th>
                                        <th style="text-align:center; border-top:none;">Pagos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mensualidades as $m)
                                        @php
                                            $estado      = $m->estadoPago();
                                            $isVigente   = $m->isVigente();
                                            $saldo       = $m->saldo();
                                            $tienePagos  = $m->pagos->isNotEmpty() || (float) $m->monto_pagado > 0;
                                            $estadoClass = match($estado) {
                                                'Pagado'  => 'success',
                                                'Vencido' => 'danger',
                                                default   => 'warning',
                                            };
                                        @endphp

                                        {{-- Fila principal --}}
                                        <tr class="mensualidad-row {{ $tienePagos ? 'clickable-row' : '' }}"
                                            style="{{ $tienePagos ? 'cursor:pointer;' : '' }}"
                                            @if($tienePagos) data-target="pagos-{{ $m->id }}" @endif>
                                            <td style="vertical-align:middle;">
                                                @if($tienePagos)
                                                    <i class="fa fa-chevron-right toggle-icon" style="font-size:10px; color:#aaa; margin-right:6px; transition:transform .2s;"></i>
                                                @else
                                                    <i class="fa fa-minus" style="font-size:10px; color:#ddd; margin-right:6px;"></i>
                                                @endif
                                                {{ $m->fecha_inicio->format('d/m/Y') }}
                                                @if($isVigente)
                                                    <span class="label label-success" style="margin-left:4px; font-size:10px;">Vigente</span>
                                                @endif
                                            </td>
                                            <td style="vertical-align:middle;">{{ $m->fecha_fin->format('d/m/Y') }}</td>
                                            <td style="text-align:right; vertical-align:middle;">{{ number_format($m->monto, 2) }}</td>
                                            <td style="text-align:right; vertical-align:middle; color:#27ae60; font-weight:600;">{{ number_format($m->monto_pagado, 2) }}</td>
                                            <td style="text-align:right; vertical-align:middle;">
                                                @if($saldo > 0)
                                                    <strong style="color:#e74c3c;">{{ number_format($saldo, 2) }}</strong>
                                                @else
                                                    <span class="text-muted">0.00</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <span class="label label-{{ $estadoClass }}">{{ $estado }}</span>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle; color:#aaa; font-size:12px;">
                                                @if($tienePagos)
                                                    {{ $m->pagos->count() ?: '1' }} pago(s)
                                                @else
                                                    <span class="text-muted">Sin pagos</span>
                                                @endif
                                            </td>
                                        </tr>

                                        @if($m->observacion)
                                            <tr style="background:#fffdf0;">
                                                <td colspan="7" style="border-top:none; color:#888; font-size:11px; padding: 4px 12px 6px 32px;">
                                                    <i class="voyager-message"></i> {{ $m->observacion }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Fila expandible de pagos --}}
                                        @if($tienePagos)
                                        <tr id="pagos-{{ $m->id }}" style="display:none; background:#f8fbff;">
                                            <td colspan="7" style="padding: 0 0 0 32px; border-top: none;">
                                                <div style="padding: 10px 16px 14px 0;">
                                                    <table class="table table-condensed" style="margin-bottom:0; background:transparent;">
                                                        <thead>
                                                            <tr style="background:#eaf2fb;">
                                                                <th style="border-top:none; font-size:11px; color:#555;">#</th>
                                                                <th style="border-top:none; font-size:11px; color:#555;">Fecha</th>
                                                                <th style="text-align:right; border-top:none; font-size:11px; color:#555;">Monto</th>
                                                                <th style="border-top:none; font-size:11px; color:#555;">Cobrado por</th>
                                                                <th style="border-top:none; font-size:11px; color:#555;">Observación</th>
                                                                <th style="text-align:center; border-top:none; font-size:11px; color:#555;">Comprobante</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if($m->pagos->isEmpty() && (float) $m->monto_pagado > 0)
                                                                <tr style="background:#fffbe6;">
                                                                    <td style="font-size:11px; color:#aaa;">—</td>
                                                                    <td style="font-size:12px;">{{ $m->updated_at->format('d/m/Y') }}</td>
                                                                    <td style="text-align:right; font-weight:700; color:#27ae60; font-size:12px;">Bs {{ number_format((float) $m->monto_pagado, 2) }}</td>
                                                                    <td style="color:#aaa; font-size:11px;">—</td>
                                                                    <td style="color:#888; font-size:11px;"><em>Pago anterior al sistema de seguimiento</em></td>
                                                                    <td style="text-align:center; color:#aaa; font-size:11px;">—</td>
                                                                </tr>
                                                            @else
                                                                @foreach($m->pagos as $pago)
                                                                <tr>
                                                                    <td style="font-size:11px; color:#aaa;">{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</td>
                                                                    <td style="font-size:12px;">{{ $pago->fecha->format('d/m/Y') }}</td>
                                                                    <td style="text-align:right; font-weight:700; color:#27ae60; font-size:12px;">Bs {{ number_format((float) $pago->monto, 2) }}</td>
                                                                    <td style="font-size:12px; color:#555;">{{ optional($pago->registerUser)->name ?: 'Sistema' }}</td>
                                                                    <td style="font-size:11px; color:#888;">{{ $pago->observacion ?: '—' }}</td>
                                                                    <td style="text-align:center;">
                                                                        <a href="{{ route('dojo.mensualidades.pago.comprobante', $pago->id) }}"
                                                                           target="_blank"
                                                                           class="btn btn-xs btn-default"
                                                                           title="Imprimir comprobante">
                                                                            <i class="fa fa-print"></i>
                                                                        </a>
                                                                        <form method="POST"
                                                                              action="{{ route('dojo.mensualidades.pago.whatsapp', $pago->id) }}"
                                                                              class="form-whatsapp-comprobante"
                                                                              style="display:inline;">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                    class="btn btn-xs btn-success"
                                                                                    title="Enviar comprobante por WhatsApp">
                                                                                <i class="voyager-paper-plane"></i>
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif

                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @elseif(!auth()->user()->dojo_id)
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body text-center" style="padding: 50px 0; color:#aaa;">
                        <i class="fa fa-file-invoice-dollar" style="font-size:48px;"></i>
                        <p style="margin-top:14px;">Seleccione una sucursal para ver su historial.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@stop

@section('javascript')
<script>
    $(document).on('click', '.clickable-row', function() {
        var target  = $(this).data('target');
        var icon    = $(this).find('.toggle-icon');
        var $expand = $('#' + target);

        if ($expand.is(':visible')) {
            $expand.slideUp(180);
            icon.css('transform', 'rotate(0deg)');
        } else {
            $expand.slideDown(220);
            icon.css('transform', 'rotate(90deg)');
        }
    });

    $(document).on('submit', '.form-whatsapp-comprobante', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="voyager-refresh"></i>');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: {
                'Accept': 'application/json'
            },
            success: function(res) {
                toastr.success(res.message || 'Comprobante enviado por WhatsApp correctamente.');
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'No se pudo enviar el comprobante por WhatsApp.';

                toastr.error(message);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
</script>
@endsection
