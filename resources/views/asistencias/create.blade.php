@extends('voyager::master')

@section('page_title', 'Nueva Asistencia')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0;">
                        <div class="col-md-6" style="padding: 0;">
                            <h1 class="page-title">
                                <i class="fa-solid fa-clipboard-user"></i> Nueva Asistencia
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px;">
                            <a href="{{ route('voyager.asistencias.index') }}" class="btn btn-warning btn-sm">
                                <i class="voyager-list"></i> Volver
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
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">

                        {{-- Fila 1: Dojo --}}
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Dojo <span class="text-danger">*</span></label>
                                @if($userDojoId)
                                    {{-- Usuario de sucursal: dojo fijo --}}
                                    <input type="text" class="form-control" value="{{ optional($dojos->first())->nombre ?? '—' }}" disabled>
                                    <input type="hidden" id="select-dojo" value="{{ $userDojoId }}">
                                @else
                                    {{-- Admin global: puede elegir --}}
                                    <select id="select-dojo" class="form-control select2">
                                        <option value="">— Seleccione un dojo —</option>
                                        @foreach($dojos as $d)
                                            <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                
                            <div class="col-md-2 form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" id="input-fecha" class="form-control"
                                       value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Horario <span class="text-danger">*</span></label>
                                <select id="select-horario" class="form-control">
                                    <option value="">— Seleccione un horario —</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group" style="padding-top:25px;">
                                <button id="btn-cargar" class="btn btn-primary" type="button" disabled>
                                    <i class="fa-solid fa-magnifying-glass"></i> Cargar alumnos
                                </button>
                            </div>
                        </div>

                        {{-- Mensaje de error/validación --}}
                        <div id="div-error" class="alert alert-danger" style="display:none;"></div>

                        {{-- Formulario de asistencia (se muestra al cargar alumnos) --}}
                        <form id="form-asistencia" action="{{ route('voyager.asistencias.store') }}"
                              method="POST" style="display:none;">
                            @csrf
                            <input type="hidden" name="horario_id" id="hidden-horario-id">
                            <input type="hidden" name="fecha"      id="hidden-fecha">
                            <input type="hidden" name="dojo_id"    id="hidden-dojo-id">

                            <div id="div-alumnos"></div>

                            <div class="form-group">
                                <label>Observaciones</label>
                                <textarea name="observacion" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="form-group text-right">
                                <a href="{{ route('voyager.asistencias.index') }}" class="btn btn-default">Cancelar</a>
                                <button type="submit" class="btn btn-success btn-submit">
                                    <i class="fa-solid fa-save"></i> Guardar Asistencia
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .asistencia-table th { background: #f7fafd; font-size: 13px; }
        .asistencia-table td { vertical-align: middle; font-size: 13px; }
        .estado-radio { display: none; }
        .estado-label {
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 12px;
            font-weight: 600;
            user-select: none;
            transition: all .15s;
        }
        .estado-radio:checked + .estado-label { color: #fff; border-color: transparent; }
        .radio-asistencia:checked + .estado-label { background: #27ae60; }
        .radio-licencia:checked  + .estado-label { background: #e67e22; }
        .radio-falta:checked    + .estado-label { background: #c0392b; }
        .estado-label:hover { opacity: .85; }
    </style>
@stop

@section('javascript')
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    @php
        $horariosJson = $horarios->map(function($h) {
            return ['id' => $h->id, 'nombre' => $h->nombre, 'tipo' => $h->tipo, 'dojo_id' => $h->dojo_id];
        })->values();
    @endphp
    <script>
        var loadUrl  = "{{ route('asistencias.load_alumnos') }}";
        var isGlobal = {{ $userDojoId ? 'false' : 'true' }};

        var allHorarios = @json($horariosJson);

        $(document).ready(function() {
            if (isGlobal) {
                $('#select-dojo').select2({ width: '100%' });
                $('#select-dojo').on('change', function() {
                    filterHorarios($(this).val());
                    resetForm();
                    checkReady();
                });
            }

            // Cargar horarios al inicio (filtra por dojo del usuario o deja vacío para admin global)
            filterHorarios($('#select-dojo').val());

            $('#input-fecha, #select-horario').on('change', function() {
                resetForm();
                checkReady();
            });

            $('#btn-cargar').on('click', function() {
                var fecha   = $('#input-fecha').val();
                var horario = $('#select-horario').val();
                var dojo    = $('#select-dojo').val();

                $('#div-error').hide().text('');
                $('#form-asistencia').hide();
                $('#btn-cargar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cargando...');

                $.ajax({
                    url: loadUrl,
                    data: { horario_id: horario, fecha: fecha, dojo_id: dojo },
                    success: function(data) {
                        if (!data.alumnos || data.alumnos.length === 0) {
                            $('#div-error').text('No hay alumnos asignados a ese horario en este dojo.').show();
                            return;
                        }
                        renderAlumnos(data.alumnos);
                        $('#hidden-horario-id').val(horario);
                        $('#hidden-fecha').val(fecha);
                        $('#hidden-dojo-id').val(dojo);
                        $('#form-asistencia').show();
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : 'Error al cargar los alumnos.';
                        $('#div-error').text(msg).show();
                    },
                    complete: function() {
                        $('#btn-cargar').prop('disabled', false).html('<i class="fa-solid fa-magnifying-glass"></i> Cargar alumnos');
                        checkReady();
                    }
                });
            });

            checkReady();
        });

        function filterHorarios(dojoId) {
            var $select = $('#select-horario');

            try { $select.select2('destroy'); } catch(e) {}
            $select.empty().append('<option value="">— Seleccione un horario —</option>');

            var filtrados = dojoId
                ? allHorarios.filter(function(h) { return String(h.dojo_id) === String(dojoId); })
                : allHorarios;

            filtrados.forEach(function(h) {
                var label = h.nombre;
                if (h.tipo) label += ' · ' + h.tipo;
                $select.append(new Option(label, h.id, false, false));
            });

            $select.select2({ width: '100%' });
            checkReady();
        }

        function checkReady() {
            var fecha   = $('#input-fecha').val();
            var horario = $('#select-horario').val();
            var dojo    = $('#select-dojo').val();
            var ok      = fecha && horario && dojo;
            $('#btn-cargar').prop('disabled', !ok);
        }

        function resetForm() {
            $('#form-asistencia').hide();
            $('#div-alumnos').empty();
            $('#div-error').hide().text('');
        }

        function renderAlumnos(alumnos) {
            var html = '<div class="table-responsive" style="margin-bottom:20px;">'
                     + '<table class="table table-bordered table-hover asistencia-table">'
                     + '<thead><tr>'
                     + '<th style="width:40px;">#</th><th>Alumno</th>'
                     + '<th style="text-align:center; width:130px;">Asistencia</th>'
                     + '<th style="text-align:center; width:120px;">Licencia</th>'
                     + '<th style="text-align:center; width:100px;">Falta</th>'
                     + '</tr></thead><tbody>';

            alumnos.forEach(function(a, idx) {
                var n = a.id;
                html += '<tr>'
                      + '<td>' + (idx + 1) + '</td>'
                      + '<td><strong>' + a.first_name + '</strong></td>'
                      + '<td style="text-align:center;">'
                      +   '<input type="radio" class="estado-radio radio-asistencia" name="estados[' + n + ']" id="a_' + n + '" value="asistencia" checked>'
                      +   '<label class="estado-label" for="a_' + n + '" style="color:#27ae60; border-color:#27ae60;">✓ Asistencia</label>'
                      + '</td>'
                      + '<td style="text-align:center;">'
                      +   '<input type="radio" class="estado-radio radio-licencia" name="estados[' + n + ']" id="l_' + n + '" value="licencia">'
                      +   '<label class="estado-label" for="l_' + n + '" style="color:#e67e22; border-color:#e67e22;">📋 Licencia</label>'
                      + '</td>'
                      + '<td style="text-align:center;">'
                      +   '<input type="radio" class="estado-radio radio-falta" name="estados[' + n + ']" id="f_' + n + '" value="falta">'
                      +   '<label class="estado-label" for="f_' + n + '" style="color:#c0392b; border-color:#c0392b;">✗ Falta</label>'
                      + '</td>'
                      + '</tr>';
            });

            html += '</tbody></table></div>';
            $('#div-alumnos').html(html);
        }
    </script>
@stop
