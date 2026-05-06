@extends('voyager::master')

@section('page_title', 'Consulta Inter-Dojo')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .consulta-panel { border-color: #3498db !important; }
        .consulta-panel .panel-heading { background: linear-gradient(90deg, #ebf5fb, #fafcfe); border-bottom: 1px solid #d6eaf8; }
        .alumno-row { cursor: pointer; transition: background .15s; }
        .alumno-row:hover td { background: #eef6ff !important; }
        .badge-activo   { background: #27ae60; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .badge-inactivo { background: #e67e22; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        #tabla-resultados { display: none; }
        #sin-resultados   { display: none; }
        #cargando         { display: none; }
    </style>
@stop

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding:0;">
                        <div class="col-md-6" style="padding:0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-magnifying-glass-location"></i> Consulta Inter-Dojo
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="page-content container-fluid">

    <div class="panel panel-bordered consulta-panel">
        <div class="panel-heading" style="padding:12px 15px;">
            <h4 style="margin:0; font-weight:700;">
                <i class="fa-solid fa-building" style="color:#3498db;"></i>
                Seleccionar Dojo a Consultar
            </h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Dojo / Sucursal <span class="text-danger">*</span></label>
                        <select id="select-dojo" class="form-control select2" style="width:100%;">
                            <option value="">— Seleccione un dojo —</option>
                            @foreach($dojos as $dojo)
                                <option value="{{ $dojo->id }}">{{ $dojo->nombre }}</option>
                            @endforeach
                        </select>
                        @if($dojos->isEmpty())
                            <small class="text-warning">No hay otros dojos disponibles para consultar.</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Buscar Alumno</label>
                        <input type="text" id="input-buscar" class="form-control" placeholder="Nombre o CI del alumno..." disabled>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button id="btn-buscar" class="btn btn-primary" disabled>
                            <i class="fa-solid fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Indicador de carga --}}
    <div id="cargando" class="text-center" style="padding:20px;">
        <i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#3498db;"></i>
        <p class="text-muted" style="margin-top:8px;">Buscando alumnos...</p>
    </div>

    {{-- Sin resultados --}}
    <div id="sin-resultados" class="alert alert-info">
        <i class="fa-solid fa-circle-info"></i> No se encontraron alumnos con esos criterios en el dojo seleccionado.
    </div>

    {{-- Tabla de resultados --}}
    <div id="tabla-resultados">
        <div class="panel panel-bordered">
            <div class="panel-heading" style="padding:10px 15px;">
                <h5 style="margin:0; font-weight:600;">
                    <i class="fa-solid fa-users"></i>
                    Alumnos encontrados
                    <span id="count-badge" class="badge" style="margin-left:6px; background:#3498db;"></span>
                </h5>
            </div>
            <div class="panel-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="margin:0; font-size:13px;">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th style="width:130px;">CI / Documento</th>
                                <th>Grado Actual</th>
                                <th style="width:90px; text-align:center;">Estado</th>
                                <th style="width:80px; text-align:center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-alumnos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('javascript')
<script>
$(function () {
    $('#select-dojo').select2({ placeholder: '— Seleccione un dojo —', allowClear: true });

    $('#select-dojo').on('change', function () {
        var dojoId = $(this).val();
        if (dojoId) {
            $('#input-buscar').prop('disabled', false);
            $('#btn-buscar').prop('disabled', false);
        } else {
            $('#input-buscar').prop('disabled', true).val('');
            $('#btn-buscar').prop('disabled', true);
            ocultarResultados();
        }
    });

    $('#input-buscar').on('keypress', function (e) {
        if (e.which === 13) buscar();
    });

    $('#btn-buscar').on('click', buscar);

    function buscar() {
        var dojoId = $('#select-dojo').val();
        if (!dojoId) return;

        var q = $('#input-buscar').val().trim();

        ocultarResultados();
        $('#cargando').show();

        $.ajax({
            url: '{{ route("consulta.search") }}',
            data: { dojo_id: dojoId, q: q },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                $('#cargando').hide();
                if (!data || data.length === 0) {
                    $('#sin-resultados').show();
                    return;
                }
                renderTabla(data);
            },
            error: function (xhr) {
                $('#cargando').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error al buscar.';
                toastr.error(msg);
            }
        });
    }

    function renderTabla(alumnos) {
        var tbody = $('#tbody-alumnos').empty();

        alumnos.forEach(function (a) {
            var badge = a.status == 1
                ? '<span class="badge-activo">Activo</span>'
                : '<span class="badge-inactivo">Inactivo</span>';

            var url = '{{ route("consulta.show", ":id") }}'.replace(':id', a.id);

            tbody.append(
                '<tr class="alumno-row">' +
                '<td><strong>' + escHtml(a.nombre) + '</strong></td>' +
                '<td>' + escHtml(a.ci) + '</td>' +
                '<td>' + escHtml(a.grado) + '</td>' +
                '<td style="text-align:center;">' + badge + '</td>' +
                '<td style="text-align:center;">' +
                    '<a href="' + url + '" class="btn btn-info btn-xs"><i class="fa-solid fa-eye"></i> Ver</a>' +
                '</td>' +
                '</tr>'
            );
        });

        $('#count-badge').text(alumnos.length);
        $('#tabla-resultados').show();
    }

    function ocultarResultados() {
        $('#tabla-resultados').hide();
        $('#sin-resultados').hide();
        $('#tbody-alumnos').empty();
    }

    function escHtml(str) {
        return $('<div>').text(str).html();
    }
});
</script>
@stop
