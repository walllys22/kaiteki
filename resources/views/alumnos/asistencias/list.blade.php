{{-- Resumen general del alumno --}}
@if($totales && $totales->total > 0)
<div class="row" style="margin-bottom:15px;">
    <div class="col-xs-3">
        <div style="text-align:center; background:#f9fff9; border:1px solid #c3e6c3; border-radius:8px; padding:10px;">
            <div style="font-size:22px; font-weight:700; color:#27ae60;">{{ $totales->presentes }}</div>
            <small class="text-muted">Asistencias</small>
        </div>
    </div>
    <div class="col-xs-3">
        <div style="text-align:center; background:#fffdf5; border:1px solid #f5d79e; border-radius:8px; padding:10px;">
            <div style="font-size:22px; font-weight:700; color:#e67e22;">{{ $totales->licencias }}</div>
            <small class="text-muted">Licencias</small>
        </div>
    </div>
    <div class="col-xs-3">
        <div style="text-align:center; background:#fff9f9; border:1px solid #f5c6c6; border-radius:8px; padding:10px;">
            <div style="font-size:22px; font-weight:700; color:#c0392b;">{{ $totales->faltas }}</div>
            <small class="text-muted">Faltas</small>
        </div>
    </div>
    <div class="col-xs-3">
        <div style="text-align:center; background:#f5f5f5; border:1px solid #ddd; border-radius:8px; padding:10px;">
            @php
                $pct = $totales->total > 0 ? round($totales->presentes / $totales->total * 100) : 0;
            @endphp
            <div style="font-size:22px; font-weight:700; color:{{ $pct >= 75 ? '#27ae60' : ($pct >= 50 ? '#e67e22' : '#c0392b') }};">
                {{ $pct }}%
            </div>
            <small class="text-muted">Asistencia</small>
        </div>
    </div>
</div>
@endif

{{-- Tabla historial --}}
<div class="table-responsive">
    <table class="table table-bordered table-hover" style="font-size:13px;">
        <thead>
            <tr>
                <th style="width:120px;">Fecha</th>
                <th>Horario</th>
                <th style="width:130px;">Dojo</th>
                <th style="width:130px; text-align:center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $det)
                @php $a = $det->asistencia; @endphp
                <tr>
                    <td style="vertical-align:middle; font-weight:600;">
                        {{ $a ? \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') : '—' }}
                        @if($a)
                            <br><small class="text-muted">
                                {{ \Carbon\Carbon::parse($a->fecha)->locale('es')->isoFormat('dddd') }}
                            </small>
                        @endif
                    </td>
                    <td style="vertical-align:middle;">
                        {{ optional(optional($a)->horario)->nombre ?: '—' }}
                        @if(optional(optional($a)->horario)->tipo)
                            <br><small class="text-muted">{{ $a->horario->tipo }}</small>
                        @endif
                    </td>
                    <td style="vertical-align:middle;">
                        {{ optional(optional($a)->dojo)->nombre ?: '—' }}
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        @if($det->estado === 'asistencia')
                            <span class="label label-success" style="font-size:12px; padding:5px 10px;">✓ Asistencia</span>
                        @elseif($det->estado === 'licencia')
                            <span class="label label-warning" style="font-size:12px; padding:5px 10px;">📋 Licencia</span>
                        @else
                            <span class="label label-danger" style="font-size:12px; padding:5px 10px;">✗ Falta</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Sin registros de asistencia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($data->count())
    <div class="row">
        <div class="col-md-6">
            <p class="text-muted" style="font-size:12px;">
                Mostrando {{ $data->firstItem() }}–{{ $data->lastItem() }} de {{ $data->total() }} registros.
            </p>
        </div>
        <div class="col-md-6 text-right">
            {{ $data->links() }}
        </div>
    </div>
@endif
