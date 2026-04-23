@extends('voyager::master')

@section('page_title', 'Ver Alumnos')


@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-user-graduate"></i> Detalles del Alumno
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_alumnos'))
                                <a href="{{ route('voyager.alumnos.index') }}" class="btn btn-warning btn-sm">
                                    <i class="voyager-list"></i> <span>Volver</span>
                                </a>
                            @endif
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
                        {{-- Columna de Imagen/Poster --}}
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
                                <h3 class="panel-title">Información General </h3>
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
                                            {{ \Carbon\Carbon::parse($alumno->entry_date)->format('d/m/Y') }}</p>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Horario</label>
                                        <p class="form-control-static">{{ $alumno->horario->tipo }}
                                            {{ $alumno->horario->nombre }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Grado</label>
                                        <p class="form-control-static">{{ $alumno->grado->numero }}
                                            {{ $alumno->grado->tipo }} {{ $alumno->grado->nombre }} </p>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label class="control-label" style="font-weight:bold;">Estado</label>
                                        <p class="form-control-static">
                                            @if ($alumno->status == 1)
                                                <label class="label label-success">Activo</label>
                                            @else
                                                <label class="label label-warning">Inactivo</label>
                                            @endif
                                        </p>
                                    </div>
                                    <hr style="margin: 10px 0;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sección de Parentesco --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-heading"
                            style="border-bottom:0; display: flex; align-items: center; justify-content: space-between;">
                            <h3 class="panel-title" style="margin: 0;">
                                <i class="fa-solid fa-people-pulling"></i> Padres o Tutores
                            </h3>
                            <div style="padding-right: 20px;">
                                @if (auth()->user()->hasPermission('add_alumnos'))
                                    <button class="btn btn-success btn-sm" data-toggle="modal"
                                        data-target="#modal-add-tutor">
                                        <i class="voyager-plus"></i> <span>Agregar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="dataTables_length">
                                        <label>Mostrar <select id="select-paginate-tutor" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                    </div>
                                </div>
                                <div class="col-sm-3" style="margin-bottom: 10px">
                                    <input type="text" id="input-search-tutor" placeholder="🔍 Buscar..."
                                        class="form-control">
                                </div>
                            </div>
                            <div id="div-tutores-list">
                                <p class="text-center">Cargando tutores...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Sección de Enfermedad del Alumno --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-heading"
                            style="border-bottom:0; display: flex; align-items: center; justify-content: space-between;">
                            <h3 class="panel-title" style="margin: 0;">
                                <i class="fa-solid fa-briefcase-medical"></i> Enfermedad del Alumno
                            </h3>
                            <div style="padding-right: 20px;">
                                <button class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#modal-add-enfermedad">
                                    <i class="voyager-plus"></i> <span>Agregar</span>
                                </button>
                            </div>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="dataTables_length">
                                        <label>Mostrar <select id="select-paginate-enfer" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                    </div>
                                </div>
                                <div class="col-sm-3" style="margin-bottom: 10px">
                                    <input type="text" id="input-search-enfermedad" placeholder="🔍 Buscar..."
                                        class="form-control">
                                </div>
                            </div>
                            <div id="div-enfermedad-list">
                                <p class="text-center">Cargando enfermedad...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal para agregar tutores --}}
        <form id="form-add-tutor" class="form-edit-add" action="{{ route('alumno.tutores.store') }}" method="POST">
            @csrf
            <div class="modal fade" tabindex="-1" id="modal-add-tutor" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Tutor</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                            {{-- lista el tutor --}}
                            <div class="form-group col-md-6">
                                <label for="tutor_id">Nombre del Tutor</label>
                                <select name="tutor_id" class="form-control select2" required>
                                    <option value="">Seleccione una persona</option>
                                    @foreach ($people as $person)
                                        <option value="{{ $person->id }}"
                                            {{ old('tutor_id') == $person->id ? 'selected' : '' }}>
                                            {{ $person->first_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- lista el parentesco --}}
                            <div class="form-group col-md-6">
                                <label for="pariente_id">Parentesco</label>
                                <select name="pariente_id" class="form-control select2" required>
                                    <option value="">Seleccione un Parentesco</option>
                                    @foreach ($parientes as $pariente)
                                        <option value="{{ $pariente->id }}"
                                            {{ old('pariente_id') == $pariente->id ? 'selected' : '' }}>
                                            {{ $pariente->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="address">Obervaciones</label>
                                <textarea name="observacion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default btn-cancel"
                                data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-submit">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        {{-- Modal para agregar Enfermedad del Alumno --}}
        <form id="form-add-enfermedad" class="form-edit-add" action="{{ route('alumno.enfermedade.store') }}"
            method="POST">
            @csrf
            <div class="modal fade" tabindex="-1" id="modal-add-enfermedad" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Enfermedad</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">

                            <div class="form-group col-md-12">
                                <label for="nombre">Nombre de la Enfermedad</label>
                                <input type="text" class="form-control" name="enfermedad" placeholder="enfermedad"
                                    value="{{ old('enfermedad', $dataTypeContent->enfermedad) }}">
                            </div>

                            <div class="form-group col-md-12">
                                <label for="nombre">Nombre del Medicamento</label>
                                <input type="text" class="form-control" name="medicamento" placeholder="Medicamento"
                                    value="{{ old('medicamento', $dataTypeContent->medicamento) }}">
                            </div>


                            <div class="form-group col-md-12">
                                <label for="nombre">Administración del Medicamento</label>
                                <input type="text" class="form-control" name="dosis"
                                    placeholder="Administración del Medicamento"
                                    value="{{ old('dosis', $dataTypeContent->dosis) }}">
                            </div>

                            <div class="form-group col-md-12">
                                <label for="address">Obervaciones</label>
                                <textarea name="observaciones" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default btn-cancel"
                                data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-submit">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @include('partials.modal-delete')

    @stop

    @section('css')
        <style>
            /* Asegura que el select ocupe todo el ancho y el buscador sea visible en el modal */
            .select2-container {
                width: 100% !important;
            }

            /* Evita conflictos de profundidad (z-index) con el modal de Bootstrap */
            .select2-dropdown {
                z-index: 10001;
            }
        </style>
    @stop

    @section('javascript')
        <script src="{{ asset('js/btn-submit.js') }}"></script>
        <script>
            var countPageTutor = 10;
            var timeoutTutor = null;
            var countPageEnfer = 10;
            var timeoutEnfer = null;

            $(document).ready(function() {
                // Inicializar Select2 específicamente cuando el modal se muestra
                $('#modal-add-tutor').on('shown.bs.modal', function() {
                    $(this).find('.select2').select2({
                        dropdownParent: $('#modal-add-tutor'),
                        width: '100%'
                    });
                });

                listTutores();
                enfermedadList();

                // Eventos para Tutores
                $('#input-search-tutor').on('keyup', function(e) {
                    if (e.keyCode == 13) {
                        clearTimeout(timeoutTutor);
                        listTutores();
                    }
                });

                $('#input-search-enfermedad').on('keyup', function(e) {
                    if (e.keyCode == 13) {
                        clearTimeout(timeoutEnfer);
                        enfermedadList();
                    }
                });

                $('#select-paginate-tutor').change(function() {
                    countPageTutor = $(this).val();
                    listTutores();
                });

                $('#select-paginate-enfer').change(function() {
                    countPageEnfer = $(this).val();
                    enfermedadList();
                });


                $('#input-search-tutor').on('input', function() {
                    clearTimeout(timeoutTutor);
                    timeoutTutor = setTimeout(function() {
                        listTutores();
                    }, 1000);
                });

                $('#input-search-enfermedad').on('input', function() {
                    clearTimeout(timeoutEnfer);
                    timeoutEnfer = setTimeout(function() {
                        enfermedadList();
                    }, 1000);
                });


            });

            function listTutores(page = 1) {
                $('#div-tutores-list').loading({
                    message: 'Cargando...'
                });
                let id = "{{ $alumno->id }}";
                let search = $('#input-search-tutor').val() ? $('#input-search-tutor').val() : '';
                let url = "{{ url('admin/alumnos') }}/" + id + "/parentesco/list";

                $.ajax({
                    url: `${url}?search=${search}&paginate=${countPageTutor}&page=${page}`,
                    type: 'get',
                    success: function(result) {
                        $("#div-tutores-list").html(result);
                        $('#div-tutores-list').loading('toggle');
                    },
                    error: function(err) {
                        $("#div-tutores-list").html(
                            '<p class="text-center">No se pudieron cargar los tutoresss.</p>');
                        $('#div-tutores-list').loading('toggle');
                    }
                });
            }

            function deleteItem(url) {
                $('#delete_form').attr('action', url);
            }


            function enfermedadList(page = 1) {
                $('#div-enfermedad-list').loading({
                    message: 'Cargando...'
                });
                let id = "{{ $alumno->id }}";
                let search = $('#input-search-enfermedad').val() ? $('#input-search-enfermedad').val() : '';
                let url = "{{ url('admin/alumnos') }}/" + id + "/enfermedade/list";

                $.ajax({
                    url: `${url}?search=${search}&paginate=${countPageEnfer}&page=${page}`,
                    type: 'get',
                    success: function(result) {
                        $("#div-enfermedad-list").html(result);
                        $('#div-enfermedad-list').loading('toggle');
                    },
                    error: function(err) {
                        $("#div-enfermedad-list").html(
                            '<p class="text-center">No se pudieron cargar los datos de la enfermedad.</p>');
                        $('#div-enfermedad-list').loading('toggle');
                    }
                });
            }
        </script>
    @stop
