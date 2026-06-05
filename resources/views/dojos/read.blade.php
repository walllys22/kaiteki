@extends('voyager::master')

@section('page_title', 'Ver Dojo')

@section('page_header')
@stop

@section('css')
<style>
    .dojo-read-page {
        overflow-x: hidden;
        padding-left: 16px;
        padding-right: 16px;
    }
    .dojo-read-page > .row {
        margin-left: 0;
        margin-right: 0;
    }
    .dojo-read-page > .row > [class*="col-"] {
        padding-left: 0;
        padding-right: 0;
    }
    .dojo-read-page .panel {
        border: 0;
        border-radius: 6px;
        box-shadow: 0 4px 14px rgba(31, 45, 61, .06);
        overflow: hidden;
    }
    .dojo-read-page .panel-heading {
        background: #fff;
        border-bottom: 1px solid #edf1f5;
        min-height: 56px;
        padding: 14px 18px;
    }
    .dojo-read-page .table-responsive {
        border: 0;
        margin-bottom: 0;
        overflow-x: hidden;
        width: 100%;
    }
    .dojo-read-page table {
        table-layout: fixed;
        width: 100%;
    }
    .dojo-read-page th,
    .dojo-read-page td {
        white-space: normal !important;
        word-break: break-word;
    }
    .dojo-read-page .dojo-profile-body {
        padding: 20px 24px;
    }
    .dojo-read-page .dojo-profile-layout {
        align-items: flex-start;
        display: flex;
        gap: 22px;
        min-width: 0;
    }
    .dojo-read-page .dojo-profile-logo {
        flex: 0 0 auto;
    }
    .dojo-read-page .dojo-profile-info {
        flex: 1 1 auto;
        min-width: 0;
    }
    .dojo-read-page .dojo-profile-head {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .dojo-read-page .dojo-profile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .dojo-read-page .dojo-profile-grid {
        color: #555;
        display: grid;
        font-size: 13.5px;
        gap: 9px 22px;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    }
    .dojo-read-page .dojo-info-item {
        align-items: flex-start;
        display: flex;
        gap: 8px;
        min-width: 0;
    }
    .dojo-read-page .dojo-info-item span {
        min-width: 0;
    }
    #panel-mensualidades {
        padding: 14px 20px 20px;
    }
    #panel-mensualidades .table > thead > tr > th,
    #panel-mensualidades .table > tbody > tr > td {
        vertical-align: middle;
    }
    #panel-mensualidades .bread-actions {
        min-width: 112px;
        white-space: nowrap !important;
    }
    #panel-mensualidades .bread-actions .btn,
    #panel-mensualidades .form-whatsapp-comprobante .btn {
        height: 34px;
        margin: 2px;
        width: 38px;
    }
    #panel-mensualidades .table .table {
        font-size: 12px;
    }
    #panel-mensualidades .table .table th,
    #panel-mensualidades .table .table td {
        padding: 8px 6px;
    }
    @media (max-width: 900px) {
        .dojo-read-page {
            padding-left: 10px;
            padding-right: 10px;
        }
        .dojo-read-page .dojo-profile-layout {
            flex-direction: column;
        }
        .dojo-read-page .dojo-profile-actions {
            width: 100%;
        }
        .dojo-read-page .dojo-profile-actions .btn {
            flex: 1 1 110px;
        }
    }
</style>
@stop

@section('content')
<div class="page-content container-fluid dojo-read-page">

    {{-- Perfil del dojo --}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered" style="border-left: 4px solid #22a7f0; margin-bottom: 20px;">
                <div class="panel-body dojo-profile-body">
                    <div class="dojo-profile-layout">

                        {{-- Logo --}}
                        <div class="dojo-profile-logo">
                            @if($dojo->logo)
                                <img src="{{ filter_var($dojo->logo, FILTER_VALIDATE_URL) ? $dojo->logo : Voyager::image($dojo->logo) }}"
                                     alt="{{ $dojo->nombre }}"
                                     style="width:100px; height:100px; object-fit:contain; border:1px solid #e5e9ef; border-radius:10px; padding:6px; background:#f8f9fa;">
                            @else
                                <div style="width:100px; height:100px; background:#f0f4f8; border-radius:10px; display:flex; align-items:center; justify-content:center; border:1px solid #e1e8ed;">
                                    <i class="voyager-home" style="font-size:38px; color:#b0bec5;"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="dojo-profile-info">
                            <div class="dojo-profile-head">
                                <div style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                                    <h2 style="margin:0; font-size:22px; font-weight:700; color:#2d3748;">{{ $dojo->nombre ?? 'Sin nombre' }}</h2>
                                    @if($dojo->status == 1)
                                        <span class="label label-success" style="font-size:11px; padding:4px 10px; vertical-align:middle;">Activo</span>
                                    @else
                                        <span class="label label-danger" style="font-size:11px; padding:4px 10px; vertical-align:middle;">Inactivo</span>
                                    @endif
                                </div>
                                <div class="dojo-profile-actions">
                                    @if(auth()->user()->hasPermission('edit_dojos'))
                                        <a href="{{ route('voyager.dojos.edit', $dojo->id) }}" class="btn btn-info btn-sm">
                                            <i class="voyager-edit"></i> Editar
                                        </a>
                                    @endif
                                    <a href="{{ route('voyager.dojos.index') }}" class="btn btn-warning btn-sm">
                                        <i class="voyager-list"></i> Volver
                                    </a>
                                </div>
                            </div>

                            <div class="dojo-profile-grid">
                                @if($dojo->ciudad)
                                <div class="dojo-info-item">
                                    <i class="fa fa-map-marker" style="width:14px; color:#22a7f0; text-align:center;"></i>
                                    <span><strong style="color:#888; font-weight:600;">Ciudad:</strong> {{ $dojo->ciudad->nombre }}</span>
                                </div>
                                @endif
                                @if($dojo->person)
                                <div class="dojo-info-item">
                                    <i class="fa fa-user" style="width:14px; color:#22a7f0; text-align:center;"></i>
                                    <span><strong style="color:#888; font-weight:600;">Responsable:</strong> {{ $dojo->person->first_name }}</span>
                                </div>
                                @endif
                                @if($dojo->grado_responsable)
                                <div class="dojo-info-item">
                                    <i class="fa fa-star" style="width:14px; color:#22a7f0; text-align:center;"></i>
                                    <span><strong style="color:#888; font-weight:600;">Grado:</strong> {{ $dojo->grado_responsable }}</span>
                                </div>
                                @endif
                                @if($dojo->phone)
                                <div class="dojo-info-item">
                                    <i class="fa fa-phone" style="width:14px; color:#22a7f0; text-align:center;"></i>
                                    <span>{{ $dojo->phone }}</span>
                                </div>
                                @endif
                                @if($dojo->email)
                                <div class="dojo-info-item">
                                    <i class="fa fa-envelope" style="width:14px; color:#22a7f0; text-align:center;"></i>
                                    <span>{{ $dojo->email }}</span>
                                </div>
                                @endif
                                @if($dojo->address)
                                <div class="dojo-info-item">
                                    <i class="fa fa-home" style="width:14px; color:#22a7f0; text-align:center;"></i>
                                    <span>{{ $dojo->address }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Usuarios del dojo --}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="panel-title" style="margin:0;">
                        <i class="voyager-people"></i> Usuarios de la Sucursal
                        <span class="label label-primary" style="margin-left:8px;">{{ $users->count() }}</span>
                    </h3>
                </div>
                <div class="panel-body" style="padding:0;">
                    @if($users->isEmpty())
                        <div class="text-center" style="padding: 40px 0;">
                            <i class="voyager-people" style="font-size:48px; color:#dee2e6;"></i>
                            <p class="text-muted" style="margin-top:12px;">No hay usuarios registrados para esta sucursal.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover table-condensed" style="margin-bottom:0;">
                                <thead style="background:#f8f9fa;">
                                    <tr>
                                        <th style="width:56px; text-align:center; border-top:none;"></th>
                                        <th style="border-top:none;">Nombre</th>
                                        <th style="border-top:none;">Persona vinculada</th>
                                        <th style="border-top:none;">Email</th>
                                        <th style="text-align:center; border-top:none;">Rol</th>
                                        <th style="text-align:center; border-top:none;">Estado</th>
                                        @if(auth()->user()->hasPermission('read_users') || auth()->user()->hasPermission('edit_users'))
                                            <th style="text-align:center; border-top:none; width:110px;">Acciones</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td style="text-align:center; vertical-align:middle; padding: 10px 8px;">
                                                <img src="{{ filter_var($user->avatar, FILTER_VALIDATE_URL) ? $user->avatar : Voyager::image($user->avatar) }}"
                                                     style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e9ecef; display:block; margin:0 auto;"
                                                     alt="{{ $user->name }}">
                                            </td>
                                            <td style="vertical-align:middle;">
                                                <strong>{{ $user->name }}</strong>
                                            </td>
                                            <td style="vertical-align:middle; color:#6c757d;">
                                                {{ $user->person->first_name ?? '—' }}
                                            </td>
                                            <td style="vertical-align:middle; color:#6c757d;">
                                                {{ $user->email }}
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                @if($user->role)
                                                    <span class="label label-primary">{{ $user->role->display_name }}</span>
                                                @else
                                                    <span class="text-muted" style="font-size:12px;">Sin rol</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                @if($user->status)
                                                    <span class="label label-success">Habilitado</span>
                                                @else
                                                    <span class="label label-danger">Inhabilitado</span>
                                                @endif
                                            </td>
                                            @if(auth()->user()->hasPermission('read_users') || auth()->user()->hasPermission('edit_users'))
                                                <td class="no-sort no-click bread-actions text-center">
                                                    @if(auth()->user()->hasPermission('read_users'))
                                                        <a href="{{ route('voyager.users.show', $user->id) }}" class="btn btn-sm btn-warning" title="Ver">
                                                            <i class="voyager-eye"></i>
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('edit_users'))
                                                        <a href="{{ route('voyager.users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="voyager-edit"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Mensualidades del dojo --}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="panel-title" style="margin:0;">
                        <i class="voyager-dollar"></i> Mensualidades de la Sucursal
                    </h3>
                    @if(!auth()->user()->dojo_id && auth()->user()->hasPermission('edit_dojos'))
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-nueva-mensualidad">
                            <i class="voyager-plus"></i> Nueva Mensualidad
                        </button>
                    @endif
                </div>
                <div class="panel-body" id="panel-mensualidades">
                    <div class="text-center text-muted" style="padding:20px 0;">
                        <i class="voyager-refresh"></i> Cargando...
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal editar fecha fin --}}
@if(!auth()->user()->dojo_id)
<div class="modal fade" id="modal-editar-fecha-fin" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="voyager-edit"></i> Editar Fecha Fin</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Nueva fecha fin <span class="text-danger">*</span></label>
                    <input type="date" id="fecha-fin-input" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btn-confirmar-fecha-fin">
                    <i class="voyager-check"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal historial de pagos --}}
<div class="modal fade" id="modal-pagos-mensualidad" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-list"></i> Pagos — <span id="pagos-periodo-label"></span></h4>
            </div>
            <div class="modal-body" id="modal-pagos-body" style="padding:0;">
                <div class="text-center" style="padding:20px;"><i class="voyager-refresh"></i> Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal pago parcial --}}
@if(!auth()->user()->dojo_id)
<div class="modal fade" id="modal-pagar-mensualidad" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="voyager-dollar"></i> Registrar Pago</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:12px; margin-bottom:12px;" id="pago-periodo-label"></p>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; text-align:center; margin-bottom:16px;">
                    <div style="background:#f8f9fa; border-radius:6px; padding:8px;">
                        <div style="font-size:11px; color:#888;">Total</div>
                        <div style="font-weight:700;" id="pago-monto-label">—</div>
                    </div>
                    <div style="background:#f8f9fa; border-radius:6px; padding:8px;">
                        <div style="font-size:11px; color:#888;">Pagado</div>
                        <div style="font-weight:700; color:#27ae60;" id="pago-pagado-label">—</div>
                    </div>
                    <div style="background:#ffeaea; border-radius:6px; padding:8px;">
                        <div style="font-size:11px; color:#888;">Saldo</div>
                        <div style="font-weight:700; color:#e74c3c;" id="pago-saldo-label">—</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Monto a pagar <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-addon">Bs.</span>
                        <input type="number" id="pago-monto-input" class="form-control"
                               step="0.01" min="0.01" placeholder="0.00">
                    </div>
                    <small class="text-muted">Máximo: <span id="pago-max-label">—</span></small>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Observación</label>
                    <input type="text" id="pago-obs-input" class="form-control"
                           maxlength="500" placeholder="Opcional...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-pago">
                    <i class="voyager-dollar"></i> Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal nueva mensualidad --}}
@if(!auth()->user()->dojo_id && auth()->user()->hasPermission('edit_dojos'))
<div class="modal fade" id="modal-nueva-mensualidad" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('dojo.mensualidades.store', $dojo->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="voyager-dollar"></i> Nueva Mensualidad</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha inicio <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_inicio" class="form-control" required
                                       value="{{ date('Y-m-01') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha fin <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_fin" class="form-control" required
                                       value="{{ date('Y-m-t') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Monto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Bs.</span>
                            <input type="number" name="monto" class="form-control" step="0.01" min="0" required
                                   placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <textarea name="observacion" class="form-control" rows="2" maxlength="500"
                                  placeholder="Notas opcionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="voyager-plus"></i> Registrar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@stop

@section('javascript')
<script>
    var listUrl = '{{ route('dojo.mensualidades.list', $dojo->id) }}';

    function cargarMensualidades() {
        $.get(listUrl, function(html) {
            $('#panel-mensualidades').html(html);
            bindMensualidadActions();
            actualizarModalNuevaMensualidad();
        });
    }

    function actualizarModalNuevaMensualidad() {
        var ultimaFechaFin = $('#ultima-fecha-fin').val();
        if (!ultimaFechaFin) return;

        // Parse local — evita timezone shift de UTC
        var p = ultimaFechaFin.split('-');
        var ultima = new Date(+p[0], +p[1] - 1, +p[2]);

        var inicio = new Date(ultima.getFullYear(), ultima.getMonth(), ultima.getDate() + 1);
        var fin    = new Date(inicio.getFullYear(), inicio.getMonth() + 1, 0);

        var fmt = function(d) {
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
        };

        var fmtInicio = fmt(inicio);
        var fmtFin    = fmt(fin);

        $('input[name="fecha_inicio"]').val(fmtInicio).attr('min', fmtInicio);
        $('input[name="fecha_fin"]').val(fmtFin).attr('min', fmtInicio);
    }

    var pagoUrl   = null;
    var pagoSaldo = 0;
    var fechaFinUrl = null;

    function bindMensualidadActions() {
        $('.btn-pagar-mensualidad').off('click').on('click', function() {
            var btn = $(this);
            pagoUrl   = btn.data('url');
            pagoSaldo = parseFloat(btn.data('saldo'));

            $('#pago-periodo-label').text('Período: ' + btn.data('periodo'));
            $('#pago-monto-label').text(parseFloat(btn.data('monto')).toFixed(2));
            $('#pago-pagado-label').text(parseFloat(btn.data('pagado')).toFixed(2));
            $('#pago-saldo-label').text(pagoSaldo.toFixed(2));
            $('#pago-max-label').text(pagoSaldo.toFixed(2));
            $('#pago-monto-input').val(pagoSaldo.toFixed(2)).attr('max', pagoSaldo);

            $('#modal-pagar-mensualidad').modal('show');
        });

        $('#btn-confirmar-pago').off('click').on('click', function() {
            var monto = parseFloat($('#pago-monto-input').val());
            var obs   = $('#pago-obs-input').val();
            if (!monto || monto <= 0 || monto > pagoSaldo) {
                toastr.error('Monto inválido. Debe ser entre 0.01 y ' + pagoSaldo.toFixed(2));
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: pagoUrl,
                type: 'PUT',
                data: { _token: '{{ csrf_token() }}', monto_pago: monto, observacion: obs },
                success: function(res) {
                    toastr.success(res.message);
                    $('#modal-pagar-mensualidad').modal('hide');
                    cargarMensualidades();

                    if (res.comprobante_url) {
                        setTimeout(function() {
                            if (confirm('¿Imprimir comprobante del pago?')) {
                                window.open(res.comprobante_url, '_blank');
                            }
                        }, 400);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON ? xhr.responseJSON.error : 'Error al registrar pago.');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });

        $('.clickable-mensualidad').off('click').on('click', function() {
            var target = $(this).data('target');
            var icon   = $(this).find('.toggle-icon-m');
            var $row   = $('#' + target);
            if ($row.is(':visible')) {
                $row.slideUp(180);
                icon.css('transform', 'rotate(0deg)');
            } else {
                $row.slideDown(220);
                icon.css('transform', 'rotate(90deg)');
            }
        });

        $('.btn-editar-fecha-fin').off('click').on('click', function() {
            var btn = $(this);
            fechaFinUrl = btn.data('url');
            var fechaActual = btn.data('fecha');
            var minFecha    = btn.data('min');
            $('#fecha-fin-input')
                .val(fechaActual)
                .attr('min', minFecha);
            $('#modal-editar-fecha-fin').modal('show');
        });

        $(document).off('click', '.btn-ver-pagos').on('click', '.btn-ver-pagos', function() {
            var btn = $(this);
            $('#pagos-periodo-label').text(btn.data('periodo'));
            $('#modal-pagos-body').html('<div class="text-center" style="padding:20px;"><i class="voyager-refresh"></i> Cargando...</div>');
            $('#modal-pagos-mensualidad').modal('show');

            $.get(btn.data('url'), function(html) {
                $('#modal-pagos-body').html(html);
            });
        });

        $('.btn-eliminar-mensualidad').off('click').on('click', function() {
            var btn = $(this);
            var url = btn.data('url');
            var periodo = btn.data('periodo');
            if (!confirm('¿Eliminar mensualidad de ' + periodo + '?')) return;

            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    toastr.success(res.message);
                    cargarMensualidades();
                },
                error: function() {
                    toastr.error('Error al eliminar.');
                }
            });
        });

        $(document).off('submit', '.form-whatsapp-comprobante').on('submit', '.form-whatsapp-comprobante', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var originalHtml = $btn.html();

            $btn.prop('disabled', true).html('<i class="voyager-refresh"></i>');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'Accept': 'application/json'
                },
                success: function(res) {
                    toastr.success(res.message || 'Comprobante enviado por WhatsApp correctamente.');
                },
                error: function(xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'No se pudo enviar el comprobante por WhatsApp.';

                    toastr.error(message);
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    }

    $('#btn-confirmar-fecha-fin').on('click', function() {
        var fechaFin = $('#fecha-fin-input').val();
        if (!fechaFin) { toastr.error('Ingrese una fecha.'); return; }

        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: fechaFinUrl,
            type: 'PUT',
            data: { _token: '{{ csrf_token() }}', fecha_fin: fechaFin },
            success: function(res) {
                toastr.success(res.message);
                $('#modal-editar-fecha-fin').modal('hide');
                cargarMensualidades();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON ? xhr.responseJSON.error : 'Error al guardar.');
            },
            complete: function() { btn.prop('disabled', false); }
        });
    });

    $(document).ready(function() {
        cargarMensualidades();
    });
</script>
@endsection
