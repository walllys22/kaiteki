@php
    $tienePagosRegistrados = $mensualidad->pagos->isNotEmpty();
    $montoPagadoAntiguo    = $tienePagosRegistrados
        ? 0
        : (float) $mensualidad->monto_pagado;
@endphp

@if(!$tienePagosRegistrados && $montoPagadoAntiguo <= 0)
    <div class="text-center" style="padding: 20px 0;">
        <p class="text-muted">No hay pagos registrados para esta mensualidad.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-condensed table-hover" style="margin-bottom:0;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="border-top:none;">#</th>
                    <th style="border-top:none;">Fecha</th>
                    <th style="text-align:right; border-top:none;">Monto</th>
                    <th style="border-top:none;">Cobrado por</th>
                    <th style="border-top:none;">Observación</th>
                    <th style="text-align:center; border-top:none;">Comprobante</th>
                </tr>
            </thead>
            <tbody>
                @php $acumulado = 0; @endphp
                @if(!$tienePagosRegistrados && $montoPagadoAntiguo > 0)
                    @php $acumulado = $montoPagadoAntiguo; @endphp
                    <tr style="background:#fffbe6;">
                        <td style="vertical-align:middle; color:#888; font-size:11px;">—</td>
                        <td style="vertical-align:middle;">{{ $mensualidad->updated_at->format('d/m/Y') }}</td>
                        <td style="text-align:right; vertical-align:middle; font-weight:700; color:#27ae60;">
                            Bs {{ number_format($montoPagadoAntiguo, 2) }}
                        </td>
                        <td style="vertical-align:middle; color:#888;">—</td>
                        <td style="vertical-align:middle; color:#888; font-size:11px;">
                            <em>Pago registrado antes del sistema de seguimiento</em>
                        </td>
                        <td style="text-align:center; vertical-align:middle; color:#aaa;">—</td>
                    </tr>
                @endif
                @foreach($mensualidad->pagos as $pago)
                    @php $acumulado += (float) $pago->monto; @endphp
                    <tr>
                        <td style="vertical-align:middle; color:#888; font-size:11px;">
                            {{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="vertical-align:middle;">{{ $pago->fecha->format('d/m/Y') }}</td>
                        <td style="text-align:right; vertical-align:middle; font-weight:700; color:#27ae60;">
                            Bs {{ number_format((float) $pago->monto, 2) }}
                        </td>
                        <td style="vertical-align:middle; color:#555;">
                            {{ optional($pago->registerUser)->name ?: 'Sistema' }}
                        </td>
                        <td style="vertical-align:middle; color:#888; font-size:11px;">
                            {{ $pago->observacion ?: '—' }}
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                            <a href="{{ route('dojo.mensualidades.pago.comprobante', $pago->id) }}"
                               target="_blank"
                               class="btn btn-xs btn-default"
                               title="Imprimir comprobante">
                                <i class="fa fa-print"></i> Imprimir
                            </a>
                            <form method="POST"
                                  action="{{ route('dojo.mensualidades.pago.whatsapp', $pago->id) }}"
                                  class="form-whatsapp-comprobante"
                                  style="display:inline;">
                                @csrf
                                <button type="submit"
                                        class="btn btn-xs btn-success"
                                        title="Enviar comprobante por WhatsApp">
                                    <i class="voyager-phone"></i> WhatsApp
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8f9fa;">
                    <td colspan="2" style="font-weight:700; text-align:right;">Total pagado:</td>
                    <td style="text-align:right; font-weight:700;">Bs {{ number_format($acumulado, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif
