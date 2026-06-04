<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="text-align:center; width: 70px;">CI/Nit</th>
                <th style="width:50px;"></th>
                <th>Tutor</th>
                <th>Parentesco</th>
                <th>Telefono</th>
                <th>Dirección</th>
                <th style="text-align:center; width: 130px;">Estado</th>
                <th style="text-align:center; width: 160px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td style="text-align:center; vertical-align:middle;">{{ $item->tutor->ci }}</td>
                    <td style="text-align:center; vertical-align:middle; padding:6px 4px;">
                        @php
                            $tutorImg = asset('images/default.jpg');
                            if(optional($item->tutor)->image){
                                $tutorImg = asset('storage/' . str_replace('.avif', '', $item->tutor->image) . '-cropped.webp');
                            }
                        @endphp
                        <img src="{{ $tutorImg }}" alt="{{ optional($item->tutor)->first_name }}"
                             style="width:38px; height:38px; border-radius:50%; object-fit:cover; border:1px solid #ddd;">
                    </td>
                    <td style="vertical-align:middle;">{{ optional($item->tutor)->first_name ?: 'Tutor no disponible' }}</td>
                    <td>{{ optional($item->pariente)->nombre ?: 'No registrado' }}</td>
                    <td>{{ $item->tutor->phone }}</td>
                    <td>{{ $item->tutor->address}}</td>
                    <td style="text-align:center;">
                        <span
                            class="label {{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'label-success' : 'label-default' }}">
                            {{ (string) $item->status === '1' || $item->status === 1 || $item->status === null ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php
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
                        @endif
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
                    <td colspan="8">
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
