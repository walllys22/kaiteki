@extends('voyager::master')

@section('page_title', 'Ver Alumnos')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0;">
                        <div class="col-md-6" style="padding: 0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-user-graduate"></i> Detalles del Alumno
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px;">
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
                                        <span class="label {{ $alumno->status == 1 ? 'label-success' : 'label-warning' }} alumno-status-label">
                                            {{ $alumno->status == 1 ? 'Activo' : 'Inactivo' }}
                                        </span>
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

            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading" style="padding-bottom: 0;">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-folder-open"></i> Historial del Alumno
                        </h3>
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-tabs alumno-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab-grados" aria-controls="tab-grados" role="tab" data-toggle="tab">
                                    <i class="fa-solid fa-award"></i> Grados
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-enfermedades" aria-controls="tab-enfermedades" role="tab" data-toggle="tab">
                                    <i class="fa-solid fa-briefcase-medical"></i> Enfermedades
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab-tutores" aria-controls="tab-tutores" role="tab" data-toggle="tab">
                                    <i class="fa-solid fa-people-pulling"></i> Tutores
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content alumno-tab-content">
                            <div role="tabpanel" class="tab-pane active" id="tab-grados">
                                <div class="row alumno-tab-toolbar">
                                    <div class="col-sm-7">
                                        <div class="dataTables_length">
                                            <label>Mostrar <select id="select-paginate-grado" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 text-right" style="margin-bottom: 10px;">
                                        <button id="btn-add-grado" class="btn btn-success btn-sm" style="display:none;" data-toggle="modal" data-target="#modal-add-grado">
                                            <i class="voyager-plus"></i> Nuevo Grado
                                        </button>
                                    </div>
                                    <div class="col-sm-2" style="margin-bottom: 10px;">
                                        <input type="text" id="input-search-grado" placeholder="🔍 Buscar..." class="form-control">
                                    </div>
                                </div>
                                <div id="div-grados-list">
                                    <p class="text-center">Cargando grados...</p>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab-enfermedades">
                                <div class="row alumno-tab-toolbar">
                                    <div class="col-sm-8">
                                        <div class="dataTables_length">
                                            <label>Mostrar <select id="select-paginate-enfer" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 text-right" style="margin-bottom: 10px;">
                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-add-enfermedad">
                                            <i class="voyager-plus"></i> Agregar
                                        </button>
                                    </div>
                                    <div class="col-sm-2" style="margin-bottom: 10px;">
                                        <input type="text" id="input-search-enfermedad" placeholder="🔍 Buscar..." class="form-control">
                                    </div>
                                </div>
                                <div id="div-enfermedad-list">
                                    <p class="text-center">Cargando enfermedades...</p>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab-tutores">
                                <div class="row alumno-tab-toolbar">
                                    <div class="col-sm-8">
                                        <div class="dataTables_length">
                                            <label>Mostrar <select id="select-paginate-tutor" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 text-right" style="margin-bottom: 10px;">
                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-add-tutor">
                                            <i class="voyager-plus"></i> Agregar
                                        </button>
                                    </div>
                                    <div class="col-sm-2" style="margin-bottom: 10px;">
                                        <input type="text" id="input-search-tutor" placeholder="🔍 Buscar..." class="form-control">
                                    </div>
                                </div>
                                <div id="div-tutores-list">
                                    <p class="text-center">Cargando tutores...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="form-add-tutor" class="form-edit-add" action="{{ route('alumno.tutores.store') }}" method="POST">
            @csrf
            <div class="modal fade" tabindex="-1" id="modal-add-tutor" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Tutor</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                            <div class="form-group col-md-6">
                                <label for="person_id">Nombre del Tutor</label>
                                <select name="person_id" class="form-control select2" required>
                                    <option value="">Seleccione una persona</option>
                                    @foreach ($people as $person)
                                        @if ((int) $person->id !== (int) $alumno->person_id)
                                            <option value="{{ $person->id }}">{{ $person->first_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="text-muted">El alumno actual no aparece en esta lista.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="parentesco_id">Parentesco</label>
                                <select name="parentesco_id" class="form-control select2" required>
                                    <option value="">Seleccione un Parentesco</option>
                                    @foreach ($parientes as $pariente)
                                        <option value="{{ $pariente->id }}">{{ $pariente->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="observacion">Observaciones</label>
                                <textarea name="observacion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-submit">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Modal: Nuevo Grado --}}
        <form id="form-add-grado" class="form-edit-add" action="{{ route('alumno.grado.store') }}" method="POST">
            @csrf
            <div class="modal fade" tabindex="-1" id="modal-add-grado" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            <h4 class="modal-title"><i class="fa-solid fa-award"></i> Registrar Nuevo Grado</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                            <div class="form-group col-md-8">
                                <label>Grado <span class="text-danger">*</span></label>
                                <select name="grado_id" class="form-control select2" required>
                                    <option value="" selected disabled>Seleccione un grado</option>
                                    @foreach ($grados as $g)
                                        <option value="{{ $g->id }}">
                                            {{ trim(($g->tipo ?? '') . ' ' . ($g->numero ?? '') . ' ' . ($g->nombre ?? '')) }}
                                            ({{ $g->puntas }} puntas · {{ $g->dias }} días)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Fecha de inicio <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Observaciones</label>
                                <textarea name="observacion" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-submit">Registrar Grado</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form id="form-add-enfermedad" class="form-edit-add" action="{{ route('alumno.enfermedade.store') }}" method="POST">
            @csrf
            <div class="modal fade" tabindex="-1" id="modal-add-enfermedad" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Enfermedad</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                            <div class="form-group col-md-12">
                                <label for="nombre">Nombre de la Enfermedad</label>
                                <input type="text" class="form-control" name="nombre" placeholder="Nombre de la enfermedad" required>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="medicamento">Nombre del Medicamento</label>
                                <input type="text" class="form-control" name="medicamento" placeholder="Medicamento">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="administracion">Administración del Medicamento</label>
                                <input type="text" class="form-control" name="administracion" placeholder="Administración del medicamento">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="observacion">Observaciones</label>
                                <textarea name="observacion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-submit">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .alumno-summary-panel .panel-body { padding: 18px; }
        .alumno-photo-card { margin-bottom: 15px; }
        .alumno-photo-title,
        .alumno-label {
            color: #7a8a9a;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .04em;
            margin-bottom: 6px;
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
        .alumno-value {
            color: #253443;
            font-size: 15px;
            line-height: 1.5;
        }
        .alumno-data-card {
            background-color: #fbfcfe;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            margin-bottom: 12px;
            min-height: 70px;
            padding: 12px 14px;
        }
        .alumno-status-label {
            font-size: 12px;
            padding: 6px 10px;
        }
        .alumno-tabs { margin-bottom: 15px; }
        .alumno-tab-content {
            border: 1px solid #edf2f7;
            border-top: 0;
            border-radius: 0 0 8px 8px;
            padding: 15px;
        }
        .alumno-tab-toolbar { margin-bottom: 10px; }
        .select2-container { width: 100% !important; }
        .select2-dropdown { z-index: 10001; }
        @media (max-width: 991px) {
            .alumno-info-header { display: block; }
            .alumno-status-wrap { margin-top: 10px; }
        }
    </style>
@stop

@section('javascript')
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    <script>
        var countPageGrado = 10;
        var countPageTutor = 10;
        var countPageEnfer = 10;
        var timeoutGrado = null;
        var timeoutTutor = null;
        var timeoutEnfer = null;

        $(document).ready(function() {
            $('#modal-add-tutor, #modal-add-enfermedad, #modal-add-grado').on('shown.bs.modal', function() {
                $(this).find('.select2').select2({
                    dropdownParent: $(this),
                    width: '100%'
                });
            });

            gradoList();
            listTutores();
            enfermedadList();

            $('#input-search-grado').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    clearTimeout(timeoutGrado);
                    gradoList();
                }
            }).on('input', function() {
                clearTimeout(timeoutGrado);
                timeoutGrado = setTimeout(function() { gradoList(); }, 1000);
            });

            $('#input-search-tutor').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    clearTimeout(timeoutTutor);
                    listTutores();
                }
            }).on('input', function() {
                clearTimeout(timeoutTutor);
                timeoutTutor = setTimeout(function() { listTutores(); }, 1000);
            });

            $('#input-search-enfermedad').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    clearTimeout(timeoutEnfer);
                    enfermedadList();
                }
            }).on('input', function() {
                clearTimeout(timeoutEnfer);
                timeoutEnfer = setTimeout(function() { enfermedadList(); }, 1000);
            });

            $('#select-paginate-grado').change(function() {
                countPageGrado = $(this).val();
                gradoList();
            });

            $('#select-paginate-tutor').change(function() {
                countPageTutor = $(this).val();
                listTutores();
            });

            $('#select-paginate-enfer').change(function() {
                countPageEnfer = $(this).val();
                enfermedadList();
            });

            $(document).on('click', '#div-grados-list .pagination a', function(e) {
                e.preventDefault();
                let link = $(this).attr('href');
                if (link) {
                    let page = new URL(link).searchParams.get('page') || 1;
                    gradoList(page);
                }
            });

            $(document).on('click', '#div-tutores-list .pagination a', function(e) {
                e.preventDefault();
                let link = $(this).attr('href');
                if (link) {
                    let page = new URL(link).searchParams.get('page') || 1;
                    listTutores(page);
                }
            });

            $(document).on('click', '#div-enfermedad-list .pagination a', function(e) {
                e.preventDefault();
                let link = $(this).attr('href');
                if (link) {
                    let page = new URL(link).searchParams.get('page') || 1;
                    enfermedadList(page);
                }
            });
        });

        function gradoList(page = 1) {
            $('#div-grados-list').loading({ message: 'Cargando...' });
            let id = "{{ $alumno->id }}";
            let search = $('#input-search-grado').val() ? $('#input-search-grado').val() : '';
            let url = "{{ url('admin/alumnos') }}/" + id + "/grados/list";

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPageGrado}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $('#div-grados-list').html(result);
                    $('#div-grados-list').loading('toggle');
                    // Mostrar u ocultar el botón "Nuevo Grado"
                    var puedeAgregar = $('#puede-agregar-grado').val();
                    $('#btn-add-grado').toggle(puedeAgregar === '1');
                    // Aplicar fecha mínima al input del modal
                    var minFecha = $('#min-fecha-grado').val();
                    var $fechaInput = $('#form-add-grado input[name="fecha"]');
                    if (minFecha) {
                        $fechaInput.attr('min', minFecha);
                        if ($fechaInput.val() < minFecha) {
                            $fechaInput.val(minFecha);
                        }
                    } else {
                        $fechaInput.removeAttr('min');
                    }
                },
                error: function() {
                    $('#div-grados-list').html('<p class="text-center">No se pudieron cargar los grados.</p>');
                    $('#div-grados-list').loading('toggle');
                    $('#btn-add-grado').hide();
                }
            });
        }

        function listTutores(page = 1) {
            $('#div-tutores-list').loading({ message: 'Cargando...' });
            let id = "{{ $alumno->id }}";
            let search = $('#input-search-tutor').val() ? $('#input-search-tutor').val() : '';
            let url = "{{ url('admin/alumnos') }}/" + id + "/parentesco/list";

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPageTutor}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $('#div-tutores-list').html(result);
                    $('#div-tutores-list').loading('toggle');
                },
                error: function() {
                    $('#div-tutores-list').html('<p class="text-center">No se pudieron cargar los tutores.</p>');
                    $('#div-tutores-list').loading('toggle');
                }
            });
        }

        function enfermedadList(page = 1) {
            $('#div-enfermedad-list').loading({ message: 'Cargando...' });
            let id = "{{ $alumno->id }}";
            let search = $('#input-search-enfermedad').val() ? $('#input-search-enfermedad').val() : '';
            let url = "{{ url('admin/alumnos') }}/" + id + "/enfermedade/list";

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPageEnfer}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $('#div-enfermedad-list').html(result);
                    $('#div-enfermedad-list').loading('toggle');
                },
                error: function() {
                    $('#div-enfermedad-list').html('<p class="text-center">No se pudieron cargar las enfermedades.</p>');
                    $('#div-enfermedad-list').loading('toggle');
                }
            });
        }

        function deleteItem(url) {
            $('#delete_form').attr('action', url);
        }
    </script>
@stop
