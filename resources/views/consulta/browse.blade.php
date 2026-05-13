@extends('voyager::master')

@section('page_title', 'Consulta Inter-Dojo')

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
<div class="page-content browse container-fluid">
    <div class="row">
        <div class="col-md-12">

            {{-- Buscador --}}
            <div class="panel panel-bordered">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa-solid fa-user"></i></span>
                                <input type="text"
                                       id="input-buscar"
                                       class="form-control"
                                       placeholder="Buscar por nombre o número de CI..."
                                       autocomplete="off">
                            </div>
                            <p class="help-block" style="margin-top:6px; margin-bottom:0;">
                                <i class="fa-solid fa-circle-info"></i>
                                Buscá en todos los dojos excepto el tuyo.
                            </p>
                        </div>
                        <div class="col-sm-3">
                            <button id="btn-buscar" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    @if($dojos->isEmpty())
                        <div class="alert alert-warning" style="margin-top:12px; margin-bottom:0;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            No hay otros dojos disponibles para consultar.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cargando --}}
            <div id="cargando" style="display:none;">
                <div class="panel panel-bordered">
                    <div class="panel-body text-center" style="padding:40px;">
                        <i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="text-muted" style="margin-top:10px; margin-bottom:0;">Buscando alumnos...</p>
                    </div>
                </div>
            </div>

            {{-- Sin resultados --}}
            <div id="sin-resultados" style="display:none;">
                <div class="panel panel-bordered">
                    <div class="panel-body text-center" style="padding:40px;">
                        <i class="fa-solid fa-magnifying-glass fa-2x text-muted"></i>
                        <p class="text-muted" style="margin-top:10px; margin-bottom:0;">No se encontraron alumnos con ese nombre o CI.</p>
                    </div>
                </div>
            </div>

            {{-- Estado inicial --}}
            <div id="estado-inicial">
                <div class="panel panel-bordered">
                    <div class="panel-body text-center" style="padding:40px;">
                        <i class="fa-solid fa-users-viewfinder fa-2x text-muted"></i>
                        <p class="text-muted" style="margin-top:10px; margin-bottom:0;">Ingresá un nombre o CI para comenzar la búsqueda.</p>
                    </div>
                </div>
            </div>

            {{-- Resultados --}}
            <div id="panel-resultados" style="display:none;">
                <div class="panel panel-bordered">
                    <div class="panel-heading" style="display:flex; align-items:center; justify-content:space-between;">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-users"></i> Resultados de la búsqueda
                        </h3>
                        <span id="count-badge" class="badge" style="font-size:12px;"></span>
                    </div>
                    <div class="panel-body" style="padding:0;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="margin:0;">
                                <thead>
                                    <tr>
                                        <th>Alumno</th>
                                        <th style="width:130px;">CI</th>
                                        <th>Dojo</th>
                                        <th>Grado Actual</th>
                                        <th style="width:90px; text-align:center;">Estado</th>
                                        <th style="width:70px; text-align:center;">Ver</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-alumnos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('javascript')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
$(function () {
    $('#input-buscar').on('keypress', function (e) {
        if (e.which === 13) buscar();
    });

    $('#btn-buscar').on('click', buscar);

    function buscar() {
        var q = $('#input-buscar').val().trim();
        if (!q) {
            toastr.warning('Ingresá un nombre o CI para buscar.');
            return;
        }

        setEstado('cargando');

        $.ajax({
            url: '{{ route("consulta.search") }}',
            data: { q: q },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                if (!data || data.length === 0) {
                    setEstado('vacio');
                } else {
                    renderTabla(data);
                    setEstado('resultados');
                }
            },
            error: function (xhr) {
                setEstado('inicial');
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error al realizar la búsqueda.';
                toastr.error(msg);
            }
        });
    }

    function renderTabla(alumnos) {
        var tbody = $('#tbody-alumnos').empty();

        alumnos.forEach(function (a) {
            var estado = a.status == 1
                ? '<span class="label label-success">Activo</span>'
                : '<span class="label label-warning">Inactivo</span>';

            var url = '{{ route("consulta.show", ":id") }}'.replace(':id', a.id);

            tbody.append(
                '<tr style="cursor:pointer;" onclick="window.location=\'' + url + '\'">' +
                '<td><strong>' + esc(a.nombre) + '</strong></td>' +
                '<td>' + esc(a.ci) + '</td>' +
                '<td>' + esc(a.dojo) + '</td>' +
                '<td>' + esc(a.grado) + '</td>' +
                '<td style="text-align:center;">' + estado + '</td>' +
                '<td style="text-align:center;">' +
                    '<a href="' + url + '" class="btn btn-info btn-xs" onclick="event.stopPropagation();">' +
                        '<i class="fa-solid fa-eye"></i>' +
                    '</a>' +
                '</td>' +
                '</tr>'
            );
        });

        var total = alumnos.length;
        $('#count-badge').text(total + (total === 50 ? '+' : '') + ' resultado' + (total !== 1 ? 's' : ''));
    }

    function setEstado(estado) {
        $('#estado-inicial, #cargando, #sin-resultados, #panel-resultados').hide();
        if (estado === 'inicial')    $('#estado-inicial').show();
        if (estado === 'cargando')   $('#cargando').show();
        if (estado === 'vacio')      $('#sin-resultados').show();
        if (estado === 'resultados') $('#panel-resultados').show();
    }

    function esc(str) {
        return $('<div>').text(str || '').html();
    }
});
</script>
@stop
