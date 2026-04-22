<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Grado</th>
                <th>Aprobó</th>
                <th>Observaciones</th>
                <th class="actions text-right">Acciones</th>
            </tr>
        </thead>
        <tbody style="color: black;">
            @forelse($historial as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $item->tipo }}</td>
                    <td>
                        @if($item->grado)
                            {{ $item->grado->tipo }} {{ $item->grado->numero }} {{ $item->grado->nombre }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if(is_null($item->aprobo) || $item->aprobo === '')
                            <span class="label label-default"></span>
                        @else
                            <span class="label label-{{ $item->aprobo ? 'success' : 'danger' }}">
                                {{ $item->aprobo ? 'Sí' : 'No' }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $item->observaciones }}</td>
                    <td class="no-sort no-click text-right">
                        <button title="Eliminar" class="btn btn-sm btn-danger delete" onclick="deleteItem('{{ url('admin/alumnos/historial/delete/'.$item->id) }}')" data-toggle="modal" data-target="#delete_modal">
                            <i class="voyager-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No hay registros de historial para este alumno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pull-right">
    {{ $historial->appends(['search' => request('search'), 'paginate' => request('paginate')])->links() }}
</div>

<script>
    $('.pagination a').on('click', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        listHistorial(page);
    });
</script>