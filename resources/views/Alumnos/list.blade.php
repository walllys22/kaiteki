<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    <th style="text-align: center">Nombre Dojo</th>                    
                    <th style="text-align: center">Nombre Completo</th>
                    <th style="text-align: center">Ingreso</th>                    
                    <th style="text-align: center">Horario</th>
                    <th style="text-align: center">Grado</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody style="color: black;">
                @forelse ($data as $item)
                @php
                    $image = asset('images/default.jpg');
                    if($item->person->image){
                        $image = asset('storage/' . str_replace('.avif', '', $item->person->image) . '-cropped.webp');
                    }
                @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td style="text-align: center">{{ $item->dojo->nombre }} </td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="{{ $image }}" alt="{{ $item->person->first_name }}" class="image-expandable" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px; object-fit: cover;">
                            <div>
                                {{ $item->person->first_name }} 
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center">
                        {{ \Carbon\Carbon::parse($item->entry_date)->format('d/m/Y') }}
                    </td>
                    <td style="text-align: center"> {{ $item->Horario->tipo }} {{ $item->Horario->nombre }}</td>
                    <td style="text-align: center"> {{ $item->grado->numero}} {{ $item->grado->tipo }} {{ $item->grado->nombre}} </td>
                    <td style="text-align: center">
                        @if ($item->status==1)  
                            <label class="label label-success">Activo</label>
                        @else
                            <label class="label label-warning">Inactivo</label>
                        @endif
                    </td>
                    <td style="width: 18%" class="no-sort no-click bread-actions text-right">

                            @if (auth()->user()->hasPermission('read_alumnos'))
                            <a href="{{ route('voyager.alumnos.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('edit_alumnos'))
                            <a href="{{ route('voyager.alumnos.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_alumnos'))
                            <a href="javascript:void(0);" onclick="deleteItem('{{ $item->id }}', '{{ route('voyager.alumnos.destroy', ['id' => $item->id]) }}')" title="Eliminar" class="btn btn-sm btn-danger">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        <div class="row" >
                            <div class="btn group">
                                <a href="{{ route('alumnos.historial.show', $item->id) }}" title="Historial" class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-file-pen"></i> <span class="hidden-xs hidden-sm"></span>
                                </a>
                                <a href="#" onclick="statusItem('{{ $item->id }}', '{{ $item->person->first_name }}', '{{ $item->dojo->nombre }}')" title="Estado" class="btn btn-sm btn-dark">
                                    <i class="fa-solid fa-person-circle-xmark"></i> <span class="hidden-xs hidden-sm"></span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="8">
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