<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover" style="font-size:13px;">
        <thead>
            <tr>
                <th style="width:120px;">Fecha</th>
                <th>Horario</th>
                <th style="width:150px;">Dojo</th>
                <th style="width:80px; text-align:center;">Presentes</th>
                <th style="width:80px; text-align:center;">Licencias</th>
                <th style="width:80px; text-align:center;">Faltas</th>
                <th style="width:80px; text-align:center;">Total</th>
                <th style="width:100px; text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                @php
                    $detalles   = $item->detalles;
                    $presentes  = $detalles->where('estado', 'asistencia')->count();
                    $licencias  = $detalles->where('estado', 'licencia')->count();
                    $faltas     = $detalles->where('estado', 'falta')->count();
                    $total      = $detalles->count();
                @endphp
                <tr>
                    <td style="vertical-align:middle; font-weight:600;">
                        {{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}
                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($item->fecha)->locale('es')->isoFormat('dddd') }}</small>
                    </td>
                    <td style="vertical-align:middle;">
                        <strong>{{ optional($item->horario)->nombre ?: '—' }}</strong>
                        @if(optional($item->horario)->tipo)
                            <br><small class="text-muted">{{ $item->horario->tipo }}</small>
                        @endif
                    </td>
                    <td style="vertical-align:middle;">{{ optional($item->dojo)->nombre ?: '—' }}</td>
                    <td style="text-align:center; vertical-align:middle;">
                        <span class="label label-success">{{ $presentes }}</span>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <span class="label label-warning">{{ $licencias }}</span>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <span class="label label-danger">{{ $faltas }}</span>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <span class="label label-default">{{ $total }}</span>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <a href="{{ route('voyager.asistencias.show', $item->id) }}"
                           class="btn btn-info btn-xs" title="Ver detalle">
                            <i class="voyager-eye"></i>
                        </a>
                        @if(auth()->user()->hasPermission('delete_asistencias'))
                        <form action="{{ route('voyager.asistencias.destroy', $item->id) }}"
                              method="POST" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar esta asistencia?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                                <i class="voyager-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Sin registros de asistencia.</td>
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
