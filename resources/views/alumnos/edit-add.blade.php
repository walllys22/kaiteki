@extends('voyager::master')

@section('page_title', (isset($dataTypeContent->id) ? 'Editar' : 'Crear') . ' Alumno')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-6" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-user-graduate"></i>
                                {{ isset($dataTypeContent->id) ? 'Editar' : 'Crear' }} Alumno
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px">
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
    @php
        $userDojoId = auth()->user()->dojo_id;
        $isEditing = isset($dataTypeContent->id);
        $currentDojoId = old('dojo_id', $selectedDojoId);
        $currentStatus = (int) old('status', $dataTypeContent->status ?? 1);
    @endphp

    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <form role="form"
                        action="{{ $isEditing ? route('voyager.alumnos.update', $dataTypeContent->id) : route('voyager.alumnos.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($isEditing)
                            @method('PUT')
                        @endif

                        <div class="panel-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul style="margin-bottom: 0;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
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
                                </div>

                                <div class="col-md-5 form-group">
                                    <label for="person_id">Alumno</label>
                                    @if ($isEditing)
                                        <select class="form-control select2" disabled>
                                            @foreach ($people as $person)
                                                <option value="{{ $person->id }}" {{ (string) old('person_id', $dataTypeContent->person_id) === (string) $person->id ? 'selected' : '' }}>
                                                    {{ $person->first_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="person_id" value="{{ old('person_id', $dataTypeContent->person_id) }}">
                                    @else
                                        <div class="input-group">
                                            <select name="person_id" id="select-person_id" class="form-control" required></select>
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" title="Nueva persona" data-target="#modal-create-person" data-toggle="modal" style="margin: 0px" type="button">
                                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                                </button>
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="entry_date">Fecha de Ingreso</label>
                                    <input type="date" id="entry_date" class="form-control" name="entry_date" value="{{ old('entry_date', $dataTypeContent->entry_date) }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="horario_id">Horario</label>
                                    <select name="horario_id" id="horario_id" class="form-control select2" required>
                                        <option value="">Seleccione un horario</option>
                                        @foreach ($horario as $item)
                                            <option value="{{ $item->id }}"
                                                data-dojo-id="{{ $item->dojo_id }}"
                                                {{ (string) old('horario_id', $dataTypeContent->horario_id) === (string) $item->id ? 'selected' : '' }}>
                                                {{ trim(($item->tipo ?? '') . ' ' . ($item->nombre ?? '')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label for="grado_id">Grado</label>
                                    <select name="grado_id" id="grado_id" class="form-control select2" required>
                                        <option value="">Seleccione un grado</option>
                                        @foreach ($grado as $item)
                                            <option value="{{ $item->id }}" {{ (string) old('grado_id', $dataTypeContent->grado_id) === (string) $item->id ? 'selected' : '' }}>
                                                {{ trim(($item->tipo ?? '') . ' ' . ($item->numero ?? '') . ' ' . ($item->nombre ?? '')) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label for="tipoSangre">Tipo de Sangre</label>
                                    <select name="tipoSangre" id="tipoSangre" class="form-control select2">
                                        <option value="">Seleccione un tipo de sangre</option>
                                        @foreach ($bloodTypes as $bloodType)
                                            <option value="{{ $bloodType }}" {{ old('tipoSangre', $dataTypeContent->tipoSangre) === $bloodType ? 'selected' : '' }}>
                                                {{ $bloodType }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="control-label" style="font-weight:bold; display:block;">Estado</label>
                                    <input type="hidden" name="status" id="status_input" value="{{ $currentStatus }}">
                                    <input type="checkbox" id="status_toggle" class="toggleswitch"
                                        {{ $currentStatus === 1 ? 'checked' : '' }}
                                        data-on="Activo" data-off="Inactivo">
                                </div>

                                <div class="col-md-8 form-group">
                                    <label for="observacion">Observaciones</label>
                                    <textarea class="form-control" id="observacion" name="observacion" rows="3" placeholder="Observación adicional">{{ old('observacion', $dataTypeContent->observacion) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save btn-submit">{{ __('voyager::generic.save') }}</button>
                            <a href="{{ route('voyager.alumnos.index') }}" class="btn btn-default">{{ __('voyager::generic.cancel') }}</a>
                        </div>
                    </form>
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
    </div>
@stop

@section('css')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@stop

@section('javascript')
    <script src="{{ asset('js/include/person-select.js') }}"></script>
    <script src="{{ asset('js/include/person-register.js') }}"></script>
    <script src="{{ asset('js/btn-submit.js') }}"></script>

    <script>
        window.getPersonListParams = function () {
            return {
                dojo_id: $('#dojo_id').val() || ''
            };
        };

        function syncHorarioOptions() {
            const dojoId = $('#dojo_id').val() || '{{ $userDojoId }}';
            const horarioSelect = $('#horario_id');
            const currentValue = horarioSelect.val();

            horarioSelect.find('option').each(function () {
                const option = $(this);
                const optionDojoId = option.data('dojo-id');

                if (!option.val()) {
                    option.prop('hidden', false).prop('disabled', false);
                    return;
                }

                if (!dojoId || String(optionDojoId) === String(dojoId)) {
                    option.prop('hidden', false).prop('disabled', false);
                } else {
                    option.prop('hidden', true).prop('disabled', true);
                }
            });

            if (currentValue) {
                const selectedOption = horarioSelect.find('option:selected');
                if (selectedOption.prop('disabled')) {
                    horarioSelect.val('').trigger('change');
                } else {
                    horarioSelect.trigger('change.select2');
                }
            }
        }

        function validateAlumnoRegistration() {
            const personId = $('#select-person_id').val();
            const dojoId = $('#dojo_id').val() || '{{ $userDojoId }}';
            const currentId = '{{ $dataTypeContent->id ?? '' }}';

            if (!personId || !dojoId || {{ $isEditing ? 'true' : 'false' }}) {
                return;
            }

            let url = '{{ route('alumnos.check_registration', ['person_id' => 'TEMP_ID']) }}'.replace('TEMP_ID', personId);
            let params = ['dojo_id=' + dojoId];

            if (currentId) {
                params.push('id=' + currentId);
            }

            url += '?' + params.join('&');

            $.get(url, function(data) {
                $('.nombre-dojo').text(data.dojo || '');

                if (data.status === 'exists') {
                    $('#modal-alumno-exists').modal('show');
                    $('#select-person_id').val(null).trigger('change');
                } else if (data.status === 'other_dojo') {
                    $('#modal-alumno-other-dojo').modal('show');
                    $('#select-person_id').val(null).trigger('change');
                } else if (data.status === 'responsible_same_dojo') {
                    $('#modal-responsible-same-dojo').modal('show');
                    $('#select-person_id').val(null).trigger('change');
                }
            });
        }

        $(document).ready(function() {
            $('.toggleswitch').bootstrapToggle();

            $('#status_toggle').change(function() {
                $('#status_input').val($(this).is(':checked') ? 1 : 0);
            });

            syncHorarioOptions();

            $('#dojo_id').on('change', function() {
                syncHorarioOptions();

                if (!{{ $isEditing ? 'true' : 'false' }}) {
                    $('#select-person_id').val(null).trigger('change');
                }
            });

            $('#select-person_id').on('change', function() {
                validateAlumnoRegistration();
            });
        });
    </script>
@stop
