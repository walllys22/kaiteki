<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    <th style="text-align: center">Nombre Completo</th>
                    <th style="text-align: center">Fecha Ingrero</th>                    
                    <th style="text-align: center">Horario</th>
                    <th style="text-align: center">Grado</th>
                    <th style="text-align: center">Responsable</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Registrado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                @php
                    $image = asset('images/default.jpg');
                    if($item->archivo){
                        $image = asset('storage/' . str_replace('.avif', '', $item->archivo) . '-cropped.webp');
                    }
                @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->nombre }}</td>       
                </tr>
                @empty
                    <tr>
                        <td colspan="9">
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
</div>

<div class="col-md-12">
    <div class="col-md-4" style="overflow-x:auto">
        @if(count($data)>0)
            <p class="text-muted">Mostrando del {{$data->firstItem()}} al {{$data->lastItem()}} de {{$data->total()}} registros.</p>
        @endif
    </div>
    <div class="col-md-8" style="overflow-x:auto">
        <nav class="text-right">
            {{ $data->links() }}
        </nav>
    </div>
</div>

<script>
   
   var page = "{{ request('page') }}";
    $(document).ready(function(){
        $('.page-link').click(function(e){
            e.preventDefault();
            let link = $(this).attr('href');
            if(link){
                page = link.split('=')[1];
                list(page);
            }
        });
    });
</script>