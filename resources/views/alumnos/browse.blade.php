@extends('voyager::master')

@section('page_title', 'Viendo Alumnos')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-user-graduate"></i> Alumnos
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            <a href="#" class="btn btn-dark" data-toggle="modal" data-target="#modal-print">
                                <i class="fa-solid fa-print"></i> <span>Imprimir</span>
                            </a>
                            <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#modal-print-cumple">
                                <i class="fa-solid fa-cake-candles"></i> <span>Cumpleaños</span>
                            </a>
                            @if (auth()->user()->hasPermission('add_alumnos'))
                                <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modal-add-alumno">
                                    <i class="voyager-plus"></i> <span>Crear</span>
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
    @php
        $userDojoId = auth()->user()->dojo_id;
        $currentDojoId = old('dojo_id', $selectedDojoId ?? $userDojoId);
    @endphp

    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-4" style="margin-bottom: 10px">
                                <select id="filter-estado" class="form-control input-sm" title="Reporte por estado de progresión">
                                    <option value="">— Todos los alumnos —</option>
                                    <option value="repaso">⭐ Listos para repaso/punta</option>
                                    <option value="examen">🎓 Por dar examen</option>
                                </select>
                            </div>
                            <div class="col-sm-4" style="margin-bottom: 10px">
                                <input type="text" id="input-search" placeholder="🔍 Buscar..." class="form-control">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-cannot-delete" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #f0ad4e; color: black;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: black;"><i class="voyager-warning"></i> Advertencia</h4>
                    </div>
                    <div class="modal-body" style="color: black;">
                        <p>El Alumno no se puede eliminar porque tiene un Historial.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-status" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: black;"><i class="fa-solid fa-person-circle-xmark"></i> Cambio de estado del Alumno</h4>
                    </div>
                    <div class="modal-body" style="color: black;">
                        <p><strong>Alumno:</strong> <span id="status-alumno-name"></span></p>
                        <p><strong>Dojo:</strong> <span id="status-alumno-dojo"></span></p>
                        <p><strong>Estado actual:</strong> <span id="status-alumno-current"></span></p>
                    </div>
                    <div class="modal-footer" style="text-align: center; color: black;">
                        <p id="status-alumno-question">Esta seguro de cambiar el estado del alumno</p>
                        <br>
                        <form action="#" id="status_form" method="POST">
                            @csrf
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark" id="status-submit-button">Cambiar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-danger fade" tabindex="-1" id="modal-delete" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> ¿Estás seguro de que quieres eliminar esto?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete_form" method="POST">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar esto">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-success fade" tabindex="-1" id="modal-print" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: white;"><i class="fa-solid fa-print"></i> Lista de Alumnos por Grado</h4>
                    </div>
                    <div class="modal-body">
                        @if(!$userDojoId)
                        <div class="form-group">
                            <label style="color: #333;">Sucursal / Dojo</label>
                            <select id="print_dojo_id" class="form-control">
                                <option value="">— Todos los Dojos —</option>
                                @foreach (\App\Models\Dojo::whereNull('deleted_at')->orderBy('nombre')->get() as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="print_dojo_id" value="{{ $userDojoId }}">
                        @endif

                        <div class="form-group">
                            <label style="color: #333;">Grado actual del alumno</label>
                            <select id="print_grado_id" class="form-control">
                                <option value="">— Todos los grados —</option>
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado->id }}">
                                        {{ trim(($grado->tipo ?? '') . ' ' . ($grado->numero ?? '') . ' ' . ($grado->nombre ?? '')) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btn-print-confirm">
                            <i class="fa-solid fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-print-cumple" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: white;"><i class="fa-solid fa-cake-candles"></i> Lista de Cumpleaños</h4>
                    </div>
                    <div class="modal-body">

                        <div class="alert alert-info" style="font-size:12px; margin-bottom:12px;">
                            <i class="fa fa-info-circle"></i>
                            El rango se aplica por <strong>día y mes</strong>: trae a todos los alumnos que cumplen
                            años en esas fechas, sin importar el año de nacimiento. Se puede cruzar el fin de año
                            (ej. 15/12 al 15/01).
                        </div>

                        {{-- La lista de cumpleaños siempre sale de UNA sucursal.
                             Usuario de sucursal y rol administrador quedan atados
                             a su dojo; solo el rol admin elige, y sin opción "todos". --}}
                        @if(!$userDojoId)
                        <div class="form-group">
                            <label style="color: #333;">Sucursal / Dojo <span style="color:#c0392b;">*</span></label>
                            <select id="cumple_dojo_id" class="form-control">
                                @foreach (\App\Models\Dojo::whereNull('deleted_at')->orderBy('nombre')->get() as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="form-group">
                            <label style="color: #333;">Sucursal / Dojo</label>
                            <p class="form-control-static" style="color:#333; font-weight:bold; margin:0;">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ optional(\App\Models\Dojo::find($userDojoId))->nombre ?: 'Sucursal asignada' }}
                            </p>
                        </div>
                        <input type="hidden" id="cumple_dojo_id" value="{{ $userDojoId }}">
                        @endif

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="color: #333;">Desde</label>
                                    <input type="date" id="cumple_desde" class="form-control"
                                           value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="color: #333;">Hasta</label>
                                    <input type="date" id="cumple_hasta" class="form-control"
                                           value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="color: #333;">Rangos rápidos</label><br>
                            <button type="button" class="btn btn-default btn-sm cumple-preset" data-preset="mes">Este mes</button>
                            <button type="button" class="btn btn-default btn-sm cumple-preset" data-preset="mes_siguiente">Mes siguiente</button>
                            <button type="button" class="btn btn-default btn-sm cumple-preset" data-preset="30dias">Próximos 30 días</button>
                            <button type="button" class="btn btn-default btn-sm cumple-preset" data-preset="anio">Todo el año</button>
                        </div>

                        <div class="form-group">
                            <label style="color: #333;">Alumnos a incluir</label>
                            <select id="cumple_estado" class="form-control">
                                <option value="1">Solo activos</option>
                                <option value="0">Solo inactivos</option>
                                <option value="todos">Activos e inactivos</option>
                            </select>
                        </div>

                        <div id="cumple-alert" class="alert alert-danger" style="display:none; margin-bottom:0;">
                            <i class="fa fa-exclamation-triangle"></i> <span id="cumple-alert-msg"></span>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" id="btn-print-cumple-confirm">
                            <i class="fa-solid fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.modal-registerPerson')

        <div class="modal modal-primary fade" tabindex="-1" id="modal-add-alumno" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="form-add-alumno" action="{{ route('voyager.alumnos.store') }}" method="POST">
                        @csrf

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" style="color: white;">
                                <i class="fa-solid fa-user-graduate"></i> Registrar Alumno
                            </h4>
                        </div>

                        <div class="modal-body">
                            <div id="modal-alumno-alert" class="alert alert-warning" style="display:none; margin-bottom:12px;">
                                <i class="fa fa-exclamation-triangle"></i> <span id="modal-alumno-alert-msg"></span>
                            </div>

                            <div class="row">
                                <input type="hidden" name="dojo_id" id="modal_alumno_dojo_id" value="{{ $currentDojoId }}">

                                <div class="col-md-9 form-group">
                                    <label for="modal_select_person_id">Alumno</label>
                                    <div class="input-group">
                                        <select name="person_id" id="modal_select_person_id" class="form-control" required></select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" title="Nueva persona" style="margin: 0px" type="button" id="btn-open-create-person-from-alumno">
                                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="modal_fechaIngreso">Fecha de Ingreso</label>
                                    <input type="date" id="modal_fechaIngreso" class="form-control" name="fechaIngreso" value="{{ old('fechaIngreso') }}" max="{{ now()->format('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <input type="hidden" name="status" value="1">

                                <div class="col-md-6 form-group">
                                    <label for="modal_grado_id">Grado Inicial <span class="text-danger">*</span></label>
                                    <select name="grado_id" id="modal_grado_id" class="form-control" required>
                                        <option value="">Seleccione un grado</option>
                                        @foreach ($grados as $grado)
                                            <option value="{{ $grado->id }}">
                                                {{ trim(($grado->tipo ?? '').' '.($grado->numero ?? '').' '.($grado->nombre ?? '')) }}
                                                ({{ $grado->puntas }} puntas · {{ $grado->dias }} días)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="modal_horario_id">Horario <span class="text-danger">*</span></label>
                                    <select name="horario_id" id="modal_horario_id" class="form-control" required>
                                        <option value="">— Seleccione un horario —</option>
                                    </select>
                                </div>

                                <div class="col-md-12 form-group">
                                    <label for="modal_observacion">Observaciones</label>
                                    <textarea class="form-control" id="modal_observacion" name="observacion" rows="3" placeholder="Observación adicional">{{ old('observacion') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-submit">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-alumno-exists" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Alumno ya existe en el Dojo <span class="nombre-dojo" style="font-weight: bold;"></span>.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-alumno-other-dojo" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>Alumno registrado en el Dojo <span class="nombre-dojo" style="font-weight: bold;"></span>.</p>
                        <p>Para registrarlo aquí primero debe inactivarse en ese Dojo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-warning fade" tabindex="-1" id="modal-responsible-same-dojo" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Validación</h4>
                    </div>
                    <div class="modal-body">
                        <p>La persona seleccionada es responsable del Dojo <span class="nombre-dojo" style="font-weight: bold;"></span> y no puede registrarse como alumno en esa misma sucursal.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    <script src="{{ asset('js/include/person-select.js') }}"></script>
    <script src="{{ asset('js/include/person-register.js') }}"></script>

    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        var timeout = null;
        var shouldRestoreAlumnoModal = false;
        var alumnoPersonSelected = null;

        @php
            $horariosModalJson = $horarios->map(function($h) {
                return ['id' => $h->id, 'nombre' => $h->nombre, 'tipo' => $h->tipo, 'dojo_id' => $h->dojo_id];
            })->values();
        @endphp
        var allHorariosModal = @json($horariosModalJson);

        function filterHorariosModal(dojoId) {
            var $sel = $('#modal_horario_id');
            $sel.empty().append('<option value="">— Seleccione un horario —</option>');

            if (!dojoId) return; // Sin dojo determinado, dejar vacío

            var lista = allHorariosModal.filter(function(h) {
                return String(h.dojo_id) === String(dojoId);
            });
            lista.forEach(function(h) {
                var label = h.nombre + (h.tipo ? ' · ' + h.tipo : '');
                $sel.append(new Option(label, h.id, false, false));
            });
        }

        function showAlumnoAlert(msg) {
            $('#modal-alumno-alert-msg').html(msg);
            $('#modal-alumno-alert').show();
        }

        function hideAlumnoAlert() {
            $('#modal-alumno-alert').hide().find('#modal-alumno-alert-msg').html('');
        }

        function resetPersonSelect() {
            $('#modal_alumno_dojo_id').val('{{ $userDojoId ?: '' }}');
            $('#modal_select_person_id').val(null).trigger('change');
            filterHorariosModal('{{ $userDojoId ?: '' }}');
        }

        window.getPersonListParams = function () {
            if ('{{ $userDojoId }}') {
                return {
                    dojo_id: '{{ $userDojoId }}'
                };
            }

            return {};
        };

        function initAlumnoPersonSelect() {
            if ($('#modal_select_person_id').hasClass('select2-hidden-accessible')) {
                $('#modal_select_person_id').select2('destroy');
            }

            $('#modal_select_person_id').select2({
                width: '100%',
                dropdownParent: $('#modal-add-alumno'),
                placeholder: '<i class="fa fa-search"></i> Buscar...',
                escapeMarkup: function(markup) {
                    return markup;
                },
                language: {
                    inputTooShort: function (data) {
                        return `Por favor ingrese ${data.minimum - data.input.length} o más caracteres`;
                    },
                    noResults: function () {
                        return `<i class="far fa-frown"></i> No hay resultados encontrados`;
                    }
                },
                quietMillis: 250,
                minimumInputLength: 2,
                ajax: {
                    url: window.personListUrl,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            dojo_id: '{{ $userDojoId }}'
                        };
                    },
                    processResults: function (data) {
                        let results = [];
                        data.map(data => {
                            results.push({
                                ...data,
                                disabled: false
                            });
                        });
                        return { results };
                    },
                    cache: false
                },
                templateResult: formatPersonResult,
                templateSelection: function (opt) {
                    alumnoPersonSelected = opt;
                    window.personSelected = opt;
                    return opt.first_name ? opt.first_name : '<i class="fa fa-search"></i> Buscar... ';
                }
            });
        }

        function validateAlumnoRegistration() {
            const personId = $('#modal_select_person_id').val();

            // Leer directo de select2 para evitar datos desactualizados de alumnoPersonSelected
            const selectedData = $('#modal_select_person_id').select2('data');
            const selectedPerson = (selectedData && selectedData.length && selectedData[0].id) ? selectedData[0] : null;
            const dojoId = '{{ $userDojoId }}' || (selectedPerson ? selectedPerson.dojo_id : null);

            if (!personId || !dojoId) {
                return;
            }

            $('#modal_alumno_dojo_id').val(dojoId);
            filterHorariosModal(dojoId);

            let url = '{{ route('alumnos.check_registration', ['person_id' => 'TEMP_ID']) }}'.replace('TEMP_ID', personId);
            url += '?dojo_id=' + dojoId;

            $.get(url, function(data) {
                var dojo = data.dojo || '';

                if (data.status === 'exists') {
                    showAlumnoAlert('Alumno ya existe en el Dojo <strong>' + dojo + '</strong>.');
                    resetPersonSelect();
                } else if (data.status === 'other_dojo') {
                    showAlumnoAlert('Alumno registrado en el Dojo <strong>' + dojo + '</strong>. Para registrarlo aquí debe inactivarse en ese Dojo.');
                    resetPersonSelect();
                } else if (data.status === 'responsible_same_dojo') {
                    showAlumnoAlert('La persona seleccionada es responsable del Dojo <strong>' + dojo + '</strong> y no puede registrarse como alumno en esa misma sucursal.');
                    resetPersonSelect();
                } else {
                    hideAlumnoAlert();
                }
            });
        }

        $(document).ready(() => {
            list();
            $('.toggleswitch').bootstrapToggle();

            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    clearTimeout(timeout);
                    list();
                }
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
                list();
            });

            $('#filter-estado').change(function(){
                list();
            });

            $('#input-search').on('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    list();
                }, 2000);
            });

            $('#btn-print-confirm').click(function(){
                let dojo_id  = $('#print_dojo_id').val();
                let grado_id = $('#print_grado_id').val();
                let url = '{{ route("alumnos.print") }}';
                let params = [];
                if (dojo_id)  params.push('dojo_id='  + dojo_id);
                if (grado_id) params.push('grado_id=' + grado_id);
                if (params.length) url += '?' + params.join('&');
                window.open(url, '_blank');
                $('#modal-print').modal('hide');
            });

            // ── Lista de cumpleaños ──
            function fmtFecha(d) {
                var mes = String(d.getMonth() + 1).padStart(2, '0');
                var dia = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + mes + '-' + dia;
            }

            $('.cumple-preset').click(function(){
                var preset = $(this).data('preset');
                var hoy = new Date();
                var desde, hasta;

                if (preset === 'mes') {
                    desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    hasta = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                } else if (preset === 'mes_siguiente') {
                    desde = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 1);
                    hasta = new Date(hoy.getFullYear(), hoy.getMonth() + 2, 0);
                } else if (preset === '30dias') {
                    desde = hoy;
                    hasta = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() + 30);
                } else { // anio
                    desde = new Date(hoy.getFullYear(), 0, 1);
                    hasta = new Date(hoy.getFullYear(), 11, 31);
                }

                $('#cumple_desde').val(fmtFecha(desde));
                $('#cumple_hasta').val(fmtFecha(hasta));
                $('#cumple-alert').hide();
            });

            $('#btn-print-cumple-confirm').click(function(){
                var desde   = $('#cumple_desde').val();
                var hasta   = $('#cumple_hasta').val();
                var dojo_id = $('#cumple_dojo_id').val();

                if (!desde || !hasta) {
                    $('#cumple-alert-msg').text('Seleccione la fecha desde y la fecha hasta.');
                    $('#cumple-alert').show();
                    return;
                }
                // La lista siempre es de una sola sucursal.
                if (!dojo_id) {
                    $('#cumple-alert-msg').text('Seleccione la sucursal / dojo.');
                    $('#cumple-alert').show();
                    return;
                }
                $('#cumple-alert').hide();

                var params = [
                    'desde=' + desde,
                    'hasta=' + hasta,
                    'estado=' + $('#cumple_estado').val(),
                    'dojo_id=' + dojo_id
                ];

                window.open('{{ route("alumnos.cumpleanos.print") }}?' + params.join('&'), '_blank');
                $('#modal-print-cumple').modal('hide');
            });

            $('#modal_alumno_dojo_id').on('change', function() {
                $('#modal_select_person_id').val(null).trigger('change');
                filterHorariosModal($(this).val());
            });

            $('#modal_select_person_id').on('change', function() {
                validateAlumnoRegistration();
            });

            $('#btn-open-create-person-from-alumno').on('click', function(e) {
                e.preventDefault();
                shouldRestoreAlumnoModal = true;

                const dojoId = '{{ $userDojoId }}' || (alumnoPersonSelected ? alumnoPersonSelected.dojo_id : null);

                if ($('#modal_person_dojo_id').length) {
                    $('#modal_person_dojo_id').val(dojoId);
                }

                if ($('#modal_person_dojo_select').length && !$('#modal_person_dojo_select').is(':disabled')) {
                    $('#modal_person_dojo_select').val(dojoId).trigger('change');
                }

                $('#modal-add-alumno').modal('hide');
            });

            $('#modal-add-alumno').on('hidden.bs.modal', function() {
                if (shouldRestoreAlumnoModal) {
                    $('#modal-create-person').modal('show');
                }
            });

            $('#modal-create-person').on('hidden.bs.modal', function() {
                if (shouldRestoreAlumnoModal) {
                    $('#modal-add-alumno').modal('show');
                }
            });

            $(document).on('person:created', function(event, person) {
                if (!shouldRestoreAlumnoModal || !person) {
                    return;
                }

                alumnoPersonSelected = person;
                $('#modal_alumno_dojo_id').val(person.dojo_id || '{{ $userDojoId }}');
                const option = new Option(person.first_name, person.id, true, true);
                $('#modal_select_person_id').append(option).trigger('change');
                $('#modal_select_person_id').trigger({
                    type: 'select2:select',
                    params: {
                        data: person
                    }
                });
            });

            $('#modal-add-alumno').on('shown.bs.modal', function() {
                shouldRestoreAlumnoModal = false;
                alumnoPersonSelected = null;
                hideAlumnoAlert();
                $('#modal_alumno_dojo_id').val('{{ $userDojoId ?: '' }}');
                filterHorariosModal('{{ $userDojoId ?: '' }}');
                initAlumnoPersonSelect();
                $('#modal_select_person_id').val(null).trigger('change');
            });
        });

        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});

            let url = '{{ url("admin/alumnos/ajax/list") }}';
            let search = $('#input-search').val() ? $('#input-search').val() : '';
            let estado = $('#filter-estado').val() || '';

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPage}&page=${page}&estado=${estado}`,
                type: 'get',
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                }
            });
        }

        function statusItem(id, name, dojo, status) {
            let url = '{{ route("alumnos.status.update", ["id" => "TEMP_ID"]) }}'.replace('TEMP_ID', id);
            $('#status_form').attr('action', url);
            $('#status-alumno-name').text(name);
            $('#status-alumno-dojo').text(dojo);
            const isActive = String(status) === '1';
            $('#status-alumno-current').text(isActive ? 'Activo' : 'Inactivo');
            $('#status-alumno-question').text(isActive ? 'Esta seguro de deshabilitar al alumno' : 'Esta seguro de habilitar al alumno');
            $('#status-submit-button')
                .text(isActive ? 'Deshabilitar' : 'Habilitar')
                .removeClass('btn-dark btn-success')
                .addClass(isActive ? 'btn-dark' : 'btn-success');
            $('#modal-status').modal('show');
        }

        function deleteItem(alumnoId, url){
            $.ajax({
                url: "{{ route('alumnos.check_historial', ['id' => 'TEMP_ID']) }}".replace('TEMP_ID', alumnoId),
                type: 'GET',
                success: function(response) {
                    if (response.has_historial) {
                        $('#modal-cannot-delete').modal('show');
                    } else {
                        $('#delete_form').attr('action', url);
                        $('#modal-delete').modal('show');
                    }
                },
                error: function() {
                    alert('Ocurrió un error al verificar el historial del alumno. Por favor, inténtelo de nuevo.');
                }
            });
        }
    </script>
@stop
