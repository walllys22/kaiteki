<div class="table-responsive">
    <table class="table table-bordered table-hover" style="font-size:13px;">
        <thead>
            <tr>
                <th>Horario</th>
                <th style="width:100px;">Tipo</th>
                <th style="width:130px;">Dojo</th>
                <th style="width:100px; text-align:center;">Estado</th>
                <th style="width:130px;">Fecha Asignación</th>
                <th>Observación</th>
                @if(auth()->user()->hasPermission('delete_alumnos') && ($alumnoActivo ?? true))
                <th style="width:60px; text-align:center;">Acc.</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                @php
                    $h = $item->horario;
                @endphp
                <tr>
                    <td style="vertical-align:middle;">
                        <strong>{{ optional($h)->nombre ?: '—' }}</strong>
                    </td>
                    <td style="vertical-align:middle;">
                        {{ optional($h)->tipo ?: '—' }}
                    </td>
                    <td style="vertical-align:middle;">
                        {{ optional(optional($h)->dojo)->nombre ?: '—' }}
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        @if($item->status == '1' || $item->status == 1)
                            <span class="label label-success">Activo</span>
                        @else
                            <span class="label label-default">Inactivo</span>
                        @endif
                    </td>
                    <td style="vertical-align:middle;">
                        {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '—' }}
                    </td>
                    <td style="vertical-align:middle;">{{ $item->observacion ?: '—' }}</td>
                    @if(auth()->user()->hasPermission('delete_alumnos') && ($alumnoActivo ?? true))
                    <td style="text-align:center; vertical-align:middle;">
                        @if($item->status == '1' || $item->status == 1)
                            <a href="#" onclick="deleteItem('{{ route('alumno.horario.destroy', $item->id) }}')"
                               data-toggle="modal" data-target="#modal_delete_confirm"
                               class="btn btn-danger btn-xs">
                                <i class="voyager-trash"></i>
                            </a>
                        @else
                            <span class="text-muted" title="No se puede eliminar un horario inactivo">—</span>
                        @endif
                    </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->hasPermission('delete_alumnos') && ($alumnoActivo ?? true) ? 7 : 6 }}"
                        class="text-center text-muted">
                        Sin horarios asignados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($data->count())
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
