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

                        {{-- lista de personas --}}
                        <div class="panel-body">
                            <div class="form-group col-md-8">       
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
                            <div class="form-group col-md-6">
                                {{-- lista de Horario --}}
                                <label for="horario_id">Horario</label>
                                <select name="horario_id" class="form-control select2" required>
                                    <option value="">Seleccione un Horario</option>
                                    @foreach($horario as $horarios)
                                        <option value="{{ $horarios->id }} " {{ old('horario_id', $dataTypeContent->horario_id) == $horarios->id ? 'selected' : '' }}>
                                        <option value="{{ $horarios->id }}" {{ old('horario_id', $dataTypeContent->horario_id) == $horarios->id ? 'selected' : '' }}>
                                            {{ $horarios->id }} {{ $horarios->tipo }} {{ $horarios->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Grado --}}  
                            <div class="form-group col-md-6">
                                {{-- lista de Grados --}}
                                <label for="grado_id">Grado</label>
                                <select name="grado_id" class="form-control select2" required>
                                    <option value="">Seleccione un Grado</option>
                                    @foreach($grado as $grados)
                                        <option value="{{ $grados->id }} " {{ old('grado_id', $dataTypeContent->grado_id) == $grados->id ? 'selected' : '' }}>
                                        <option value="{{ $grados->id }}" {{ old('grado_id', $dataTypeContent->grado_id) == $grados->id ? 'selected' : '' }}>
                                            {{ $grados->tipo }} {{ $grados->numero }} {{ $grados->nombre }}
                                        </option>
                                    @endforeach
                                </select>
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
                            <div class="form-group col-md-6">
                                <label for="observacion">Observaciones</label>
                                <textarea class="form-control" name="observacion" placeholder="observacion" rows="3" required>{{ old('observacion', $dataTypeContent->observacion) }}</textarea>
                            </div>
                            {{-- Estado --}}
                            <input type="hidden" name="status" value="1">
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
    </style>
@stop
