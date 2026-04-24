@extends('voyager::master')

@section('page_title', 'Ver Historial de Alumno')


@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-file-pen"></i> Historial del Alumno
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            <a href="{{ route('voyager.alumnos.index') }}" class="btn btn-warning btn-sm">
                                <i class="voyager-list"></i> <span>Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('content')
    @include('partials.modal-delete')

    @php
        // Usamos $dataTypeContent que es la variable por defecto que pasa Voyager para el registro actual
        $alumno = $dataTypeContent;

        // Lógica de imagen similar a tu list.blade.php
        $image = asset('images/default.jpg');
        if ($alumno->person->image) {
            $image = asset('storage/' . str_replace('.avif', '', $alumno->person->image) . '-cropped.webp');
        }
    @endphp
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        {{-- Columna de Imagen --}}
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Foto del Alumno</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                @if ($alumno->person->image && Str::endsWith($alumno->person->image, ['.jpg', '.jpeg', '.png', '.webp', '.avif']))
                                    <img src="{{ $image }}"
                                        style="width:150%; max-width:150px; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                                @else
                                    <div
                                        style="width:150%; height:150px; background-color: #f8f9fa; display:flex; align-items:center; justify-content:center; border: 1px solid #ddd; border-radius: 5px; color:#999;">
                                        <i class="voyager-trophy" style="font-size: 50px;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Columna de Datos --}}
                        <div class="col-md-9">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Información General</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Nombre del Dojo</label>
                                        <p class="form-control-static">{{ $alumno->dojo->nombre }} </p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Nombre del Alumno</label>
                                        <p class="form-control-static">{{ $alumno->person->first_name }} </p>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label class="control-label" style="font-weight:bold; text-align:center;">Fecha
                                            Ingreso</label>
                                        <p class="form-control-static">
                                            {{ $alumno->fechaIngreso ? \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') : 'N/A' }}</p>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Estado</label>
                                        <p class="form-control-static">
                                            @if ($alumno->status == 1)
                                                <label class="label label-success">Activo</label>
                                            @else
                                                <label class="label label-warning">Inactivo</label>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-8 form-group">
                                        <label class="control-label" style="font-weight:bold;">Observaciones</label>
                                        <p class="form-control-static">{{ $alumno->observacion ?: 'Sin observaciones registradas.' }}</p>
                                    </div>
                                    <hr style="margin: 10px 0;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Sección de Historial --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-heading"
                            style="border-bottom:0; display: flex; align-items: center; justify-content: space-between;">
                            <h3 class="panel-title" style="margin: 0;">
                                <i class="fa-solid fa-file-pen"></i> Historial
                            </h3>
                            <div style="padding-right: 20px;">
                                @if (auth()->user()->hasPermission('add_alumnos'))
                                    <button class="btn btn-success btn-sm" data-toggle="modal"
                                        data-target="#modal-add-historial">
                                        <i class="voyager-plus"></i> <span>Agregar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="dataTables_length">
                                        <label>Mostrar <select id="select-paginate-historial" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                    </div>
                                </div>
                                <div class="col-sm-3" style="margin-bottom: 10px">
                                    <input type="text" id="input-search-historial" placeholder="🔍 Buscar..."
                                        class="form-control">
                                </div>
                            </div>
                            <div id="div-historial-list" style="color: black;">
                                <p class="text-center">Cargando historial...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Agregar Historial --}}
    <div class="modal fade" id="modal-add-historial" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('alumnos.historial.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Registro de Historial</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value=""> {{-- Campo ID oculto --}}
                        <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">

                        <div class="form-group">
                            <label for="grado_id">Grado</label>
                            <select name="grado_id" class="form-control select2" required>
                                <option value="">Seleccione un Grado</option>
                                @foreach($grado as $item)
                                    <option value="{{ $item->id }}">{{ $item->tipo }} {{ $item->numero }} {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select name="tipo" class="form-control" required>
                                <option value="Repaso">Repaso</option>
                                <option value="Examen">Examen</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="aprobo">¿Aprobó?</label>
                            <select name="aprobo" class="form-control" required>
                                <option value="Si">Si</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" name="fecha" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Ingrese las observaciones aquí..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script>
        var countPageHistorial = 10;
        var timeoutHistorial = null;

        $(document).ready(function() {
            listHistorial();

            // Búsqueda en tiempo real
            $('#input-search-historial').on('input', function() {
                clearTimeout(timeoutHistorial);
                timeoutHistorial = setTimeout(function() {
                    listHistorial();
                }, 1000);
            });

            // Búsqueda al presionar Enter
            $('#input-search-historial').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    clearTimeout(timeoutHistorial);
                    listHistorial();
                }
            });

            // Cambio de paginación
            $('#select-paginate-historial').change(function() {
                countPageHistorial = $(this).val();
                listHistorial();
            });
        });

        function listHistorial(page = 1) {
            $('#div-historial-list').loading({
                message: 'Cargando historial...'
            });
            let id = "{{ $alumno->id }}";
            let search = $('#input-search-historial').val() ? $('#input-search-historial').val() : '';
            let url = "{{ url('admin/alumnos') }}/" + id + "/historial/list";

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPageHistorial}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $("#div-historial-list").html(result);
                    $('#div-historial-list').loading('toggle');
                },
                error: function(err) {
                    $("#div-historial-list").html('<p class="text-center">No se pudieron cargar los registros del historial.</p>');
                    $('#div-historial-list').loading('toggle');
                }
            });
        }
    </script>
@stop
