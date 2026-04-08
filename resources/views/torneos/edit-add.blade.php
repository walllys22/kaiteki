@extends('voyager::master')

@section('page_title', (isset($dataTypeContent->id) ? 'Editar' : 'Crear').' Torneo')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-trophy"></i>
        {{ isset($dataTypeContent->id) ? 'Editar' : 'Crear' }} Torneo
    </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <!-- FORM -->
                    <form role="form"
                          action="{{ isset($dataTypeContent->id) ? route('voyager.torneos.update', $dataTypeContent->id) : route('voyager.torneos.store') }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($dataTypeContent->id))
                            @method('PUT')
                        @endif

                        <div class="panel-body">
                            <div class="row">
                                {{-- Nombre del Torneo --}}
                                <div class="form-group col-md-6">
                                    <label for="nombre">Nombre del Torneo</label>
                                    <input type="text" class="form-control" name="nombre" placeholder="Nombre" value="{{ old('nombre', $dataTypeContent->nombre) }}" required>
                                </div>

                                {{-- Persona Encargada --}}
                                <div class="form-group col-md-6">
                                    <label for="person_id">Persona Responsable</label>
                                    <select name="person_id" class="form-control select2" required>
                                        <option value="">Seleccione una persona</option>
                                        @foreach($people as $person)
                                            <option value="{{ $person->id }}" {{ old('person_id', $dataTypeContent->person_id) == $person->id ? 'selected' : '' }}>
                                                {{ $person->first_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Ciudad y Modalidades --}}
                                <div class="form-group col-md-6">
                                    <label for="ciudad_id">Ciudad</label>
                                    <select name="ciudad_id" class="form-control select2" required>
                                        <option value="">Seleccione una ciudad</option>
                                        @foreach($ciudades as $ciudad)
                                            <option value="{{ $ciudad->id }}" {{ old('ciudad_id', $dataTypeContent->ciudad_id) == $ciudad->id ? 'selected' : '' }}>
                                                {{ $ciudad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="modalidad_id">Modalidades (Selección Múltiple)</label>
                                    @php $all_modalidades = \App\Models\Modalida::all(); @endphp
                                    <select name="modalidad_id[]" class="form-control select2" multiple required>
                                        @foreach($all_modalidades as $modalidad)
                                            @php
                                                // Verificamos si el ID está en el array de seleccionados (viniendo de la DB o de un error de validación)
                                                $selected_ids = old('modalidad_id', $dataTypeContent->modalidad_id) ?? [];
                                                $is_selected = is_array($selected_ids) && in_array($modalidad->id, $selected_ids);
                                            @endphp
                                            <option value="{{ $modalidad->id }}" {{ $is_selected ? 'selected' : '' }}>
                                                {{ $modalidad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Fecha Inicio --}}
                                <div class="form-group col-md-6">
                                    <label for="fechainicio">Fecha de Inicio</label>
                                    <input type="date" class="form-control" name="fechainicio" value="{{ old('fechainicio', $dataTypeContent->fechainicio) }}" required>
                                </div>

                                {{-- Fecha Final --}}
                                <div class="form-group col-md-6">
                                    <label for="fechafinal">Fecha Final</label>
                                    <input type="date" class="form-control" name="fechafinal" value="{{ old('fechafinal', $dataTypeContent->fechafinal) }}" required>
                                </div>

                                {{-- Archivo --}}
                                <div class="form-group col-md-12">
                                    <label for="archivo">Archivo / Convocatoria (PDF o Imagen)</label>
                                    @if($dataTypeContent->archivo)
                                        <div class="m-b-10">
                                            <a href="{{ asset('storage/'.$dataTypeContent->archivo) }}" target="_blank" class="btn btn-sm btn-info">Ver archivo actual</a>
                                        </div>
                                    @endif
                                    <input type="file" name="archivo" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            <a href="{{ route('voyager.torneos.index') }}" class="btn btn-default">{{ __('voyager::generic.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .select2-container { width: 100% !important; }
    </style>
@stop

@section('javascript')
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            // Obtener referencias a los campos de fecha
            let $fechaInicio = $('input[name="fechainicio"]');
            let $fechaFinal = $('input[name="fechafinal"]');

            function validarFechas() {
                let inicio = $fechaInicio.val();
                let final = $fechaFinal.val();

                if (inicio && final) {
                    if (final <= inicio) {
                        // Mostrar Toast de error (Voyager usa toastr)
                        toastr.error('La fecha final debe ser estrictamente posterior a la fecha de inicio.');
                        
                        // Limpiar el campo de fecha final
                        $fechaFinal.val('');
                    }
                }

                // Ajustar el atributo 'min' para ayudar al usuario (día siguiente al inicio)
                if (inicio) {
                    let date = new Date(inicio);
                    date.setDate(date.getDate() + 1); // +2 para compensar zona horaria y saltar el día actual
                    let minDate = date.toISOString().split('T')[0];
                    $fechaFinal.attr('min', minDate);
                }
            }

            $fechaInicio.on('change', validarFechas);
            $fechaFinal.on('change', validarFechas);

            // Ejecutar al cargar la página por si ya hay valores (en edición)
            validarFechas();
        });
    </script>
@stop