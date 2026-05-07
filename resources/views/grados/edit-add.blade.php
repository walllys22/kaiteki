@extends('voyager::master')

@section('page_title', 'Editar Grado')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding:0;">
                        <div class="col-md-6" style="padding:0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-bezier-curve"></i> Editar Grado
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top:30px;">
                            <a href="{{ route('voyager.grados.show', $grado->id) }}" class="btn btn-warning btn-sm">
                                <i class="voyager-eye"></i> Volver al Grado
                            </a>
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

                    <form action="{{ route('voyager.grados.update', $grado->id) }}"
                          method="POST" enctype="multipart/form-data" class="form-edit-add">
                        @csrf
                        @method('PUT')

                        {{-- Imagen --}}
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Imagen del Grado</label>
                                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                                    @if($grado->image)
                                        @php
                                            $imgSrc = asset('storage/' . str_replace('.avif', '', $grado->image) . '-cropped.webp');
                                        @endphp
                                        <img src="{{ $imgSrc }}"
                                             onerror="this.style.display='none'"
                                             style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                                    @else
                                        <div style="width:80px; height:80px; border-radius:8px; border:1px dashed #ccc;
                                                    display:flex; align-items:center; justify-content:center; color:#bbb; font-size:28px;">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <input type="file" name="image" accept="image/*" class="form-control" style="max-width:340px;">
                                        <small class="text-muted">JPEG, PNG, WEBP — máx. 3 MB. Dejar vacío para conservar la actual.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Tipo</label>
                                <input type="text" name="tipo" class="form-control"
                                       value="{{ old('tipo', $grado->tipo) }}"
                                       placeholder="Ej: Kyu, Dan...">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Número</label>
                                <input type="text" name="numero" class="form-control"
                                       value="{{ old('numero', $grado->numero) }}"
                                       placeholder="Ej: 10mo.">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Nombre</label>
                                <input type="text" name="nombre" class="form-control"
                                       value="{{ old('nombre', $grado->nombre) }}"
                                       placeholder="Ej: Cinturón Blanco">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Puntas requeridas <span class="text-danger">*</span></label>
                                <input type="number" name="puntas" class="form-control" min="0"
                                       value="{{ old('puntas', $grado->puntas) }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Días requeridos <span class="text-danger">*</span></label>
                                <input type="number" name="dias" class="form-control" min="0"
                                       value="{{ old('dias', $grado->dias) }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Estado</label><br>
                                <input type="checkbox" name="status" class="toggleswitch"
                                       {{ old('status', $grado->status) ? 'checked' : '' }}
                                       data-on="Activo" data-off="Inactivo">
                            </div>
                        </div>

                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12 text-right">
                                <a href="{{ route('voyager.grados.show', $grado->id) }}" class="btn btn-default">Cancelar</a>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <i class="voyager-edit"></i> Actualizar Grado
                                </button>
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
    });
</script>
@stop
