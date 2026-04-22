@extends('voyager::master')

@section('page_title', (isset($dataTypeContent->id) ? 'Editar' : 'Crear') . ' Alumno')

@section('page_header')
    <h1 class="page-title">
        <i class="fa-solid fa-user-graduate"></i>
        {{ isset($dataTypeContent->id) ? 'Editar' : 'Crear' }} Alumno
    </h1>
@stop


@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <!-- FORM -->
                    <form role="form"
                        action="{{ isset($dataTypeContent->id) ? route('voyager.alumnos.update', $dataTypeContent->id) : route('voyager.alumnos.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @if (isset($dataTypeContent->id))
                            @method('PUT')
                        @endif
                        {{-- lista de dojo --}}
                        <div class="panel-body">
                            {{-- lista de Dojos --}}
                            <div class="form-group col-md-6">
                                <label for="dojo_id">Nombre del Dojo</label>
                                @if ($dataTypeContent)
                                    <select class="form-control select2" disabled>
                                        <option value="">Seleccione un Dojo</option>
                                        @foreach ($dojo as $dojos)
                                            <option value="{{ $dojos->id }}"
                                                {{ old('dojo_id', $dataTypeContent->dojo_id) == $dojos->id ? 'selected' : '' }}>
                                                {{ $dojos->nombre }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" name="dojo_id" value="{{ $dataTypeContent->dojo_id }}">
                                @else
                                    <select name="dojo_id" class="form-control select2" required>
                                        <option value="">Seleccione un Dojo</option>
                                        @foreach ($dojo as $dojos)
                                            <option value="{{ $dojos->id }}"
                                                {{ old('dojo_id') == $dojos->id ? 'selected' : '' }}>
                                                {{ $dojos->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            {{-- lista de personas --}}
                            <div class="form-group col-md-6">
                                <label for="person_id">Nombre del Alumno</label>
                                @if ($dataTypeContent)
                                    <select class="form-control select2" disabled>
                                        @foreach ($people as $person)
                                            <option value="{{ $person->id }}"
                                                {{ old('person_id', $dataTypeContent->person_id) == $person->id ? 'selected' : '' }}>
                                                {{ $person->first_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" name="person_id" value="{{ $dataTypeContent->person_id }}">
                                @else
                                    <select name="person_id" class="form-control select2" required>
                                        <option value="">Seleccione una persona</option>
                                        @foreach ($people as $person)
                                            <option value="{{ $person->id }}"
                                                {{ old('person_id') == $person->id ? 'selected' : '' }}>
                                                {{ $person->first_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- Fecha Ingreso --}}
                            <div class="form-group col-md-4">
                                <label for="fechainicio">Fecha Ingreso</label>
                                <input type="date" class="form-control" name="entry_date"
                                    value="{{ old('entry_date', $dataTypeContent->entry_date) }}" required>
                            </div>
                            {{-- Horario --}}
                            <div class="form-group col-md-4">
                                {{-- lista de Horario --}}
                                <label for="horario_id">Horario</label>
                                <select name="horario_id" class="form-control select2" required>
                                    <option value="">Seleccione un Horario</option>
                                    @foreach ($horario as $horarios)
                                        <option value="{{ $horarios->id }}"
                                            {{ old('horario_id', $dataTypeContent->horario_id) == $horarios->id ? 'selected' : '' }}>
                                            {{ $horarios->tipo }} {{ $horarios->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Grado --}}
                            <div class="form-group col-md-4">
                                {{-- lista de Grados --}}
                                <label for="grado_id">Grado</label>
                                <select name="grado_id" class="form-control select2" required>
                                    <option value="">Seleccione un Grado</option>
                                    @foreach ($grado as $grados)
                                        <option value="{{ $grados->id }}"
                                            {{ old('grado_id', $dataTypeContent->grado_id) == $grados->id ? 'selected' : '' }}>
                                            {{ $grados->tipo }} {{ $grados->numero }} {{ $grados->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Estado Alumno --}}

                            <div class="col-md-6 form-group">
                                <label class="control-label" style="font-weight:bold; display:block;">Estado</label>
                                <div class="switch-container">
                                    <input type="hidden" name="status"
                                        value="{{ isset($dataTypeContent->id) ? $dataTypeContent->status : 1 }}">

                                    <label class="switch">
                                        <input type="checkbox" id="status_toggle" value="1"
                                            {{ (isset($dataTypeContent->status) ? $dataTypeContent->status : 1) == 1 ? 'checked' : '' }}
                                            disabled>
                                        <span class="slider round"></span>
                                    </label>

                                    <span id="status-text" class="status-label"
                                        style="color: {{ (isset($dataTypeContent->status) ? $dataTypeContent->status : 1) == 1 ? '#5cb85c' : '#d9534f' }};">
                                        {{ (isset($dataTypeContent->status) ? $dataTypeContent->status : 1) == 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>


                            {{-- Observaciones --}}
                            <div class="form-group col-md-12">
                                <label for="observacion">Observaciones</label>
                                <textarea class="form-control" name="observacion" placeholder="observacion" rows="3">{{ old('observacion', $dataTypeContent->observacion) }}</textarea>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            <a href="{{ route('voyager.alumnos.index') }}"
                                class="btn btn-default">{{ __('voyager::generic.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modales de validación amarillos --}}
        <div class="modal modal-warning fade" tabindex="-1" id="modal-alumno-exists" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Alumno ya existe <span class="nombre-dojo" style="font-weight: bold;"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-alumno-other-dojo" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Alumno registrado en el Dojo <span class="nombre-dojo" style="font-weight: bold;"></span></p>
                        <p>para reistrar el alumno inactiva del Dojo <span class="nombre-dojo"
                                style="font-weight: bold;"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal para cuando la persona es responsable del mismo Dojo --}}
        <div class="modal modal-warning fade" tabindex="-1" id="modal-responsible-same-dojo" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>La persona seleccionada es responsable del Dojo <span class="nombre-dojo"
                                style="font-weight: bold;"></span> y no puede ser registrada como alumno en el mismo Dojo.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
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

        .switch-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d9534f;
            /* ROJO para Inactivo */
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        /* VERDE cuando el input está tiqueado */
        input:checked+.slider {
            background-color: #5cb85c;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

        .status-label {
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
        }
    </style>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            // Lógica de validación AJAX al cambiar de persona o de dojo
            $('select[name="person_id"], select[name="dojo_id"]').on('change', function() {
                // Siempre capturamos los valores actuales de ambos selectores
                var person_id = $('select[name="person_id"]').val() || null;
                var dojo_id = $('select[name="dojo_id"]').val();

                if (person_id && dojo_id) {
                    // Obtenemos el ID del registro actual (vacío si es nuevo)
                    let currentId = '{{ $dataTypeContent->id ?? '' }}';

                    // Construimos la URL con los parámetros necesarios
                    let url = '{{ route('alumnos.check_registration', ['person_id' => 'TEMP_ID']) }}'
                        .replace('TEMP_ID', person_id);
                    let params = [];
                    if (currentId) params.push('id=' + currentId);
                    if (dojo_id) params.push('dojo_id=' + dojo_id);

                    if (params.length > 0) url += '?' + params.join('&');

                    $.get(url, function(data) {
                        // Actualizamos el nombre del dojo en todos los spans de los modales
                        $('.nombre-dojo').text(data.dojo);

                        if (data.status == 'exists') {
                            $('#modal-alumno-exists').modal('show');
                            // Limpiamos la selección para obligar a elegir otra persona
                            $('select[name="person_id"]').val('').trigger('change');
                        } else if (data.status == 'other_dojo') {
                            $('#modal-alumno-other-dojo').modal('show');
                            $('select[name="person_id"]').val('').trigger('change');
                        } else if (data.status == 'responsible_same_dojo') {
                            $('#modal-responsible-same-dojo').modal('show');
                            $('select[name="person_id"]').val('').trigger('change');
                        }
                    });
                }
            });
        });
    </script>
@stop
