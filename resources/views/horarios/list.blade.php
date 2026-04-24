@php
    $showDojoColumn = !auth()->user()->dojo_id;
@endphp

<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    @if ($showDojoColumn)
                        <th style="text-align: center">Dojo</th>
                    @endif
                    <th style="text-align: center">Turno</th>
                    <th style="text-align: center">Nombre</th>
                    <th style="text-align: center">Responsable Actual</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td style="text-align: center">{{ $item->id }}</td>
                        @if ($showDojoColumn)
                            <td style="text-align: center">{{ $item->dojo->nombre ?? 'Sin sucursal' }}</td>
                        @endif
                        <td style="text-align: center">{{ $item->tipo ?? 'Sin turno' }}</td>
                        <td>
                            {{ $item->nombre ?? 'Sin nombre' }}
                            @if ($item->responsibles_count)
                                <br>
                                <small class="text-muted">Historial: {{ $item->responsibles_count }}</small>
                            @endif
                        </td>
                        <td style="text-align: center">
                            @if ($item->activeResponsible && $item->activeResponsible->person)
                                <span class="label label-info">{{ $item->activeResponsible->person->first_name }}</span>
                            @else
                                <span class="text-muted">Sin responsable</span>
                            @endif

                            @php
                                $inactiveResponsibles = $item->responsibles->filter(function ($responsible) {
                                    return (int) $responsible->status !== 1 && $responsible->person;
                                });
                            @endphp

                            @if ($inactiveResponsibles->isNotEmpty())
                                <div style="margin-top: 8px; text-align: left;">
                                    <small class="text-muted" style="display: block; margin-bottom: 4px;">Historial anterior:</small>
                                    @foreach ($inactiveResponsibles as $responsible)
                                        <div style="margin-bottom: 4px;">
                                            <span class="label label-default">{{ $responsible->person->first_name }}</span>
                                            <small class="text-muted">
                                                {{ optional($responsible->created_at)->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center">
                            @if ($item->status == 1)
                                <label class="label label-success">Activo</label>
                            @else
                                <label class="label label-warning">Inactivo</label>
                            @endif
                        </td>
                        <td style="width: 18%" class="no-sort no-click bread-actions text-right">
                            @if (auth()->user()->hasPermission('read_horarios'))
                                <a href="{{ route('voyager.horarios.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                    <i class="voyager-eye"></i>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('edit_horarios'))
                                <a href="{{ route('voyager.horarios.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                    <i class="voyager-edit"></i>
                                </a>
                                <a href="#" onclick="openResponsableModal('{{ $item->id }}', '{{ addslashes($item->tipo . ' ' . ($item->nombre ?? '')) }}', '{{ $item->dojo_id }}', '{{ addslashes($item->dojo->nombre ?? 'Sin sucursal') }}')" title="Agregar responsable" class="btn btn-sm btn-dark">
                                    <i class="voyager-people"></i>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete_horarios'))
                                <a href="#" onclick="deleteItem('{{ route('voyager.horarios.destroy', ['id' => $item->id]) }}')" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete">
                                    <i class="voyager-trash"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showDojoColumn ? 7 : 6 }}">
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
        @if(count($data) > 0)
            <p class="text-muted">Mostrando del {{ $data->firstItem() }} al {{ $data->lastItem() }} de {{ $data->total() }} registros.</p>
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
            if (link) {
                page = link.split('=')[1];
                list(page);
            }
        });
    });
</script>
