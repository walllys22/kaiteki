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
        if ($torneo->archivo && Str::endsWith($torneo->archivo, ['.jpg', '.jpeg', '.png', '.webp', '.avif'])) {
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
                                @if ($torneo->archivo && Str::endsWith($torneo->archivo, ['.jpg', '.jpeg', '.png', '.webp', '.avif']))
                                    <img src="{{ $image }}"
                                        style="width:100%; max-width:300px; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                                @else
                                    <div
                                        style="width:100%; height:200px; background-color: #f8f9fa; display:flex; align-items:center; justify-content:center; border: 1px solid #ddd; border-radius: 5px; color:#999;">
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
                                            @if ($torneo->estado == 'En curso')
                                                <label class="label label-success">{{ $torneo->estado }}</label>
                                            @else
                                                <label class="label label-warning">{{ $torneo->estado }}</label>
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
                                        <p class="form-control-static">
                                            {{ \Carbon\Carbon::parse($torneo->fechainicio)->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="control-label" style="font-weight:bold;">Fecha Final</label>
                                        <p class="form-control-static">
                                            {{ \Carbon\Carbon::parse($torneo->fechafinal)->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <hr style="margin: 10px 0;">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Persona Responsable</label>
                                        <p class="form-control-static">{{ $torneo->person->first_name }}</p>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="control-label" style="font-weight:bold;">Fecha de Registro</label>
                                        <p class="form-control-static">
                                            {{ \Carbon\Carbon::parse($torneo->created_at)->format('d/m/Y H:i:s') }}</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección de Categorías del Torneo --}}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading"
                        style="border-bottom:0; display: flex; align-items: center; justify-content: space-between;">
                        <h3 class="panel-title" style="margin: 0;"><i class="voyager-categories"></i> Categorías del Torneo
                        </h3>
                        <div style="padding-right: 20px;">
                            @if (auth()->user()->hasPermission('add_torneos'))
                                <button class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#modal-add-category">
                                    <i class="voyager-plus"></i> <span>Agregar</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="dataTables_length">
                                    <label>Mostrar <select id="select-paginate-cat" class="form-control input-sm">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search-cat" placeholder="🔍 Buscar..."
                                    class="form-control">
                            </div>
                        </div>
                        <div id="div-categories-list">
                            <p class="text-center">Cargando categorías...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección de Dojos del Torneo --}}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading"
                        style="border-bottom:0; display: flex; align-items: center; justify-content: space-between;">
                        <h3 class="panel-title" style="margin: 0;"><i class="voyager-categories"></i> Dojos del Torneo
                        </h3>
                        <div style="padding-right: 20px;">
                            @if (auth()->user()->hasPermission('add_torneos'))
                                <button class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#modal-add-dojos">
                                    <i class="voyager-plus"></i> <span>Agregar</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="dataTables_length">
                                    <label>Mostrar <select id="select-paginate-cat" class="form-control input-sm">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search-cat" placeholder="🔍 Buscar..."
                                    class="form-control">
                            </div>
                        </div>
                        <div id="div-dojos-list">
                            <p class="text-center">Cargando Dojos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para agregar categoría --}}
    <form id="form-add-category" class="form-edit-add" action="{{ route('torneos.categories.store') }}" method="POST">
        @csrf
        <div class="modal fade" tabindex="-1" id="modal-add-category" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Categoría</h4>
                    </div>

                    <input type="hidden" name="torneo_id" value="{{ $torneo->id }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="categoria_id">Categoría</label>
                            <select name="categoria_id" class="form-control select2" required>
                                <option value="">Seleccione una categoría</option>
                                @foreach ($categorias as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }} - {{ $item->sexo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modalidad_id">Modalidad</label>
                            <select name="modalidad_id" class="form-control select2" required>
                                <option value="">Seleccione una modalidad</option>
                                @foreach ($modalidades as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-submit">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal para agregar Dojos --}}
    <form id="form-add-tutor" class="form-edit-add" action="{{ route('torneos.torneosDojosStore.store') }}" method="POST">
        @csrf
        <div class="modal fade" tabindex="-1" id="modal-add-dojos" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-plus"></i> Agregar Dojos</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="torneo_id" value="{{ $torneo->id }}">
                        {{-- lista el dojo --}}
                        <div class="form-group col-md-6">
                            <label for="dojo_id">Nombre del Dojo</label>
                            <select name="dojo_id" class="form-control select2" required>
                                <option value="">Seleccione un Dojo</option>
                                @foreach ($dojos as $dojo)
                                    <option value="{{ $dojo->id }}">
                                        {{ $dojo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-submit">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    @include('partials.modal-delete')

@stop

@section('javascript')
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    <script>
        var countPageCat = 10;
        var timeoutCat = null;

        $(document).ready(function() {
            listCategories();
            listDojos();


            $('#input-search-cat').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    clearTimeout(timeoutCat);
                    listCategories();
                }
            });

            $('#select-paginate-cat').change(function() {
                countPageCat = $(this).val();
                listCategories();
            });

            $('#input-search-cat').on('input', function() {
                clearTimeout(timeoutCat);
                timeoutCat = setTimeout(function() {
                    listCategories();
                }, 1000); // Retardo de 1 segundo para la búsqueda automática
            });


        });

        function listCategories(page = 1) {
            $('#div-categories-list').loading({
                message: 'Cargando...'
            });
            let id = "{{ $torneo->id }}";
            let search = $('#input-search-cat').val() ? $('#input-search-cat').val() : '';
            // URL corregida según la ruta: torneos/{id}/categories/list
            let url = "{{ url('admin/torneos') }}/" + id + "/categories/list";

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPageCat}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $("#div-categories-list").html(result);
                    $('#div-categories-list').loading('toggle');
                },
                error: function(err) {
                    $("#div-categories-list").html(
                        '<p class="text-center">No se pudieron cargar las categorías.</p>');
                    $('#div-categories-list').loading('toggle');
                }
            });
        }

        function deleteItem(url) {
            $('#delete_form').attr('action', url);
        }

        function listDojos(page = 1) {
            $('#div-dojos-list').loading({
                message: 'Cargando...'
            });
            let id = "{{ $torneo->id }}";
            let search = $('#input-search-cat').val() ? $('#input-search-cat').val() : '';
            // URL corregida según la ruta: torneos/{id}/dojos/list
            let url = "{{ url('admin/torneos') }}/" + id + "/dojos/list";

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPageCat}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $("#div-dojos-list").html(result);
                    $('#div-dojos-list').loading('toggle');
                },
                error: function(err) {
                    $("#div-dojos-list").html('<p class="text-center">No se pudieron cargar los dojos.</p>');
                    $('#div-dojos-list').loading('toggle');
                }
            });
        }
    </script>
@stop
