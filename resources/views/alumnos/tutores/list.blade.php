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
                <th style="text-align:center; width: 110px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td style="text-align:center;">{{ $item->tutor->ci }}</td>
                    <td>{{ optional($item->tutor)->first_name ?: 'Tutor no disponible' }}</td>
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
                        <a href="javascript:void(0);"
                            onclick="deleteItem('{{ route('alumno.tutores.destroy', ['id' => $item->id]) }}')"
                            data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger">
                            <i class="voyager-trash"></i>
                        </a>
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
