<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="text-align:center; width: 70px;">ID</th>
                <th>Detalle del Grado</th>
                <th>Fecha</th>
                <th>Observación</th>
                <th style="text-align:center; width: 130px;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                @php
                    $grado = $item->grado;
                    $gradoLabel = trim(($grado->tipo ?? '').' '.($grado->numero ?? '').' '.($grado->nombre ?? ''));
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $item->id }}</td>
                    <td>
                        <strong>{{ $gradoLabel ?: 'Grado no disponible' }}</strong>
                        @if ($grado)
                            <br>
                            <small class="text-muted">
                                Puntas: <strong>{{ $grado->puntas ?? 0 }}</strong>
                                |
                                Días: <strong>{{ $grado->dias ?? 0 }}</strong>
                            </small>
                        @endif
                    </td>
                    <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : 'No registrada' }}</td>
                    <td>{{ $item->observacion ?: 'Sin observación' }}</td>
                    <td style="text-align:center;">
                        <span class="label {{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'label-success' : 'label-default' }}">
                            {{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay grados registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($data->count())
    <div class="row">
        <div class="col-md-6">
            <p class="text-muted">Mostrando del {{ $data->firstItem() }} al {{ $data->lastItem() }} de {{ $data->total() }} registros.</p>
        </div>
        <div class="col-md-6 text-right">
            {{ $data->links() }}
        </div>
    </div>
@endif
