<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 44px">#</th>
                    <th style="text-align: center">Grado</th>
                    <th style="text-align: center">Puntas</th>
                    <th style="text-align: center">Dias</th>
                    <th style="text-align: center; min-width: 220px;">Aranceles</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    @php
                        $gradoLabel = trim(($item->tipo ?? '') . ' ' . ($item->numero ?? '') . ' ' . ($item->nombre ?? ''));
                    @endphp
                    <tr>
                        <td style="text-align: center; vertical-align: middle;">
                            @if($item->orden)
                                <span class="label" style="background:#6c757d; font-size:12px; padding:3px 7px;">{{ $item->orden }}</span>
                            @else
                                <span style="color:#ccc; font-size:11px;">—</span>
                            @endif
                        </td>
                        <td style="vertical-align: middle;">
                            @if($item->image)
                                @php $imgSrc = \Storage::disk(env('FILESYSTEM_DRIVER'))->url(str_replace('.avif', '', $item->image) . '-cropped.webp'); @endphp
                                <img src="{{ $imgSrc }}" alt="{{ $gradoLabel }}" class="image-expandable"
                                     style="width:50px; height:50px; border-radius:6px; object-fit:cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                <span style="display:none; width:50px; height:50px; border-radius:6px; background:#f0f4f8;
                                             align-items:center; justify-content:center; color:#aaa; font-size:20px;">
                                    <i class="fa-solid fa-image"></i>
                                </span>
                            @else
                                <span style="display:inline-flex; width:50px; height:50px; border-radius:6px; background:#f0f4f8;
                                             align-items:center; justify-content:center; color:#ccc; font-size:20px;">
                                    <i class="fa-solid fa-image"></i>
                                </span>
                            @endif
                            <strong>{{ $gradoLabel ?: 'Sin nombre' }}</strong>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <label class="label label-info">{{ $item->puntas }} punta(s)</label>
                            
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <label class="label label-default" style="margin-top: 6px;">{{ $item->dias }} dia(s)</label>
                        </td>
                        <td style="vertical-align: middle;">
                            @if($item->aranceles->count())
                                <div class="grado-aranceles-stack">
                                    @foreach($item->aranceles as $arancel)
                                        <div class="grado-arancel-line {{ (int) $arancel->status === 1 ? 'activo' : 'inactivo' }}">
                                            <span class="grado-arancel-tipo">{{ $arancel->tipo }}</span>
                                            <span class="grado-arancel-precio">Bs {{ number_format((float) $arancel->precio, 2, '.', ',') }}</span>
                                            @if(!$userDojoId)
                                                <span class="grado-arancel-dojo">{{ optional($arancel->dojo)->nombre ?: 'Sin dojo' }}</span>
                                            @endif
                                            @if((int) $arancel->status !== 1)
                                                <span class="grado-arancel-estado">Inactivo</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted" style="font-size:12px;">
                                    {{ $userDojoId ? 'Sin arancel para tu dojo' : 'Sin aranceles' }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if ($item->status == 1)
                                <label class="label label-success">Activo</label>
                            @else
                                <label class="label label-warning">Inactivo</label>
                            @endif
                        </td>
                        <td style="vertical-align: middle; width: 18%" class="no-sort no-click bread-actions text-right">
                            @if (auth()->user()->hasPermission('read_grados') && Route::has('voyager.grados.show'))
                                <a href="{{ route('voyager.grados.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                    <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('edit_grados') && Route::has('voyager.grados.edit'))
                                <a href="{{ route('voyager.grados.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                    <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm"></span>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete_grados') && Route::has('voyager.grados.destroy'))
                                <a href="#" onclick="deleteItem('{{ route('voyager.grados.destroy', ['id' => $item->id]) }}')" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete">
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

<style>
    .grado-aranceles-stack {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 210px;
    }
    .grado-arancel-line {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #dbe7f0;
        border-left: 3px solid #2ecc71;
        border-radius: 4px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px 7px;
        padding: 5px 7px;
    }
    .grado-arancel-line.inactivo {
        border-left-color: #95a5a6;
        opacity: .78;
    }
    .grado-arancel-tipo {
        color: #34495e;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .grado-arancel-precio {
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }
    .grado-arancel-dojo,
    .grado-arancel-estado {
        color: #6b7280;
        font-size: 11px;
    }
</style>

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
