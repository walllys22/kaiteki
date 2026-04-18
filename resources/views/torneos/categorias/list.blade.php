<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Sexo</th>
                <th>Modalidad</th>
                <th class="text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>{{ $item->categoria->nombre }}</td>
                    <td>{{ $item->categoria->sexo }}</td>
                    <td>{{ $item->modalidad->nombre }}</td>
                    <td class="text-right">
                        <a href="{{ route('voyager.torneos.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                            <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                        </a>                   
                        <a href="#" onclick="deleteItem('{{ route('torneos.categories.destroy', ['id' => $item->id]) }}')" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                        <td colspan="4">
                            <h5 class="text-center" style="margin-top: 50px">
                                <img src="{{ asset('images/empty.png') }}" width="120px" alt="" style="opacity: 0.8">
                                <br><br>
                                No hay resultados
                            </h5>
                        </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="text-right">
    {{ $data->links() }}
</div>

<script>
    $('.page-link').click(function(e){
        e.preventDefault();
        let link = $(this).attr('href');
        if(link){
            let page = link.split('=')[1];
            listCategories(page);
        }
    });
</script>