@extends('voyager::master')

@section('page_title', 'Ver Grado')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-bezier-curve"></i> Detalle del Grado
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            <a href="{{ route('voyager.grados.index') }}" class="btn btn-warning">
                                <i class="voyager-angle-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @php
        $gradoLabel = trim(($grado->tipo ?? '') . ' ' . ($grado->numero ?? '') . ' ' . ($grado->nombre ?? ''));
        $selectedDojo = $userDojoId ? $dojos->first() : null;
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-5">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-medal"></i> Información del grado
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Grado</label>
                            <p class="form-control-static"><strong>{{ $gradoLabel ?: 'Sin nombre' }}</strong></p>
                        </div>
                        <div class="form-group">
                            <label>Puntas</label>
                            <p class="form-control-static">{{ $grado->puntas }} punta(s)</p>
                        </div>
                        <div class="form-group">
                            <label>Dias</label>
                            <p class="form-control-static">{{ $grado->dias }} dia(s)</p>
                        </div>
                        <div class="form-group">
                            <label>Estado</label>
                            <p class="form-control-static">
                                @if ($grado->status == 1)
                                    <label class="label label-success">Activo</label>
                                @else
                                    <label class="label label-warning">Inactivo</label>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->hasPermission('read_grados'))
                    <div class="panel panel-bordered">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="fa-solid fa-money-bill"></i> Registrar arancel
                            </h3>
                        </div>
                        <form action="{{ route('grados.aranceles.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="grado_id" value="{{ $grado->id }}">

                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Dojo <span class="text-danger">*</span></label>
                                    @if($userDojoId)
                                        <input type="hidden" name="dojo_id" value="{{ $userDojoId }}">
                                        <p class="form-control-static">
                                            <label class="label label-info">{{ optional($selectedDojo)->nombre ?: 'Sucursal asignada' }}</label>
                                        </p>
                                    @else
                                        <select name="dojo_id" class="form-control select2" required>
                                            <option value="">Seleccione un dojo</option>
                                            @foreach($dojos as $dojo)
                                                <option value="{{ $dojo->id }}" {{ old('dojo_id') == $dojo->id ? 'selected' : '' }}>
                                                    {{ $dojo->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Tipo <span class="text-danger">*</span></label>
                                    <select name="tipo" class="form-control" required>
                                        <option value="Repaso" {{ old('tipo') === 'Repaso' ? 'selected' : '' }}>Repaso</option>
                                        <option value="Examen" {{ old('tipo') === 'Examen' ? 'selected' : '' }}>Examen</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Precio <span class="text-danger">*</span></label>
                                    <input type="number" name="precio" class="form-control" min="0" step="0.01" value="{{ old('precio') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>Observación</label>
                                    <textarea name="observacion" class="form-control" rows="3">{{ old('observacion') }}</textarea>
                                </div>
                            </div>
                            <div class="panel-footer text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="voyager-check"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-list"></i> Aranceles por dojo
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Dojo</th>
                                        <th style="width: 120px; text-align:center;">Tipo</th>
                                        <th style="width: 120px; text-align:right;">Precio</th>
                                        <th>Observación</th>
                                        @if(auth()->user()->hasPermission('read_grados'))
                                            <th style="width: 80px; text-align:center;">Acc.</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($aranceles as $arancel)
                                        <tr>
                                            <td style="vertical-align: middle;">{{ optional($arancel->dojo)->nombre ?: 'Sin dojo' }}</td>
                                            <td style="text-align:center; vertical-align: middle;">
                                                <label class="label {{ $arancel->tipo === 'Examen' ? 'label-primary' : 'label-info' }}">
                                                    {{ $arancel->tipo }}
                                                </label>
                                            </td>
                                            <td style="text-align:right; vertical-align: middle;">
                                                {{ number_format($arancel->precio, 2, '.', ',') }}
                                            </td>
                                            <td style="vertical-align: middle;">{{ $arancel->observacion ?: '—' }}</td>
                                            @if(auth()->user()->hasPermission('read_grados'))
                                                <td style="text-align:center; vertical-align: middle;">
                                                    <a href="#" onclick="deleteItem('{{ route('grados.aranceles.destroy', $arancel->id) }}')"
                                                       data-toggle="modal" data-target="#modal-delete"
                                                       class="btn btn-danger btn-xs" title="Eliminar">
                                                        <i class="voyager-trash"></i>
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->hasPermission('read_grados') ? 5 : 4 }}" class="text-center text-muted">
                                                Sin aranceles registrados para este grado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.modal-delete')
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script>
        function deleteItem(url) {
            $('#delete_form').attr('action', url);
        }
    </script>
@stop
