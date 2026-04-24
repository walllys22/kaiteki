@extends('voyager::master')

@php
    $isEdit = !empty($dataTypeContent->id);
    $userDojoId = auth()->user()->dojo_id;
    $currentDojoId = old('dojo_id', $selectedDojoId ?? $dataTypeContent->dojo_id ?? $userDojoId);
@endphp

@section('page_title', $isEdit ? 'Editar Alumno' : 'Registrar Alumno')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">
                    <i class="fa-solid fa-user-graduate"></i>
                    {{ $isEdit ? 'Editar Alumno' : 'Registrar Alumno' }}
                </h1>
                <a href="{{ route('voyager.alumnos.index') }}" class="btn btn-warning">
                    <i class="voyager-list"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <form class="form-edit-add" action="{{ $isEdit ? route('voyager.alumnos.update', $dataTypeContent->id) : route('voyager.alumnos.store') }}" method="POST">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="dojo_id">Sucursal / Dojo</label>
                                    @if ($userDojoId)
                                        <select class="form-control select2" disabled>
                                            @foreach ($dojo as $item)
                                                <option value="{{ $item->id }}" {{ (string) $currentDojoId === (string) $item->id ? 'selected' : '' }}>
                                                    {{ $item->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="dojo_id" id="dojo_id" value="{{ $currentDojoId }}">
                                        <small class="text-muted">El alumno se registrará en tu sucursal asignada.</small>
                                    @else
                                        <select name="dojo_id" id="dojo_id" class="form-control select2" required>
                                            <option value="">Seleccione una sucursal</option>
                                            @foreach ($dojo as $item)
                                                <option value="{{ $item->id }}" {{ (string) $currentDojoId === (string) $item->id ? 'selected' : '' }}>
                                                    {{ $item->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('dojo_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5 form-group">
                                    <label for="select_person_id">Alumno</label>
                                    <div class="input-group">
                                        <select name="person_id" id="select_person_id" class="form-control" required></select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" title="Nueva persona" style="margin: 0px" type="button" id="btn-open-create-person">
                                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                            </button>
                                        </span>
                                    </div>
                                    @error('person_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="fechaIngreso">Fecha de Ingreso</label>
                                    <input type="date" id="fechaIngreso" class="form-control" name="fechaIngreso" value="{{ old('fechaIngreso', $dataTypeContent->fechaIngreso ? \Carbon\Carbon::parse($dataTypeContent->fechaIngreso)->format('Y-m-d') : '') }}" required>
                                    @error('fechaIngreso')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="control-label" style="font-weight:bold; display:block;">Estado</label>
                                    <input type="hidden" name="status" id="status_input" value="{{ old('status', $dataTypeContent->status ?? 1) ? 1 : 0 }}">
                                    <input type="checkbox" id="status_toggle" class="toggleswitch" {{ old('status', $dataTypeContent->status ?? 1) ? 'checked' : '' }} data-on="Activo" data-off="Inactivo">
                                </div>

                                <div class="col-md-8 form-group">
                                    <label for="observacion">Observaciones</label>
                                    <textarea class="form-control" id="observacion" name="observacion" rows="3" placeholder="Observación adicional">{{ old('observacion', $dataTypeContent->observacion) }}</textarea>
                                    @error('observacion')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-submit">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.modal-registerPerson')

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
@stop

@section('javascript')
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    <script src="{{ asset('js/include/person-select.js') }}"></script>
    <script src="{{ asset('js/include/person-register.js') }}"></script>
    <script>
        let shouldRestoreAlumnoForm = false;
        const initialPersonId = '{{ old('person_id', $dataTypeContent->person_id) }}';
        const initialPersonName = @json(optional($dataTypeContent->person)->first_name);

        window.getPersonListParams = function () {
            return {
                dojo_id: $('#dojo_id').val() || '{{ $userDojoId }}'
            };
        };

        function initAlumnoPersonSelect() {
            if ($('#select_person_id').hasClass('select2-hidden-accessible')) {
                return;
            }

            $('#select_person_id').select2({
                width: '100%',
                placeholder: '<i class="fa fa-search"></i> Buscar...',
                escapeMarkup: function(markup) {
                    return markup;
                },
                language: {
                    inputTooShort: function (data) {
                        return `Por favor ingrese ${data.minimum - data.input.length} o más caracteres`;
                    },
                    noResults: function () {
                        return '<i class="far fa-frown"></i> No hay resultados encontrados';
                    }
                },
                quietMillis: 250,
                minimumInputLength: 2,
                ajax: {
                    url: window.personListUrl,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            dojo_id: $('#dojo_id').val() || '{{ $userDojoId }}'
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(item => ({
                                ...item,
                                disabled: false
                            }))
                        };
                    },
                    cache: true
                },
                templateResult: formatPersonResult,
                templateSelection: function (opt) {
                    window.personSelected = opt;
                    return opt.first_name ? opt.first_name : '<i class="fa fa-search"></i> Buscar... ';
                }
            });

            if (initialPersonId && initialPersonName) {
                const option = new Option(initialPersonName, initialPersonId, true, true);
                $('#select_person_id').append(option).trigger('change');
            }
        }

        function validateAlumnoRegistration() {
            const personId = $('#select_person_id').val();
            const dojoId = $('#dojo_id').val() || '{{ $userDojoId }}';

            if (!personId || !dojoId) {
                return;
            }

            let url = '{{ route('alumnos.check_registration', ['person_id' => 'TEMP_ID']) }}'.replace('TEMP_ID', personId);
            url += '?dojo_id=' + dojoId;

            $.get(url, function(data) {
                $('.nombre-dojo').text(data.dojo || '');

                if (data.status === 'exists') {
                    $('#modal-alumno-exists').modal('show');
                    $('#select_person_id').val(null).trigger('change');
                } else if (data.status === 'other_dojo') {
                    $('#modal-alumno-other-dojo').modal('show');
                    $('#select_person_id').val(null).trigger('change');
                } else if (data.status === 'responsible_same_dojo') {
                    $('#modal-responsible-same-dojo').modal('show');
                    $('#select_person_id').val(null).trigger('change');
                }
            });
        }

        $(document).ready(function () {
            $('.toggleswitch').bootstrapToggle();
            initAlumnoPersonSelect();

            $('#status_toggle').change(function() {
                $('#status_input').val($(this).is(':checked') ? 1 : 0);
            });

            $('#dojo_id').on('change', function() {
                $('#select_person_id').val(null).trigger('change');
            });

            $('#select_person_id').on('change', function() {
                validateAlumnoRegistration();
            });

            $('#btn-open-create-person').on('click', function(e) {
                e.preventDefault();
                shouldRestoreAlumnoForm = true;

                const dojoId = $('#dojo_id').val() || '{{ $userDojoId }}';

                if ($('#modal_person_dojo_id').length) {
                    $('#modal_person_dojo_id').val(dojoId);
                }

                if ($('#modal_person_dojo_select').length && !$('#modal_person_dojo_select').is(':disabled')) {
                    $('#modal_person_dojo_select').val(dojoId).trigger('change');
                }

                $('#modal-create-person').modal('show');
            });

            $('#modal-create-person').on('hidden.bs.modal', function() {
                if (shouldRestoreAlumnoForm) {
                    shouldRestoreAlumnoForm = false;
                }
            });

            $(document).on('person:created', function(event, person) {
                if (!person || !person.id) {
                    return;
                }

                const option = new Option(person.first_name, person.id, true, true);
                $('#select_person_id').append(option).trigger('change');
            });
        });
    </script>
@stop
