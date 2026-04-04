@extends('voyager::master')

@section('page_title', 'Ver personas')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-6" style="padding: 0px; display: flex; align-items: center;">
                            <h1 class="page-title">
                                <i class="voyager-people"></i> Detalles de la Persona
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px">
                            <a href="{{ route('voyager.people.index') }}" class="btn btn-warning btn-sm">
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
        // Compatibilidad: Voyager usa $dataTypeContent por defecto, pero si pasas $person desde un controlador custom también funcionará.
        $person = $dataTypeContent ?? $person ?? new \App\Models\Person();
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <div class="row">
                        {{-- Columna de Imagen --}}
                        <div class="col-md-3">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Fotografía</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                @if(!empty($person->image))
                                    <img src="{{ filter_var($person->image, FILTER_VALIDATE_URL) ? $person->image : Voyager::image($person->image) }}" style="width:100%; max-width:300px; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                                @else
                                    <div style="width:100%; height:200px; background-color: #f8f9fa; display:flex; align-items:center; justify-content:center; border: 1px solid #ddd; border-radius: 5px; color:#999;">
                                        <i class="voyager-person" style="font-size: 50px;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Columna de Datos --}}
                        <div class="col-md-9">
                            <div class="panel-heading" style="border-bottom:0;">
                                <h3 class="panel-title">Información Personal</h3>
                            </div>
                            <div class="panel-body" style="padding-top:0;">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Documento de Identidad</label>
                                        <p class="form-control-static">{{$person->documentType}}: {{ $person->ci }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Nombre Completo</label>
                                        <p class="form-control-static">
                                            {{ $person->first_name }} {{ $person->middle_name }} {{ $person->paternal_surname }} {{ $person->maternal_surname }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Fecha de Nacimiento</label>
                                        <p class="form-control-static">{{ $person->birth_date }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Género</label>
                                        <p class="form-control-static">{{ $person->gender }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Email</label>
                                        <p class="form-control-static">{{ $person->email??'N/A' }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Teléfono</label>
                                        @php
                                            $countryCodes = ['591' => 'bo', '54' => 'ar', '55' => 'br', '56' => 'cl', '51' => 'pe', '1' => 'us', '34' => 'es'];
                                            $flag = $countryCodes[$person->country_code] ?? 'bo';
                                        @endphp
                                        <p class="form-control-static"><span class="fi fi-{{ $flag }}"></span> {{ $person->phone }}</p>
                                    </div>
                                </div>
                                <hr style="margin: 10px 0;">
                                <div class="row">
                                    <div class="col-md-12 form-group">
                                        <label class="control-label" style="font-weight:bold;">Dirección</label>
                                        <p class="form-control-static">{{ $person->address }}</p>
                                    </div>
                                </div>
                                <hr style="margin: 10px 0;">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Estado</label>
                                        <p class="form-control-static">
                                            @if($person->status == '1' || strtolower($person->status) == 'activo')
                                                <span class="label label-success">Activo</span>
                                            @else
                                                <span class="label label-default">{{ $person->status }}</span>
                                            @endif
                                        </p>
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