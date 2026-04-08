@extends('voyager::master')

@section('page_title', 'Ver Torneo')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-6" style="padding: 0px; display: flex; align-items: center;">
                            <h1 class="page-title">
                                <i class="voyager-trophy"></i> Detalles del Torneo
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px">
                            <a href="{{ route('voyager.torneos.index') }}" class="btn btn-warning btn-sm">
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
        // Usamos $dataTypeContent que es la variable por defecto que pasa Voyager para el registro actual
        $torneo = $dataTypeContent;
        
        // Lógica de imagen similar a tu list.blade.php
        $image = asset('images/default.jpg');
        if($torneo->archivo && (Str::endsWith($torneo->archivo, ['.jpg', '.jpeg', '.png', '.webp', '.avif']))){
            $image = asset('storage/' . str_replace('.avif', '', $torneo->archivo) . '-cropped.webp');
        }
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        {{-- Columna de Imagen/Poster --}}
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Imagen / Afiche</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                @if($torneo->archivo && (Str::endsWith($torneo->archivo, ['.jpg', '.jpeg', '.png', '.webp', '.avif'])))
                                    <img src="{{ $image }}" style="width:100%; max-width:300px; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                                @else
                                    <div style="width:100%; height:200px; background-color: #f8f9fa; display:flex; align-items:center; justify-content:center; border: 1px solid #ddd; border-radius: 5px; color:#999;">
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
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Nombre del Torneo</label>
                                        <p class="form-control-static">{{ strtoupper($torneo->nombre) }}</p>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Estado</label>
                                        <p class="form-control-static">
                                            @if ($torneo->estado=="En curso")  
                                                <label class="label label-success">{{$torneo->estado}}</label>
                                            @else
                                                <label class="label label-warning">{{$torneo->estado}}</label>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Ciudad</label>
                                        <p class="form-control-static">{{ $torneo->ciudad->nombre }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Fecha de Inicio</label>
                                        <p class="form-control-static">{{ \Carbon\Carbon::parse($torneo->fechainicio)->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Fecha Final</label>
                                        <p class="form-control-static">{{ \Carbon\Carbon::parse($torneo->fechafinal)->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <hr style="margin: 10px 0;">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Persona Responsable</label>
                                        <p class="form-control-static">{{ $torneo->person->first_name }} {{ $torneo->person->paternal_surname }}</p>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Fecha de Registro</label>
                                        <p class="form-control-static">{{ \Carbon\Carbon::parse($torneo->created_at)->format('d/m/Y H:i:s') }}</p>
                                    </div>
                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop