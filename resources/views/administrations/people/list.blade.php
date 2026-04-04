<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    <th style="text-align: center">Documento</th>
                    <th style="text-align: center">Nombre completo</th>                    
                    <th style="text-align: center">Fecha nac.</th>
                    <th style="text-align: center">Telefono/Celular</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                @php
                    $image = asset('images/default.jpg');
                    if($item->image){
                        $image = asset('storage/' . str_replace('.avif', '', $item->image) . '-cropped.webp');
                    }
                    $now = \Carbon\Carbon::now();
                    $birthday = new \Carbon\Carbon($item->birth_date);
                    $age = $birthday->diffInYears($now);
                @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->documentType }}: {{ $item->ci }}</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="{{ $image }}" alt="{{ $item->first_name }}" class="image-expandable" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px; object-fit: cover;">
                            <div>
                                {{ strtoupper($item->first_name) }} {{ $item->middle_name ? strtoupper($item->middle_name) : '' }} {{ strtoupper($item->paternal_surname) }}  {{ strtoupper($item->maternal_surname) }}
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center">
                        @if ($item->birth_date)
                            {{ \Carbon\Carbon::parse($item->birth_date)->format('d/m/Y') }} <br> <small>{{ $age }} años</small>
                        @else
                            Sin Datos                            
                        @endif
                    </td>
                    <td style="text-align: center">
                        @if($item->phone)
                            @php
                                $countryCodes = ['591' => 'bo', '54' => 'ar', '55' => 'br', '56' => 'cl', '51' => 'pe', '1' => 'us', '34' => 'es'];
                                $countryNames = ['591' => 'Bolivia', '54' => 'Argentina', '55' => 'Brasil', '56' => 'Chile', '51' => 'Perú', '1' => 'USA', '34' => 'España'];
                                $code = $item->country_code ?? '591';
                                $flag = $countryCodes[$code] ?? 'bo';
                                $countryName = $countryNames[$code] ?? '';
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <span style="font-weight: bold; font-size: 13px; white-space: nowrap;">
                                    <span class="fi fi-{{ $flag }}" title="{{ $countryName }}" style="margin-right: 3px; box-shadow: 1px 1px 3px rgba(0,0,0,0.2);"></span> +{{ $code }} {{ $item->phone }}
                                </span>
                                <a href="https://wa.me/{{ $code }}{{ $item->phone }}?text=Hola {{ $item->first_name }}" target="_blank" class="label label-success" style="margin-top: 5px; padding: 3px 8px; font-size: 10px; text-decoration: none; cursor: pointer;">
                                    <i class="voyager-paper-plane"></i> WhatsApp
                                </a>
                            </div>
                        @else
                            <span class="text-muted" style="font-style: italic;">No registrado</span>
                        @endif
                    </td>
                    <td style="text-align: center">
                        @if ($item->status==1)  
                            <label class="label label-success">Activo</label>
                        @else
                            <label class="label label-warning">Inactivo</label>
                        @endif

                        
                    </td>
                    <td style="width: 18%" class="no-sort no-click bread-actions text-right">
                        @if (auth()->user()->hasPermission('read_people'))
                            <a href="{{ route('voyager.people.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('edit_people'))
                            <a href="{{ route('voyager.people.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_people'))
                            <a href="#" onclick="deleteItem('{{ route('voyager.people.destroy', ['id' => $item->id]) }}')" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7">
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