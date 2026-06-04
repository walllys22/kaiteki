<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">Documento</th>
                    <th style="text-align: center">Nombre completo</th>                    
                    <th style="text-align: center">Datos personales</th>
                    <th style="text-align: center">Contacto</th>
                    <th style="text-align: center">Registrado por</th>
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
                    $image = asset('images/default.jpg');
                    if($item->image){
                        $image = asset('storage/' . str_replace('.avif', '', $item->image) . '-cropped.webp');
                    }
                    $now = \Carbon\Carbon::now();
                    $birthday = new \Carbon\Carbon($item->birth_date);
                    $age = $birthday->diffInYears($now);
                @endphp
                <tr>
                    <td style="text-align: center">
                        <strong>{{ $item->documentType }}</strong><br>
                        <span>{{ $item->ci ?: 'Sin documento' }}</span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="{{ $image }}" alt="{{ $item->first_name }}" class="image-expandable" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px; object-fit: cover;">
                            <div>
                                <strong>{{ strtoupper($item->first_name) }}</strong><br>
                                @if($item->dojo)
                                    <span class="label label-primary">{{ $item->dojo->nombre }}</span>
                                @else
                                    <span class="text-muted">Sin dojo asignado</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center">
                        @if ($item->birth_date)
                            <div><strong>Nacimiento:</strong> {{ \Carbon\Carbon::parse($item->birth_date)->format('d/m/Y') }}</div>
                            <small>{{ $age }} años</small><br>
                        @else
                            <span class="text-muted">Sin fecha de nacimiento</span><br>
                        @endif
                        <small><strong>Género:</strong> {{ $item->gender ?: 'No registrado' }}</small><br>
                        <small><strong>Tipo sangre:</strong> {{ $item->sangre ?: 'No registrado' }}</small>
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
                                @if($item->email)
                                    <small style="margin-top: 5px; display: block;">{{ $item->email }}</small>
                                @endif
                                @if($item->address)
                                    <small class="text-muted" style="margin-top: 4px; display: block;">{{ $item->address }}</small>
                                @endif
                            </div>
                        @else
                            <span class="text-muted" style="font-style: italic;">No registrado</span>
                            @if($item->email)
                                <br><small>{{ $item->email }}</small>
                            @endif
                            @if($item->address)
                                <br><small class="text-muted">{{ $item->address }}</small>
                            @endif
                        @endif
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        @if($item->register)
                            <div style="font-size:12px; font-weight:600;">{{ $item->register->name }}</div>
                        @endif
                        <div style="font-size:11px; color:#888; margin-top:2px;">
                            {{ $item->created_at ? $item->created_at->format('d/m/Y') : '—' }}
                        </div>
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
                        <td colspan="6">
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
