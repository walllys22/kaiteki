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
                    <th>Horario</th>
                    <th>Responsables</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    @php
                        $previousResponsible = $item->responsibles->first(function ($responsible) {
                            return (int) $responsible->status !== 1 && $responsible->person;
                        });
                    @endphp
                    <tr>
                        <td style="text-align: center; vertical-align: middle;">
                            {{ $item->id }}
                        </td>
                        @if ($showDojoColumn)
                            <td style="text-align: center; vertical-align: middle;">
                                @if ($item->dojo)
                                    <label class="label label-info">{{ $item->dojo->nombre }}</label>
                                @else
                                    <span class="text-muted">Sin sucursal</span>
                                @endif
                            </td>
                        @endif
                        <td style="vertical-align: middle;">
                            <strong>{{ $item->nombre ?: 'Sin nombre' }}</strong>
                            <br>
                            <small class="text-muted">Turno: {{ $item->tipo ?: 'Sin turno' }}</small>
                            <br>
                            <small class="text-muted">Historial: {{ $item->responsibles_count }} registro(s)</small>
                        </td>
                        <td style="vertical-align: middle;">
                            @if ($item->activeResponsible && $item->activeResponsible->person)
                                <label class="label label-success">{{ $item->activeResponsible->person->first_name }}</label>
                            @else
                                <span class="text-muted">Sin responsable activo</span>
                            @endif

                            @if ($previousResponsible)
                                <div style="margin-top: 8px;">
                                    <small class="text-muted" style="display: block; margin-bottom: 4px;">Responsable anterior:</small>
                                    <div style="margin-bottom: 4px;">
                                        <label class="label label-default" style="margin-bottom: 2px;">{{ $previousResponsible->person->first_name }}</label>
                                        <br>
                                        <small class="text-muted">
                                            {{ optional($previousResponsible->created_at)->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if ($item->status == 1)
                                <label class="label label-success">Activo</label>
                            @else
                                <label class="label label-warning">Inactivo</label>
                            @endif
                        </td>
                        <td style="vertical-align: middle;" class="no-sort no-click bread-actions text-right">
                            @if (auth()->user()->hasPermission('read_horarios'))
                                <a href="{{ route('voyager.horarios.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                    <i class="voyager-eye"></i>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('edit_horarios'))
                                <a href="{{ route('voyager.horarios.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                    <i class="voyager-edit"></i>
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
                        <td colspan="{{ $showDojoColumn ? 6 : 5 }}">
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
