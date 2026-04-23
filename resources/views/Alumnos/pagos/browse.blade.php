@extends('voyager::master')

@section('page_title', 'Ver Pagos de Alumno')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-money-bill-1"></i> Pagos del Alumno
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
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
                        {{-- Columna de Imagen --}}
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
                                <h3 class="panel-title">Información General</h3>
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
            {{-- Sección de Historial --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-heading"
                            style="border-bottom:0; display: flex; align-items: center; justify-content: space-between;">
                            <h3 class="panel-title" style="margin: 0;">
                                <i class="fa-solid fa-money-bill-1"></i> Historial de pagos
                            </h3>
                            <div style="padding-right: 20px;">
                                @if (auth()->user()->hasPermission('add_alumnos'))
                                    <button class="btn btn-success btn-sm" data-toggle="modal"
                                        data-target="#modal-add-historial">
                                        <i class="voyager-plus"></i> <span>Agregar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="dataTables_length">
                                        <label>Mostrar <select id="select-paginate-historial" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> registros</label>
                                    </div>
                                </div>
                                <div class="col-sm-3" style="margin-bottom: 10px">
                                    <input type="text" id="input-search-historial" placeholder="🔍 Buscar..."
                                        class="form-control">
                                </div>
                            </div>
                            <div id="div-historial-list" style="color: black;">
                                <p class="text-center">Cargando historial...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
