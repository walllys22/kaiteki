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
        $canManageAranceles = auth()->user()->hasPermission('read_grados');
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered grado-summary-panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-5">
                                <span class="grado-kicker">Grado</span>
                                <h2 class="grado-title">{{ $gradoLabel ?: 'Sin nombre' }}</h2>
                            </div>
                            <div class="col-sm-2">
                                <span class="grado-kicker">Puntas</span>
                                <div class="grado-stat">{{ $grado->puntas }}</div>
                            </div>
                            <div class="col-sm-2">
                                <span class="grado-kicker">Dias</span>
                                <div class="grado-stat">{{ $grado->dias }}</div>
                            </div>
                            <div class="col-sm-3">
                                <span class="grado-kicker">Estado</span>
                                <div>
                                    @if ($grado->status == 1)
                                        <label class="label label-success grado-label">Activo</label>
                                    @else
                                        <label class="label label-warning grado-label">Inactivo</label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                @if($canManageAranceles)
                    <div class="panel panel-bordered">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="fa-solid fa-money-bill"></i> Nuevo arancel
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
                                        <p class="form-control-static dojo-selected">
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ optional($selectedDojo)->nombre ?: 'Sucursal asignada' }}
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
                                    <div class="input-group">
                                        <span class="input-group-addon">Bs</span>
                                        <input type="number" name="precio" class="form-control" min="0" step="0.01" value="{{ old('precio') }}" required>
                                    </div>
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

            <div class="col-md-8">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-list"></i> Aranceles por dojo
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Dojo</th>
                                        <th style="width: 120px; text-align:center;">Tipo</th>
                                        <th style="width: 120px; text-align:right;">Precio</th>
                                        <th style="width: 120px; text-align:center;">Estado</th>
                                        @if($canManageAranceles)
                                            <th style="width: 95px; text-align:center;">Acc.</th>
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
                                            <td style="text-align:center; vertical-align: middle;">
                                                @if ($arancel->status == 1)
                                                    <label class="label label-success">Activo</label>
                                                @else
                                                    <label class="label label-warning">Inactivo</label>
                                                @endif
                                            </td>
                                            @if($canManageAranceles)
                                                <td style="text-align:center; vertical-align: middle;">
                                                    <button type="button"
                                                            class="btn btn-primary btn-xs"
                                                            title="Editar precio"
                                                            data-toggle="modal"
                                                            data-target="#modal-edit-precio"
                                                            data-url="{{ route('grados.aranceles.precio.update', $arancel->id) }}"
                                                            data-dojo="{{ optional($arancel->dojo)->nombre ?: 'Sin dojo' }}"
                                                            data-tipo="{{ $arancel->tipo }}"
                                                            data-precio="{{ $arancel->precio }}">
                                                        <i class="voyager-edit"></i>
                                                    </button>
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
                                            <td colspan="{{ $canManageAranceles ? 5 : 4 }}" class="text-center text-muted">
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

    <form id="form-edit-precio" action="#" method="POST">
        @csrf
        @method('PUT')
        <div class="modal fade" id="modal-edit-precio" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="voyager-edit"></i> Editar precio
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="price-context">
                            <div>
                                <span>Dojo</span>
                                <strong id="edit-precio-dojo">-</strong>
                            </div>
                            <div>
                                <span>Tipo</span>
                                <strong id="edit-precio-tipo">-</strong>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">Bs</span>
                                <input type="number" name="precio" id="edit-precio-input" class="form-control" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="voyager-check"></i> Guardar precio
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @include('partials.modal-delete')
@stop

@section('css')
    <style>
        .grado-summary-panel .panel-body {
            padding: 22px 24px;
        }

        .grado-kicker {
            color: #7a869a;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .grado-title {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
        }

        .grado-stat {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.25;
        }

        .grado-label {
            display: inline-block;
            font-size: 12px;
            margin-top: 6px;
            padding: 6px 10px;
        }

        .dojo-selected {
            background: #f5f8fb;
            border: 1px solid #e4eaef;
            border-radius: 4px;
            margin: 0;
            padding: 8px 10px;
        }

        .table > tbody > tr > td,
        .table > thead > tr > th {
            vertical-align: middle;
        }

        .price-context {
            background: #f5f8fb;
            border: 1px solid #e4eaef;
            border-radius: 4px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
            padding: 12px;
        }

        .price-context span {
            color: #7a869a;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .price-context strong {
            display: block;
        }
    </style>
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script>
        function deleteItem(url) {
            $('#delete_form').attr('action', url);
        }

        $('#modal-edit-precio').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);

            $('#form-edit-precio').attr('action', button.data('url'));
            $('#edit-precio-dojo').text(button.data('dojo'));
            $('#edit-precio-tipo').text(button.data('tipo'));
            $('#edit-precio-input').val(button.data('precio'));
        });
    </script>
@stop
