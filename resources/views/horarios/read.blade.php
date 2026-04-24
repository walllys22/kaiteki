@extends('voyager::master')

@section('page_title', 'Ver Horario')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-6" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-clock"></i> Detalle del Horario
                            </h1>
                        </div>
                        <div class="col-md-6 text-right" style="margin-top: 30px">
                            {{-- @if (auth()->user()->hasPermission('edit_horarios'))
                                <button type="button" class="btn btn-info btn-sm" onclick="openResponsableModal()">
                                    <i class="voyager-people"></i> <span>Asignar Responsable</span>
                                </button>
                            @endif --}}
                            <a href="{{ route('voyager.horarios.index') }}" class="btn btn-warning btn-sm">
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
        $showDojo = !auth()->user()->dojo_id;
    @endphp

    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-clock"></i> Información del Horario
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="control-label" style="font-weight:bold;">ID</label>
                                <p class="form-control-static">{{ $horario->id }}</p>
                            </div>
                            @if ($showDojo)
                                <div class="col-md-4 form-group">
                                    <label class="control-label" style="font-weight:bold;">Dojo</label>
                                    <p class="form-control-static">
                                        @if ($horario->dojo)
                                            <label class="label label-info">{{ $horario->dojo->nombre }}</label>
                                        @else
                                            <span class="text-muted">Sin sucursal</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            <div class="col-md-4 form-group">
                                <label class="control-label" style="font-weight:bold;">Turno</label>
                                <p class="form-control-static">
                                    {{ $horario->tipo ?? 'Sin turno' }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label class="control-label" style="font-weight:bold;">Nombre</label>
                                <p class="form-control-static" style="font-size: 18px;">
                                    <strong>{{ $horario->nombre ?? 'Sin nombre' }}</strong>
                                </p>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label" style="font-weight:bold;">Estado</label>
                                <p class="form-control-static">
                                    @if ($horario->status == 1)
                                        <label class="label label-success">Activo</label>
                                    @else
                                        <label class="label label-warning">Inactivo</label>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <hr style="margin: 10px 0 15px;">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="control-label" style="font-weight:bold;">Fecha de registro</label>
                                <p class="form-control-static">
                                    {{ optional($horario->created_at)->format('d/m/Y H:i') ?: 'Sin datos' }}
                                </p>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="control-label" style="font-weight:bold;">Total cambios de responsable</label>
                                <p class="form-control-static">
                                    <label class="label label-primary">{{ $horario->responsibles->count() }} registro(s)</label>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-people"></i> Responsable Actual
                        </h3>
                    </div>
                    <div class="panel-body">
                        @if ($horario->activeResponsible && $horario->activeResponsible->person)
                            <h4 style="margin-top: 0;">
                                <label class="label label-success">{{ $horario->activeResponsible->person->first_name }}</label>
                            </h4>
                            <p class="text-muted" style="margin-bottom: 8px;">
                                Asignado el {{ optional($horario->activeResponsible->created_at)->format('d/m/Y H:i') }}
                            </p>
                            <p style="margin-bottom: 0;">
                                <strong>Observación:</strong><br>
                                <span class="text-muted">{{ $horario->activeResponsible->observacion ?: 'Sin observación' }}</span>
                            </p>
                        @else
                            <p class="text-muted" style="margin-bottom: 15px;">Este horario todavía no tiene responsable asignado.</p>
                        @endif

                        @if (auth()->user()->hasPermission('edit_horarios'))
                            <hr style="margin: 15px 0;">
                            <button type="button" class="btn btn-info btn-block" onclick="openResponsableModal()">
                                <i class="voyager-plus"></i> {{ $horario->activeResponsible ? 'Cambiar Responsable' : 'Asignar Responsable' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 class="panel-title" style="margin:0;">
                            <i class="voyager-list"></i> Historial de Responsables
                        </h3>
                        <span class="label label-primary">{{ $horario->responsibles->count() }} registros</span>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align:center;">Fecha</th>
                                        <th>Responsable</th>
                                        <th style="text-align:center;">Estado</th>
                                        <th>Observación</th>
                                        <th style="text-align:center;">Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($horario->responsibles as $responsible)
                                        <tr>
                                            <td style="text-align:center;">
                                                {{ optional($responsible->created_at)->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <strong>{{ $responsible->person->first_name ?? 'Persona eliminada o no disponible' }}</strong>
                                            </td>
                                            <td style="text-align:center;">
                                                @if ($responsible->status == 1)
                                                    <label class="label label-success">Activo</label>
                                                @else
                                                    <label class="label label-default">Inactivo</label>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $responsible->observacion ?: 'Sin observación' }}
                                            </td>
                                            <td style="text-align:center;">
                                                {{ $responsible->registerRole ?: 'Sistema' }}
                                                @if ($responsible->registerUser_id)
                                                    <br>
                                                    <small class="text-muted">{{ $responsible->register->name }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <h5 class="text-center" style="margin-top: 30px">
                                                    <img src="{{ asset('images/empty.png') }}" width="100px" alt="" style="opacity: 0.8">
                                                    <br><br>
                                                    Aún no hay responsables registrados para este horario.
                                                </h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.modal-registerPerson')

    <div class="modal fade"  id="modal-assign-responsable" role="dialog">
        <div class="modal-dialog modal-primary">
            <div class="modal-content">
                <form id="form-assign-responsable" action="{{ route('horarios.responsables.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="horario_id" id="responsable_horario_id" value="{{ $horario->id }}">
                    <input type="hidden" id="responsable_dojo_id" value="{{ $horario->dojo_id }}">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: #ffffff !important">
                            <i class="voyager-people"></i> Asignar Responsable
                        </h4>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info" style="margin-bottom: 20px;">
                            <strong>Horario:</strong> {{ trim(($horario->tipo ?? '') . ' ' . ($horario->nombre ?? '')) ?: 'Sin nombre' }}<br>
                            <strong>Sucursal:</strong> {{ $horario->dojo->nombre ?? 'Sin sucursal' }}<br>
                            <small>Al asignar un nuevo responsable, el anterior quedará inactivo y se conservará en el historial.</small>
                        </div>

                        <div class="form-group">
                            <label for="select-person_id">Responsable</label>
                            <div class="input-group">
                                <select name="person_id" id="select-person_id" required class="form-control"></select>
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" title="Nueva persona" style="margin: 0px" type="button" id="btn-open-create-person">
                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="responsable_observacion">Observación</label>
                            <textarea name="observacion" id="responsable_observacion" class="form-control" rows="3" placeholder="Motivo del cambio o nota adicional"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-primary btn-save-responsable" value="Guardar">
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script src="{{ asset('js/include/person-select.js') }}"></script>
    <script src="{{ asset('js/include/person-register.js') }}"></script>
    <script src="{{ asset('js/btn-submit.js') }}"></script>

    <script>
        var shouldRestoreResponsableModal = false;

        function openResponsableModal() {
            $('#responsable_observacion').val('');
            $('#select-person_id').val(null).trigger('change');
            $('#modal-assign-responsable').modal('show');
        }

        window.getPersonListParams = function () {
            return {
                dojo_id: $('#responsable_dojo_id').val() || ''
            };
        };

        $(document).on('submit', '#form-assign-responsable', function(e) {
            e.preventDefault();

            const form = $(this);
            $('.btn-save-responsable').prop('disabled', true).val('Guardando...');

            $.post(form.attr('action'), form.serialize(), function(data) {
                toastr.success(data.message, 'Éxito');
                window.location.reload();
            }).fail(function(xhr) {
                const message = xhr.responseJSON?.error || 'No se pudo registrar el responsable.';
                toastr.error(message, 'Error');
            }).always(function() {
                $('.btn-save-responsable').prop('disabled', false).val('Guardar');
            });
        });

        $('#btn-open-create-person').on('click', function(e) {
            e.preventDefault();

            const dojoId = $('#responsable_dojo_id').val();
            shouldRestoreResponsableModal = true;

            if ($('#modal_person_dojo_id').length) {
                $('#modal_person_dojo_id').val(dojoId);
            }

            if ($('#modal_person_dojo_select').length && !$('#modal_person_dojo_select').is(':disabled')) {
                $('#modal_person_dojo_select').val(dojoId).trigger('change');
            }

            $('#modal-assign-responsable').modal('hide');
        });

        $('#modal-assign-responsable').on('hidden.bs.modal', function() {
            if (shouldRestoreResponsableModal) {
                $('#modal-create-person').modal('show');
            }
        });

        $('#modal-create-person').on('hidden.bs.modal', function() {
            if (shouldRestoreResponsableModal) {
                $('#modal-assign-responsable').modal('show');
            }
        });

        $(document).on('person:created', function(event, person) {
            if (!shouldRestoreResponsableModal || !person) {
                return;
            }

            const option = new Option(person.first_name, person.id, true, true);
            $('#select-person_id').append(option).trigger('change');
            $('#select-person_id').trigger({
                type: 'select2:select',
                params: {
                    data: person
                }
            });
        });

        $('#modal-assign-responsable').on('shown.bs.modal', function() {
            shouldRestoreResponsableModal = false;
        });
    </script>
@stop
