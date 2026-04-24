@extends('voyager::master')

@section('page_title', 'Viendo Horarios')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-clock"></i> Horarios
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_horarios'))
                                <a href="{{ route('voyager.horarios.create') }}" class="btn btn-success">
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
    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-9">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search" placeholder="🔍 Buscar..." class="form-control">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.modal-delete')
    @include('partials.modal-registerPerson')

    {{-- <div class="modal fade" id="modal-assign-responsable" role="dialog">
        <div class="modal-dialog modal-primary">
            <div class="modal-content">
                <form id="form-assign-responsable" action="{{ route('horarios.responsables.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="horario_id" id="responsable_horario_id">
                    <input type="hidden" id="responsable_dojo_id" value="">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: #ffffff !important">
                            <i class="voyager-people"></i> Asignar Responsable
                        </h4>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info" style="margin-bottom: 20px;">
                            <strong>Horario:</strong> <span id="responsable_horario_nombre">-</span><br>
                            <strong>Sucursal:</strong> <span id="responsable_dojo_nombre">-</span><br>
                            <small>Al asignar un nuevo responsable, el anterior quedará inactivo y se conservará en el historial.</small>
                        </div>

                        <div class="form-group">
                            <label for="select-person_id">Responsable</label>
                            <div class="input-group">
                                <select name="person_id" id="select-person_id" required class="form-control"></select>
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" title="Nueva persona" data-target="#modal-create-person" data-toggle="modal" style="margin: 0px" type="button" id="btn-open-create-person">
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
    </div> --}}
@stop

@section('css')
    <style>
    </style>
@stop

@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script src="{{ asset('js/btn-submit.js') }}"></script>
    <script src="{{ asset('js/include/person-select.js') }}"></script>
    <script src="{{ asset('js/include/person-register.js') }}"></script>

    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        var timeout = null;
        var shouldRestoreResponsableModal = false;

        $(document).ready(() => {
            list();

            $('#input-search').on('keyup', function(e){
                if (e.keyCode == 13) {
                    clearTimeout(timeout);
                    list();
                }
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
                list();
            });

            $('#input-search').on('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    list();
                }, 2000);
            });
        });

        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});

            let url = '{{ url("admin/horarios/ajax/list") }}';
            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPage}&page=${page}`,
                type: 'get',
                success: function(result){
                    $('#div-results').html(result);
                    $('#div-results').loading('toggle');
                }
            });
        }

        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }

        function openResponsableModal(horarioId, horarioNombre, dojoId, dojoNombre) {
            $('#responsable_horario_id').val(horarioId);
            $('#responsable_dojo_id').val(dojoId || '');
            $('#responsable_horario_nombre').text(horarioNombre || '-');
            $('#responsable_dojo_nombre').text(dojoNombre || 'Sin sucursal');
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
                $('#modal-assign-responsable').modal('hide');
                list(typeof page !== 'undefined' ? page : 1);
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
