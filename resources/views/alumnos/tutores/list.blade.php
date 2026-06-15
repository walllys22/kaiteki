<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="text-align:center; width: 70px;">CI/Nit</th>
                <th>Tutor</th>
                <th>Parentesco</th>
                <th>Telefono</th>
                <th>Dirección</th>
                <th style="text-align:center; width: 130px;">Estado</th>
                <th style="text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td style="text-align:center; vertical-align:middle;">{{ $item->tutor->ci }}</td>
                    <td style="vertical-align:middle;">
                        @php
                            $tutorImg = asset('images/default.jpg');
                            if(optional($item->tutor)->image){
                                $tutorImg = asset('storage/' . str_replace('.avif', '', $item->tutor->image) . '-cropped.webp');
                            }
                        @endphp
                        <div style="display:flex; align-items:center;">
                            <img src="{{ $tutorImg }}" alt="{{ optional($item->tutor)->first_name }}"
                                 class="image-expandable"
                                 style="width:60px; height:60px; border-radius:30px; margin-right:10px; object-fit:cover;">
                            <span>{{ optional($item->tutor)->first_name ?: 'Tutor no disponible' }}</span>
                        </div>
                    </td>
                    <td>{{ optional($item->pariente)->nombre ?: 'No registrado' }}</td>
                    <td>{{ $item->tutor->phone }}</td>
                    <td>{{ $item->tutor->address}}</td>
                    <td style="text-align:center;">
                        <span
                            class="label {{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'label-success' : 'label-default' }}">
                            {{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="no-sort no-click bread-actions text-center">
                        <a href="javascript:void(0);"
                            class="btn btn-sm btn-info btn-ver-tutor"
                            title="Ver información"
                            data-img="{{ $tutorImg }}"
                            data-nombre="{{ optional($item->tutor)->first_name ?: 'Tutor no disponible' }}"
                            data-documenttype="{{ optional($item->tutor)->documentType ?: 'CI' }}"
                            data-ci="{{ optional($item->tutor)->ci ?: '-' }}"
                            data-parentesco="{{ optional($item->pariente)->nombre ?: 'No registrado' }}"
                            data-nacimiento="{{ optional($item->tutor)->birth_date ? \Carbon\Carbon::parse($item->tutor->birth_date)->format('d/m/Y') : '-' }}"
                            data-genero="{{ optional($item->tutor)->gender ?: '-' }}"
                            data-sangre="{{ optional($item->tutor)->sangre ?: '-' }}"
                            data-telefono="{{ trim((optional($item->tutor)->country_code ?? '') . ' ' . (optional($item->tutor)->phone ?? '')) ?: '-' }}"
                            data-email="{{ optional($item->tutor)->email ?: '-' }}"
                            data-direccion="{{ optional($item->tutor)->address ?: '-' }}"
                            data-estado="{{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'Activo' : 'Inactivo' }}">
                            <i class="voyager-eye"></i>
                        </a>
                        {{-- @php
                            $isActive = (string) $item->status === '1' || $item->status === 1 || $item->status === null;
                        @endphp
                        @if (auth()->user()->hasPermission('edit_alumnos') && ($alumnoActivo ?? true))
                            <form action="{{ route('alumno.tutores.status.update', ['id' => $item->id]) }}" method="POST" style="display:inline-block; margin:0;">
                                @csrf
                                <button type="submit"
                                    class="btn btn-sm {{ $isActive ? 'btn-warning' : 'btn-success' }}"
                                    title="{{ $isActive ? 'Desactivar' : 'Activar' }}">
                                    <i class="{{ $isActive ? 'voyager-x' : 'voyager-check' }}"></i>
                                </button>
                            </form>
                        @endif --}}
                        @if($alumnoActivo ?? true)
                        <a href="javascript:void(0);"
                            onclick="deleteItem('{{ route('alumno.tutores.destroy', ['id' => $item->id]) }}')"
                            data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger">
                            <i class="voyager-trash"></i>
                        </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <h5 class="text-center" style="margin-top: 50px">
                            <img src="{{ asset('images/empty.png') }}" width="120px" alt=""
                                style="opacity: 0.8">
                            <br><br>
                            No hay resultados
                        </h5>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($data->count())
    <div class="row">
        <div class="col-md-6">
            <p class="text-muted">Mostrando del {{ $data->firstItem() }} al {{ $data->lastItem() }} de
                {{ $data->total() }} registros.</p>
        </div>
        <div class="col-md-6 text-right">
            {{ $data->links() }}
        </div>
    </div>
@endif

<div class="modal fade" id="modal-ver-tutor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="voyager-person"></i> Información del Tutor</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="vt-img" src="" alt="Tutor"
                            style="width:160px; height:160px; border-radius:8px; object-fit:cover; border:1px solid #eee;">
                        <h4 id="vt-nombre" style="margin-top:12px; margin-bottom:4px;"></h4>
                        <span id="vt-estado" class="label label-success"></span>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered" style="margin-bottom:0;">
                            <tbody>
                                <tr><th style="width:40%;">Nombre</th><td id="vt-nombre-row"></td></tr>
                                <tr><th>Documento</th><td><span id="vt-documenttype"></span> <strong id="vt-ci"></strong></td></tr>
                                <tr><th>Parentesco</th><td id="vt-parentesco"></td></tr>
                                <tr><th>Fecha de Nacimiento</th><td id="vt-nacimiento"></td></tr>
                                <tr><th>Género</th><td id="vt-genero"></td></tr>
                                <tr><th>Tipo de Sangre</th><td id="vt-sangre"></td></tr>
                                <tr><th>Teléfono</th><td id="vt-telefono"></td></tr>
                                <tr><th>Email</th><td id="vt-email"></td></tr>
                                <tr><th>Dirección</th><td id="vt-direccion"></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).off('click', '.btn-ver-tutor').on('click', '.btn-ver-tutor', function () {
        var d = $(this).data();
        $('#vt-img').attr('src', d.img);
        $('#vt-nombre').text(d.nombre);
        $('#vt-nombre-row').text(d.nombre);
        $('#vt-documenttype').text(d.documenttype);
        $('#vt-ci').text(d.ci);
        $('#vt-parentesco').text(d.parentesco);
        $('#vt-nacimiento').text(d.nacimiento);
        $('#vt-genero').text(d.genero);
        $('#vt-sangre').text(d.sangre);
        $('#vt-telefono').text(d.telefono);
        $('#vt-email').text(d.email);
        $('#vt-direccion').text(d.direccion);
        var estado = $('#vt-estado');
        estado.text(d.estado);
        estado.attr('class', 'label ' + (d.estado === 'Activo' ? 'label-success' : 'label-default'));
        $('#modal-ver-tutor').modal('show');
    });
</script>
