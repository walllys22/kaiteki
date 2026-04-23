<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Arancel</th>
                <th>Monto</th>
                <th>Estado</th>
                <th class="actions text-right">Acciones</th>
            </tr>
        </thead>
        <tbody style="color: black;">
            @forelse($aranceles as $item)
                <tr>
                    <td>{{ $item->arancel->nombre ?? 'Sin nombre' }}</td>
                    <td>{{ number_format($item->monto, 2) }}</td>
                    <td>
                        @php
                            $label = 'default';
                            if ($item->status == 'Pagado') {
                                $label = 'success';
                            }
                            if ($item->status == 'Pendiente') {
                                $label = 'warning';
                            }
                        @endphp
                        <span class="label label-{{ $label }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="no-sort no-click text-right">
                        <button title="Eliminar" class="btn btn-sm btn-danger delete"
                            onclick="deleteItem('{{ url('admin/alumnos/pagos/delete/' . $item->id) }}')"
                            data-toggle="modal" data-target="#delete_modal">
                            <i class="voyager-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <h5 class="text-center" style="margin-top: 50px">
                            <img src="{{ asset('images/empty.png') }}" width="120px" alt=""
                                style="opacity: 0.8">
                            <br><br>
                            No hay resultados
                        </h5>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pull-right">
    {{ $aranceles->appends(['search' => request('search'), 'paginate' => request('paginate')])->links() }}
</div>

<script>
    $('.pagination a').on('click', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        listPagos(page);
    });
</script>
