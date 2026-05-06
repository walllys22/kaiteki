@extends('voyager::master')

@php $editing = isset($horario); @endphp

@section('page_title', $editing ? 'Editar Horario' : 'Crear Horario')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding:0;">
                        <div class="col-md-6" style="padding:0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-clock"></i>
                                {{ $editing ? 'Editar Horario' : 'Crear Horario' }}
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top:30px;">
                            @if($editing)
                                <a href="{{ route('voyager.horarios.show', $horario->id) }}" class="btn btn-warning btn-sm">
                                    <i class="voyager-eye"></i> Volver al Horario
                                </a>
                            @else
                                <a href="{{ route('voyager.horarios.index') }}" class="btn btn-warning btn-sm">
                                    <i class="voyager-list"></i> Volver
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
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-bordered">
                <div class="panel-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin:0; padding-left:18px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ $editing ? route('voyager.horarios.update', $horario->id) : route('voyager.horarios.store') }}"
                          method="POST" class="form-edit-add">
                        @csrf
                        @if($editing)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Dojo / Sucursal <span class="text-danger">*</span></label>
                                @if($userDojoId)
                                    <input type="text" class="form-control"
                                           value="{{ $dojos->first()->nombre ?? 'Sin dojo asignado' }}" readonly>
                                    <input type="hidden" name="dojo_id" value="{{ $userDojoId }}">
                                @else
                                    <select name="dojo_id" class="form-control select2" required>
                                        <option value="">— Seleccione un dojo —</option>
                                        @foreach($dojos as $dojo)
                                            <option value="{{ $dojo->id }}"
                                                {{ old('dojo_id', $editing ? $horario->dojo_id : '') == $dojo->id ? 'selected' : '' }}>
                                                {{ $dojo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control"
                                       value="{{ old('nombre', $editing ? $horario->nombre : '') }}"
                                       placeholder="Ej: Horario Mañana" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach(['Mañana', 'Tarde', 'Noche'] as $tipo)
                                        <option value="{{ $tipo }}"
                                            {{ old('tipo', $editing ? $horario->tipo : '') == $tipo ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($editing)
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Estado</label><br>
                                <input type="checkbox" name="status" class="toggleswitch"
                                       {{ old('status', $horario->status) ? 'checked' : '' }}
                                       data-on="Activo" data-off="Inactivo">
                            </div>
                        </div>
                        @endif

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12 text-right">
                                @if($editing)
                                    <a href="{{ route('voyager.horarios.show', $horario->id) }}" class="btn btn-default">Cancelar</a>
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <i class="voyager-edit"></i> Actualizar Horario
                                    </button>
                                @else
                                    <a href="{{ route('voyager.horarios.index') }}" class="btn btn-default">Cancelar</a>
                                    <button type="submit" class="btn btn-success btn-submit">
                                        <i class="voyager-plus"></i> Guardar Horario
                                    </button>
                                @endif
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script src="{{ asset('js/btn-submit.js') }}"></script>
<script>
    $('document').ready(function () {
        $('.toggleswitch').bootstrapToggle();
        $('.select2').select2();
    });
</script>
@stop
