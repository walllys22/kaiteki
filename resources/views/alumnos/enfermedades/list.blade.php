<div class="table-responsive">
    <table id="dataTable" class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="text-align:center; width: 70px;">ID</th>
                <th>Enfermedad</th>
                <th>Medicamento</th>
                <th>Administración</th>
                <th>Observación</th>
                <th style="text-align:center; width: 110px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td style="text-align:center;">{{ $item->id }}</td>
                    <td>{{ $item->nombre ?: 'No registrada' }}</td>
                    <td>{{ $item->medicamento ?: 'No registrado' }}</td>
                    <td>{{ $item->administracion ?: 'No registrada' }}</td>
                    <td>{{ $item->observacion ?: 'Sin observación' }}</td>
                    <td class="text-center">
                        @if($alumnoActivo ?? true)
                        <a href="javascript:void(0);"
                            onclick="deleteItem('{{ route('alumno.enfermedade.destroy', ['id' => $item->id]) }}')"
                            data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger">
                            <i class="voyager-trash"></i>
                        </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty

                <tr>
                    <td colspan="6">
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
