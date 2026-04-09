@extends('voyager::master')

@section('page_title', 'Ver Torneo')


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
        // Usamos $dataTypeContent que es la variable por defecto que pasa Voyager para el registro actual
        $alumno = $dataTypeContent;
        
        // Lógica de imagen similar a tu list.blade.php
        $image = asset('images/default.jpg');
        if($alumno->foto && (Str::endsWith($alumno->foto, ['.jpg', '.jpeg', '.png', '.webp', '.avif']))){
            $image = asset('storage/' . str_replace('.avif', '', $alumno->foto) . '-cropped.webp');
        }
    @endphp


<div class="panel panel-bordered">
    <div class="panel-body" style="padding: 0px">
        <div class="col-md-3" style="padding: 0px">
            {{-- Columna de Imagen/Poster --}}
            <div class="panel-heading" style="padding: 0px">
                <h3 class="panel-title" style="text-align:center">Foto</h3>
            </div>
            <div class="panel-body" style="padding-top:0;">
                @if($alumno->foto && (Str::endsWith($alumno->foto, ['.jpg', '.jpeg', '.png', '.webp', '.avif'])))
                    <img src="{{ $image }}" style="width:100%; max-width:200px; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                @else
                    <div style="width:100%; height:400px; background-color: #f8f9fa; display:flex; align-items:center; justify-content:center; border: 1px solid #ddd; border-radius: 5px; color:#999;">
                        <i class="voyager-trophy" style="font-size: 50px;"></i>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-8" style="padding: 0px">
            <div class="col-md-12">
                <label class="control-label" style="font-weight:bold;">Nombre del Alumno</label>
                <p class="form-control-static">{{ $alumno->person->first_name }} </p>
            </div>
            <div class="col-md-12">
                 <label class="control-label" style="font-weight:bold;"> </label>
            </div> 

            <div class="col-md-12">
                 <label class="control-label" style="font-weight:bold;"> </label>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" style="font-weight:bold; text-align:center;">Fecha Ingteso</label>
                <p class="form-control-static">{{ \Carbon\Carbon::parse($alumno->entry_date)->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-6">
                <label class="control-label" style="font-weight:bold;">Horario</label>
                <p class="form-control-static">{{ $alumno->grado->numero}} {{ $alumno->grado->tipo }} {{ $alumno->grado->nombre}} </p>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label" style="font-weight:bold;">Estado</label>
                <p class="form-control-static">
                    @if ($alumno->status==1)  
                        <label class="label label-success">Activo</label>
                    @else
                        <label class="label label-warning">Inactivo</label>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>




@stop