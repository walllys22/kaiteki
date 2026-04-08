<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    <th style="text-align: center">Nombre</th>
                    <th style="text-align: center">Ciudad</th>                    
                    <th style="text-align: center">Fecha Inicio</th>
                    <th style="text-align: center">Fecha Final</th>
                    <th style="text-align: center">Responsable</th>
                    <th style="text-align: center">Modalidades</th>
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
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="{{ $image }}" alt="{{ $item->nombre }}" class="image-expandable" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px; object-fit: cover;">
                            <div>
                                {{ strtoupper($item->nombre) }} 
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center">
                        {{ $item->ciudad->nombre }}
                    </td>
                    <td style="text-align: center">
                        {{ \Carbon\Carbon::parse($item->fechainicio)->format('d/m/Y') }}
                    </td>
                    <td style="text-align: center">
                        {{ \Carbon\Carbon::parse($item->fechafinal)->format('d/m/Y') }}
                    </td>
                    <td style="text-align: center">
                        {{ $item->person->first_name }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $ids = is_array($item->modalidad_id) ? $item->modalidad_id : [];
                            $nombres = \App\Models\Modalida::whereIn('id', $ids)->pluck('nombre');
                        @endphp
                        @foreach ($nombres as $nombre)
                            <span class="label label-info" style="display: inline-block; margin-bottom: 2px;">{{ $nombre }}</span>
                        @endforeach
                    </td>
                 
                    <td style="text-align: center">
                        @if ($item->estado=="En curso")  
                            <label class="label label-success">{{$item->estado}}</label>
                        @else
                            <label class="label label-warning">{{$item->estado}}</label>
                        @endif
                    </td>
                    <td style="text-align: center">
                        {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td style="width: 18%" class="no-sort no-click bread-actions text-right">
                        @if (auth()->user()->hasPermission('read_torneos'))
                            <a href="{{ route('voyager.torneos.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('edit_torneos'))
                            <a href="{{ route('voyager.torneos.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_torneos'))
                            <a href="#" onclick="deleteItem('{{ route('voyager.torneos.destroy', ['id' => $item->id]) }}')" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="10">
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