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
        $alumno = $dataTypeContent;
        $person = $alumno->person;
        $dojoData = $alumno->dojo;
        $image = asset('images/default.jpg');
        if (optional($person)->image) {
            $image = asset('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp');
        }
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered alumno-summary-panel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="alumno-photo-card">
                                    <div class="alumno-photo-title">Foto del Alumno</div>
                                    <div class="alumno-photo-frame">
                                        @if (optional($person)->image && \Illuminate\Support\Str::endsWith($person->image, ['.jpg', '.jpeg', '.png', '.webp', '.avif']))
                                            <img src="{{ $image }}" alt="{{ optional($person)->first_name ?: 'Alumno' }}" class="alumno-photo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="alumno-photo-fallback" style="display:none;">
                                                <i class="fa-solid fa-user-graduate"></i>
                                            </div>
                                        @else
                                            <div class="alumno-photo-fallback">
                                                <i class="fa-solid fa-user-graduate"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-10">
                                <div class="alumno-info-header">
                                    <div>
                                        <div class="alumno-label">Alumno</div>
                                        <h3 class="alumno-name">{{ optional($person)->first_name ?: 'Persona no disponible' }}</h3>
                                    </div>
                                    <div class="alumno-status-wrap">
                                        @if ($alumno->status == 1)
                                            <span class="label label-success alumno-status-label">Activo</span>
                                        @else
                                            <span class="label label-warning alumno-status-label">Inactivo</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Dojo</div>
                                            <div class="alumno-value">{{ optional($dojoData)->nombre ?: 'Sin dojo asignado' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Fecha de Ingreso</div>
                                            <div class="alumno-value">{{ $alumno->fechaIngreso ? \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') : 'No registrada' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Documento</div>
                                            <div class="alumno-value">{{ optional($person)->documentType ?: 'N/A' }}: {{ optional($person)->ci ?: 'No registrado' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Género</div>
                                            <div class="alumno-value">{{ optional($person)->gender ?: 'No registrado' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Fecha de Nacimiento</div>
                                            <div class="alumno-value">{{ optional($person)->birth_date ? \Carbon\Carbon::parse($person->birth_date)->format('d/m/Y') : 'No registrada' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Teléfono</div>
                                            <div class="alumno-value">
                                                @if (optional($person)->phone)
                                                    +{{ $person->country_code ?: '591' }} {{ $person->phone }}
                                                @else
                                                    No registrado
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Correo</div>
                                            <div class="alumno-value">{{ optional($person)->email ?: 'No registrado' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Dirección</div>
                                            <div class="alumno-value">{{ optional($person)->address ?: 'No registrada' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alumno-data-card">
                                            <div class="alumno-label">Observaciones</div>
                                            <div class="alumno-value">{{ $alumno->observacion ?: 'Sin observaciones registradas.' }}</div>
                                        </div>
                                    </div>
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
            .alumno-summary-panel .panel-body {
                padding: 18px;
            }

            .alumno-photo-card {
                margin-bottom: 15px;
            }

            .alumno-photo-title {
                color: #7a8a9a;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .04em;
                margin-bottom: 10px;
                text-transform: uppercase;
            }

            .alumno-photo-frame {
                align-items: center;
                background: linear-gradient(180deg, #f9fbfd 0%, #f3f6f9 100%);
                border: 1px solid #e6edf3;
                border-radius: 10px;
                display: flex;
                height: 165px;
                justify-content: center;
                overflow: hidden;
                width: 100%;
            }

            .alumno-photo-img {
                display: block;
                height: 100%;
                object-fit: cover;
                width: 100%;
            }

            .alumno-photo-fallback {
                align-items: center;
                color: #9aa7b4;
                display: flex;
                font-size: 64px;
                height: 100%;
                justify-content: center;
                width: 100%;
            }

            .alumno-info-header {
                align-items: flex-start;
                border-bottom: 1px solid #edf2f7;
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
                padding-bottom: 12px;
            }

            .alumno-name {
                color: #1f2d3d;
                font-size: 22px;
                font-weight: 700;
                margin: 4px 0 0;
            }

            .alumno-label {
                color: #7a8a9a;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .04em;
                margin-bottom: 6px;
                text-transform: uppercase;
            }

            .alumno-value {
                color: #253443;
                font-size: 15px;
                line-height: 1.5;
            }

            .alumno-data-card,
            .alumno-note-card {
                background-color: #fbfcfe;
                border: 1px solid #edf2f7;
                border-radius: 8px;
                margin-bottom: 12px;
                min-height: 70px;
                padding: 12px 14px;
            }

            .alumno-note-card {
                min-height: auto;
            }

            .alumno-status-label {
                font-size: 12px;
                padding: 6px 10px;
            }

            /* Asegura que el select ocupe todo el ancho y el buscador sea visible en el modal */
            .select2-container {
                width: 100% !important;
            }

            /* Evita conflictos de profundidad (z-index) con el modal de Bootstrap */
            .select2-dropdown {
                z-index: 10001;
            }

            @media (max-width: 991px) {
                .alumno-info-header {
                    display: block;
                }

                .alumno-status-wrap {
                    margin-top: 10px;
                }
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
