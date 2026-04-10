@extends('voyager::master')

@section('page_title', (isset($dataTypeContent->id) ? 'Editar' : 'Crear').' Alumno')

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
                        @if(isset($dataTypeContent->id))
                            @method('PUT')
                        @endif
                        {{-- lista de dojo --}}
                        <div class="panel-body">
                            {{-- lista de Dojos --}}
                            <div class="form-group col-md-6">       
                                <label for="dojo_id">Nombre del Dojo</label>
                                <select name="dojo_id" class="form-control select2" required>
                                    <option value="">Seleccione un Dojo</option>
                                    @foreach($dojo as $dojos)
                                        <option value="{{$dojos->id }}" {{ old('dojo_id', $dataTypeContent->dojo_id) == $dojos->id ? 'selected' : '' }}>
                                            {{ $dojos->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- lista de personas --}}
                            <div class="form-group col-md-6">       
                                <label for="person_id">Nombre del Alumno</label>
                                <select name="person_id" class="form-control select2" required>
                                    <option value="">Seleccione una persona</option>
                                    @foreach($people as $person)
                                        <option value="{{ $person->id }}" {{ old('person_id', $dataTypeContent->person_id) == $person->id ? 'selected' : '' }}>
                                            {{ $person->id }} {{ $person->first_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Fecha Ingreso --}}                
                            <div class="form-group col-md-4">
                                <label for="fechainicio">Fecha Ingreso</label>
                                <input type="date" class="form-control" name="entry_date" value="{{ old('entry_date', $dataTypeContent->entry_date) }}" required>
                            </div>
                            {{-- Horario --}}  
                            <div class="form-group col-md-4">
                                {{-- lista de Horario --}}
                                <label for="horario_id">Horario</label>
                                <select name="horario_id" class="form-control select2" required>
                                    <option value="">Seleccione un Horario</option>
                                    @foreach($horario as $horarios)
                                        <option value="{{ $horarios->id }}" {{ old('horario_id', $dataTypeContent->horario_id) == $horarios->id ? 'selected' : '' }}>
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
                                    @foreach($grado as $grados)
                                        <option value="{{ $grados->id }}" {{ old('grado_id', $dataTypeContent->grado_id) == $grados->id ? 'selected' : '' }}>
                                            {{ $grados->tipo }} {{ $grados->numero }} {{ $grados->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Estado Alumno --}}

                            <div class="col-md-6 form-group">
                                <label class="control-label" style="font-weight:bold; display:block;">Estado</label>
                                <div class="switch-container">
                                    <input type="hidden" name="status" value="0">
                                    
                                    <label class="switch">
                                        <input type="checkbox" 
                                            name="status" 
                                            id="status_toggle" 
                                            value="1"
                                            onchange="updateStatusText(this)"
                                            {{ $dataTypeContent->status == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    
                                    <span id="status-text" class="status-label" style="color: {{ $dataTypeContent->status == 1 ? '#5cb85c' : '#d9534f' }};">
                                        {{ $dataTypeContent->status == 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Foto Alumno --}}
                            <div class="form-group col-md-6">
                                <label for="foto">Foto</label>
                                    @if($dataTypeContent->foto)
                                        <div class="m-b-10">
                                            <a href="{{ asset('storage/'.$dataTypeContent->foto) }}" target="_blank" class="btn btn-sm btn-info">Ver foto actual</a>
                                        </div>
                                    @endif
                                <input type="file" name="foto" class="form-control">
                            </div>
                            {{-- Observaciones --}}
                            <div class="form-group col-md-12">
                                <label for="observacion">Observaciones</label>
                                <textarea class="form-control" name="observacion" placeholder="observacion" rows="3">{{ old('observacion', $dataTypeContent->observacion) }}</textarea>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            <a href="{{ route('voyager.alumnos.index') }}" class="btn btn-default">{{ __('voyager::generic.cancel') }}</a>
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

        .switch-container { display: flex; align-items: center; gap: 12px; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #d9534f; /* ROJO para Inactivo */
        transition: .4s; border-radius: 34px;
        }

        .slider:before {
        position: absolute; content: "";
        height: 18px; width: 18px; left: 4px; bottom: 4px;
        background-color: white; transition: .4s; border-radius: 50%;
        }

        /* VERDE cuando el input está tiqueado */
        input:checked + .slider { background-color: #5cb85c; }
        input:checked + .slider:before { transform: translateX(24px); }

        .status-label { font-weight: bold; font-size: 14px; transition: 0.3s; }


    </style>

    <script>
        function updateStatusText(checkbox) {
            const textLabel = document.getElementById('status-text');
            
            if (checkbox.checked) {
                // Estado Activo (1)
                textLabel.innerText = 'Activo';
                textLabel.style.color = '#5cb85c'; // Verde
            } else {
                // Estado Inactivo (0)
                textLabel.innerText = 'Inactivo';
                textLabel.style.color = '#d9534f'; // Rojo
            }
        }
    </script>


@stop
