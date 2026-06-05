@php
    $isGlobalAdmin = !auth()->user()->dojo_id;
@endphp

<input type="hidden" id="ultima-fecha-fin" value="{{ $mensualidades->first()?->fecha_fin?->format('Y-m-d') ?? '' }}">

@if($mensualidades->isEmpty())
    <div class="text-center" style="padding: 30px 0;">
        <p class="text-muted">No hay mensualidades registradas para esta sucursal.</p>
    </div>
@else
    <div class="table-responsive">
        <table id="dataTable" class="table table-hover table-condensed" style="margin-bottom:0;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="border-top:none; width:16px;"></th>
                    <th style="border-top:none;">Fecha inicio</th>
                    <th style="border-top:none;">Fecha fin</th>
                    <th style="text-align:right; border-top:none;">Monto</th>
                    <th style="text-align:right; border-top:none;">Pagado</th>
                    <th style="text-align:right; border-top:none;">Saldo</th>
                    <th style="text-align:center; border-top:none;">Estado</th>
                    @if($isGlobalAdmin)
                        <th style="text-align:center; border-top:none;">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($mensualidades as $m)
                    @php
                        $estado      = $m->estadoPago();
                        $isVigente   = $m->isVigente();
                        $saldo       = $m->saldo();
                        $isUltima    = $loop->first;
                        $tienePagos  = $m->pagos->isNotEmpty() || (float) $m->monto_pagado > 0;
                        $estadoClass = match($estado) {
                            'Pagado'  => 'success',
                            'Vencido' => 'danger',
                            default   => 'warning',
                        };
                    @endphp

                    {{-- Fila principal --}}
                    <tr class="{{ $tienePagos ? 'clickable-mensualidad' : '' }}"
                        style="{{ $tienePagos ? 'cursor:pointer;' : '' }}"
                        @if($tienePagos) data-target="mpagos-{{ $m->id }}" @endif>
                        <td style="vertical-align:middle; padding-left:10px;">
                            @if($tienePagos)
                                <i class="fa fa-chevron-right toggle-icon-m" style="font-size:9px; color:#aaa; transition:transform .2s;"></i>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">
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
                        @if($isGlobalAdmin)
                            <td class="no-sort no-click bread-actions text-center">
                                @if($isUltima)
                                    <button type="button"
                                            class="btn btn-sm btn-primary btn-editar-fecha-fin"
                                            data-id="{{ $m->id }}"
                                            data-url="{{ route('dojo.mensualidades.fecha-fin', $m->id) }}"
                                            data-fecha="{{ $m->fecha_fin->format('Y-m-d') }}"
                                            data-min="{{ $m->fecha_inicio->format('Y-m-d') }}"
                                            title="Editar fecha fin">
                                        <i class="voyager-edit"></i>
                                    </button>
                                @endif
                                @if($saldo > 0)
                                    <button type="button"
                                            class="btn btn-sm btn-success btn-pagar-mensualidad"
                                            data-id="{{ $m->id }}"
                                            data-url="{{ route('dojo.mensualidades.pagar', $m->id) }}"
                                            data-pagos-url="{{ route('dojo.mensualidades.pagos', $m->id) }}"
                                            data-monto="{{ $m->monto }}"
                                            data-pagado="{{ $m->monto_pagado }}"
                                            data-saldo="{{ $saldo }}"
                                            data-periodo="{{ $m->fecha_inicio->format('d/m/Y') }} - {{ $m->fecha_fin->format('d/m/Y') }}">
                                        <i class="voyager-dollar"></i>
                                    </button>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-danger btn-eliminar-mensualidad"
                                        data-id="{{ $m->id }}"
                                        data-url="{{ route('dojo.mensualidades.destroy', $m->id) }}"
                                        data-periodo="{{ $m->fecha_inicio->format('d/m/Y') }} - {{ $m->fecha_fin->format('d/m/Y') }}">
                                    <i class="voyager-trash"></i>
                                </button>
                            </td>
                        @endif
                    </tr>

                    @if($m->observacion)
                        <tr style="background:#fffdf0;">
                            <td colspan="{{ $isGlobalAdmin ? 8 : 7 }}" style="border-top:none; color:#888; font-size:11px; padding: 3px 10px 5px 32px;">
                                <i class="voyager-message"></i> {{ $m->observacion }}
                            </td>
                        </tr>
                    @endif

                    {{-- Fila expandible de pagos --}}
                    @if($tienePagos)
                    <tr id="mpagos-{{ $m->id }}" style="display:none; background:#f8fbff;">
                        <td colspan="{{ $isGlobalAdmin ? 8 : 7 }}" style="padding: 0 0 0 28px; border-top:none;">
                            <div style="padding: 8px 12px 12px 0;">
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
                                                       class="btn btn-sm btn-default"
                                                       title="Imprimir comprobante">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                    <form method="POST"
                                                          action="{{ route('dojo.mensualidades.pago.whatsapp', $pago->id) }}"
                                                          class="form-whatsapp-comprobante"
                                                          style="display:inline;">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success"
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
    </div>
@endif
