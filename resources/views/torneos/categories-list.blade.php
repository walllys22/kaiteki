<div class="table-responsive">
    <table class="table table-bordered table-hover">
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
                        <button onclick="deleteCategory({{ $item->id }})" class="btn btn-sm btn-danger">
                            <i class="voyager-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No hay categorías configuradas para este torneo.</td>
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