@extends('voyager::master')

@section('page_title', 'Viendo Alumnos')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-user-graduate"></i> Alumnos
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modal-print">
                                <i class="fa-solid fa-print"></i> <span>Imprimir</span>
                            </a>
                            @if (auth()->user()->hasPermission('add_alumnos'))
                                <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modal-add-alumno">
                                    <i class="voyager-plus"></i> <span>Crear</span>
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
    @php
        $userDojoId = auth()->user()->dojo_id;
        $currentDojoId = old('dojo_id', $selectedDojoId ?? $userDojoId);
    @endphp

    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search" placeholder="🔍 Buscar..." class="form-control">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-cannot-delete" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #f0ad4e; color: black;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: black;"><i class="voyager-warning"></i> Advertencia</h4>
                    </div>
                    <div class="modal-body" style="color: black;">
                        <p>El Alumno no se puede eliminar porque tiene un Historial.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-status" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: black;"><i class="fa-solid fa-person-circle-xmark"></i> Cambio de estado del Alumno</h4>
                    </div>
                    <div class="modal-body" style="color: black;">
                        <p><strong>Alumno:</strong> <span id="status-alumno-name"></span></p>
                        <p><strong>Dojo:</strong> <span id="status-alumno-dojo"></span></p>
                        <p><strong>Estado actual:</strong> <span id="status-alumno-current"></span></p>
                    </div>
                    <div class="modal-footer" style="text-align: center; color: black;">
                        <p id="status-alumno-question">Esta seguro de cambiar el estado del alumno</p>
                        <br>
                        <form action="#" id="status_form" method="POST">
                            @csrf
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark" id="status-submit-button">Cambiar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-danger fade" tabindex="-1" id="modal-delete" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> ¿Estás seguro de que quieres eliminar esto?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete_form" method="POST">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar esto">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-success fade" tabindex="-1" id="modal-print" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: white;"><i class="fa-solid fa-print"></i> Imprimir Reporte de Alumnos</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="print_dojo_id" style="color: #333;">Seleccione el Dojo que desea imprimir:</label>
                            <select id="print_dojo_id" class="form-control">
                                <option value="">-- Todos los Dojos --</option>
                                @foreach (\App\Models\Dojo::whereNull('deleted_at')->get() as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btn-print-confirm">Imprimir</button>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.modal-registerPerson')

        <div class="modal modal-primary fade" tabindex="-1" id="modal-add-alumno" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="form-add-alumno" action="{{ route('voyager.alumnos.store') }}" method="POST">
                        @csrf

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" style="color: white;">
                                <i class="fa-solid fa-user-graduate"></i> Registrar Alumno
                            </h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="dojo_id" id="modal_alumno_dojo_id" value="{{ $currentDojoId }}">

                                <div class="col-md-9 form-group">
                                    <label for="modal_select_person_id">Alumno</label>
                                    <div class="input-group">
                                        <select name="person_id" id="modal_select_person_id" class="form-control" required></select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" title="Nueva persona" style="margin: 0px" type="button" id="btn-open-create-person-from-alumno">
                                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="modal_fechaIngreso">Fecha de Ingreso</label>
                                    <input type="date" id="modal_fechaIngreso" class="form-control" name="fechaIngreso" value="{{ old('fechaIngreso') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <input type="hidden" name="status" value="1">

                                <div class="col-md-12 form-group">
                                    <label for="modal_observacion">Observaciones</label>
                                    <textarea class="form-control" id="modal_observacion" name="observacion" rows="3" placeholder="Observación adicional">{{ old('observacion') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-submit">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-alumno-exists" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Alumno ya existe en el Dojo <span class="nombre-dojo" style="font-weight: bold;"></span>.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-alumno-other-dojo" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Alumno registrado en el Dojo <span class="nombre-dojo" style="font-weight: bold;"></span>.</p>
                        <p>Para registrarlo aquí primero debe inactivarse en ese Dojo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-responsible-same-dojo" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>La persona seleccionada es responsable del Dojo <span class="nombre-dojo" style="font-weight: bold;"></span> y no puede registrarse como alumno en esa misma sucursal.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    <script src="{{ asset('js/include/person-select.js') }}"></script>
    <script src="{{ asset('js/include/person-register.js') }}"></script>

    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        var timeout = null;
        var shouldRestoreAlumnoModal = false;
        var alumnoPersonSelected = null;

        window.getPersonListParams = function () {
            if ('{{ $userDojoId }}') {
                return {
                    dojo_id: '{{ $userDojoId }}'
                };
            }

            return {};
        };

        function initAlumnoPersonSelect() {
            if ($('#modal_select_person_id').hasClass('select2-hidden-accessible')) {
                return;
            }

            $('#modal_select_person_id').select2({
                width: '100%',
                dropdownParent: $('#modal-add-alumno'),
                placeholder: '<i class="fa fa-search"></i> Buscar...',
                escapeMarkup: function(markup) {
                    return markup;
                },
                language: {
                    inputTooShort: function (data) {
                        return `Por favor ingrese ${data.minimum - data.input.length} o más caracteres`;
                    },
                    noResults: function () {
                        return `<i class="far fa-frown"></i> No hay resultados encontrados`;
                    }
                },
                quietMillis: 250,
                minimumInputLength: 2,
                ajax: {
                    url: window.personListUrl,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            dojo_id: $('#modal_alumno_dojo_id').val() || '{{ $userDojoId }}'
                        };
                    },
                    processResults: function (data) {
                        let results = [];
                        data.map(data => {
                            results.push({
                                ...data,
                                disabled: false
                            });
                        });
                        return { results };
                    },
                    cache: true
                },
                templateResult: formatPersonResult,
                templateSelection: function (opt) {
                    alumnoPersonSelected = opt;
                    window.personSelected = opt;
                    return opt.first_name ? opt.first_name : '<i class="fa fa-search"></i> Buscar... ';
                }
            });
        }

        function validateAlumnoRegistration() {
            const personId = $('#modal_select_person_id').val();
            const dojoId = '{{ $userDojoId }}' || (alumnoPersonSelected ? alumnoPersonSelected.dojo_id : null);

            if (!personId || !dojoId) {
                return;
            }

            $('#modal_alumno_dojo_id').val(dojoId);

            let url = '{{ route('alumnos.check_registration', ['person_id' => 'TEMP_ID']) }}'.replace('TEMP_ID', personId);
            url += '?dojo_id=' + dojoId;

            $.get(url, function(data) {
                $('.nombre-dojo').text(data.dojo || '');

                if (data.status === 'exists') {
                    $('#modal-alumno-exists').modal('show');
                    $('#modal_select_person_id').val(null).trigger('change');
                } else if (data.status === 'other_dojo') {
                    $('#modal-alumno-other-dojo').modal('show');
                    $('#modal_select_person_id').val(null).trigger('change');
                } else if (data.status === 'responsible_same_dojo') {
                    $('#modal-responsible-same-dojo').modal('show');
                    $('#modal_select_person_id').val(null).trigger('change');
                }
            });
        }

        $(document).ready(() => {
            list();
            $('.toggleswitch').bootstrapToggle();

            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    clearTimeout(timeout);
                    list();
                }
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
                list();
            });

            $('#input-search').on('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    list();
                }, 2000);
            });

            $('#btn-print-confirm').click(function(){
                let search = $('#input-search').val() ? $('#input-search').val() : '';
                let dojo_id = $('#print_dojo_id').val();
                let url = '{{ route("alumnos.print") }}';
                window.open(`${url}?search=${search}&dojo_id=${dojo_id}`, '_blank');
                $('#modal-print').modal('hide');
            });

            $('#modal_alumno_dojo_id').on('change', function() {
                $('#modal_select_person_id').val(null).trigger('change');
            });

            $('#modal_select_person_id').on('change', function() {
                validateAlumnoRegistration();
            });

            $('#btn-open-create-person-from-alumno').on('click', function(e) {
                e.preventDefault();
                shouldRestoreAlumnoModal = true;

                const dojoId = '{{ $userDojoId }}' || (alumnoPersonSelected ? alumnoPersonSelected.dojo_id : null);

                if ($('#modal_person_dojo_id').length) {
                    $('#modal_person_dojo_id').val(dojoId);
                }

                if ($('#modal_person_dojo_select').length && !$('#modal_person_dojo_select').is(':disabled')) {
                    $('#modal_person_dojo_select').val(dojoId).trigger('change');
                }

                $('#modal-add-alumno').modal('hide');
            });

            $('#modal-add-alumno').on('hidden.bs.modal', function() {
                if (shouldRestoreAlumnoModal) {
                    $('#modal-create-person').modal('show');
                }
            });

            $('#modal-create-person').on('hidden.bs.modal', function() {
                if (shouldRestoreAlumnoModal) {
                    $('#modal-add-alumno').modal('show');
                }
            });

            $(document).on('person:created', function(event, person) {
                if (!shouldRestoreAlumnoModal || !person) {
                    return;
                }

                alumnoPersonSelected = person;
                $('#modal_alumno_dojo_id').val(person.dojo_id || '{{ $userDojoId }}');
                const option = new Option(person.first_name, person.id, true, true);
                $('#modal_select_person_id').append(option).trigger('change');
                $('#modal_select_person_id').trigger({
                    type: 'select2:select',
                    params: {
                        data: person
                    }
                });
            });

            $('#modal-add-alumno').on('shown.bs.modal', function() {
                shouldRestoreAlumnoModal = false;
                alumnoPersonSelected = null;
                $('#modal_alumno_dojo_id').val('{{ $userDojoId ?: '' }}');
                initAlumnoPersonSelect();
                $('#modal_select_person_id').val(null).trigger('change');
            });
        });

        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});

            let url = '{{ url("admin/alumnos/ajax/list") }}';
            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPage}&page=${page}`,
                type: 'get',
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                }
            });
        }

        function statusItem(id, name, dojo, status) {
            let url = '{{ route("alumnos.status.update", ["id" => "TEMP_ID"]) }}'.replace('TEMP_ID', id);
            $('#status_form').attr('action', url);
            $('#status-alumno-name').text(name);
            $('#status-alumno-dojo').text(dojo);
            const isActive = String(status) === '1';
            $('#status-alumno-current').text(isActive ? 'Activo' : 'Inactivo');
            $('#status-alumno-question').text(isActive ? 'Esta seguro de deshabilitar al alumno' : 'Esta seguro de habilitar al alumno');
            $('#status-submit-button')
                .text(isActive ? 'Deshabilitar' : 'Habilitar')
                .removeClass('btn-dark btn-success')
                .addClass(isActive ? 'btn-dark' : 'btn-success');
            $('#modal-status').modal('show');
        }

        function deleteItem(alumnoId, url){
            $.ajax({
                url: "{{ route('alumnos.check_historial', ['id' => 'TEMP_ID']) }}".replace('TEMP_ID', alumnoId),
                type: 'GET',
                success: function(response) {
                    if (response.has_historial) {
                        $('#modal-cannot-delete').modal('show');
                    } else {
                        $('#delete_form').attr('action', url);
                        $('#modal-delete').modal('show');
                    }
                },
                error: function() {
                    alert('Ocurrió un error al verificar el historial del alumno. Por favor, inténtelo de nuevo.');
                }
            });
        }
    </script>
@stop
