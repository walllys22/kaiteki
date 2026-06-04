@php
    $isGlobalAdmin = !auth()->user()->dojo_id;
@endphp

@if($mensualidades->isEmpty())
    <div class="text-center" style="padding: 30px 0;">
        <p class="text-muted">No hay mensualidades registradas para esta sucursal.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover table-condensed" style="margin-bottom:0;">
            <thead style="background:#f8f9fa;">
                <tr>
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
                        $estado    = $m->estadoPago();
                        $isVigente = $m->isVigente();
                        $saldo     = $m->saldo();
                        $estadoClass = match($estado) {
                            'Pagado'  => 'success',
                            'Vencido' => 'danger',
                            default   => 'warning',
                        };
                    @endphp
                    <tr>
                        <td style="vertical-align:middle;">
                            {{ $m->fecha_inicio->format('d/m/Y') }}
                            @if($isVigente)
                                <span class="label label-success" style="margin-left:4px;">Vigente</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">{{ $m->fecha_fin->format('d/m/Y') }}</td>
                        <td style="text-align:right; vertical-align:middle;">{{ number_format($m->monto, 2) }}</td>
                        <td style="text-align:right; vertical-align:middle;">{{ number_format($m->monto_pagado, 2) }}</td>
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
                            <td style="text-align:center; vertical-align:middle; white-space:nowrap;">
                                @if($saldo > 0)
                                    <button type="button"
                                            class="btn btn-xs btn-success btn-pagar-mensualidad"
                                            data-id="{{ $m->id }}"
                                            data-url="{{ route('dojo.mensualidades.pagar', $m->id) }}"
                                            data-pagos-url="{{ route('dojo.mensualidades.pagos', $m->id) }}"
                                            data-monto="{{ $m->monto }}"
                                            data-pagado="{{ $m->monto_pagado }}"
                                            data-saldo="{{ $saldo }}"
                                            data-periodo="{{ $m->fecha_inicio->format('d/m/Y') }} - {{ $m->fecha_fin->format('d/m/Y') }}">
                                        <i class="voyager-dollar"></i> Pagar
                                    </button>
                                @endif
                                @if((float) $m->monto_pagado > 0 || $m->pagos_count > 0)
                                    <button type="button"
                                            class="btn btn-xs btn-default btn-ver-pagos"
                                            data-url="{{ route('dojo.mensualidades.pagos', $m->id) }}"
                                            data-periodo="{{ $m->fecha_inicio->format('d/m/Y') }} - {{ $m->fecha_fin->format('d/m/Y') }}"
                                            title="Ver pagos registrados">
                                        <i class="fa fa-list"></i>
                                        @if($m->pagos_count > 0)
                                            {{ $m->pagos_count }}
                                        @endif
                                    </button>
                                @endif
                                <button type="button"
                                        class="btn btn-xs btn-danger btn-eliminar-mensualidad"
                                        data-id="{{ $m->id }}"
                                        data-url="{{ route('dojo.mensualidades.destroy', $m->id) }}"
                                        data-periodo="{{ $m->fecha_inicio->format('d/m/Y') }} - {{ $m->fecha_fin->format('d/m/Y') }}">
                                    <i class="voyager-trash"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                    @if($m->observacion)
                        <tr>
                            <td colspan="{{ $isGlobalAdmin ? 7 : 6 }}" style="padding-top:0; padding-bottom:6px; border-top:none; color:#888; font-size:0.88em;">
                                <i class="voyager-message" style="margin-right:4px;"></i>{{ $m->observacion }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif
