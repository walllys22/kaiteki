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
                            @if (auth()->user()->hasPermission('add_alumnos'))
                            <a href="{{ route('voyager.alumnos.create') }}" class="btn btn-success">
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

        {{-- Custom modal for cannot delete --}}
        <div class="modal modal-warning fade" tabindex="-1" id="modal-cannot-delete" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #f0ad4e; color: black;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: black;"><i class="voyager-warning"></i> Advertencia</h4>
                    </div>
                    <div class="modal-body" style="color: black;">
                        <p>El Alumno no se puede eliminar porque tiene un Historial.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        {{-- Modal cambio de estado --}}
        <div class="modal modal-warning fade" tabindex="-1" id="modal-status" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" style="color: black;"><i class="fa-solid fa-person-circle-xmark"></i> Cambio de esta del Alumno</h4>
                    </div>
                    <div class="modal-body" style="color: black;">
                        <p><strong>Alumno:</strong> <span id="status-alumno-name"></span></p>
                        <p><strong>Dojo:</strong> <span id="status-alumno-dojo"></span></p>
                    </div>
                    <div class="modal-footer" style="text-align: center; color: black;">
                        <p>Esta seguro de cambiar el estado del alumno</p>
                        <br>
                        <form action="#" id="status_form" method="POST">
                            @csrf
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark">cambiar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        {{-- Single delete modal --}}
        <div class="modal modal-danger fade" tabindex="-1" id="modal-delete" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
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
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>
@stop


@section('javascript')
    <script src="{{ url('js/main.js') }}"></script>
    <script src="{{ asset('js/btn-submit.js') }}"></script>  

        
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        var timeout = null;
        $(document).ready(() => {
            list();
            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    // Cancelar el timeout del evento input si existe
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
                }, 2000); // retardo de 2 segundos cada vez que se escribe algo en el input
            });
        });


        function list(page = 1){
            $('#div-results').loading({message: 'Cargando...'});

            let url = '{{ url("admin/alumnos/ajax/list") }}';
            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPage}&page=${page}`,

                type: 'get',
                
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                }
            });

        }

        function statusItem(id, name, dojo) {
            let url = '{{ route("alumnos.status.update", ["id" => "TEMP_ID"]) }}'.replace('TEMP_ID', id);
            $('#status_form').attr('action', url);
            $('#status-alumno-name').text(name);
            $('#status-alumno-dojo').text(dojo);
            $('#modal-status').modal('show');
        }

        function deleteItem(url){
            // Extraer el ID del alumno de la URL de eliminación
            const urlParts = url.split('/');
            const alumnoId = urlParts[urlParts.length - 1];

            $.ajax({
                url: `{{ route('alumnos.check_historial', ['id' => 'TEMP_ID']) }}`.replace('TEMP_ID', alumnoId),
                type: 'GET',
                success: function(response) {
                    if (response.has_historial) {
                        $('#modal-cannot-delete').modal('show');
                    } else {
                        $('#delete_form').attr('action', url);
                        $('#modal-delete').modal('show');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Ocurrió un error al verificar el historial del alumno. Por favor, inténtelo de nuevo.');
                }
            });
        }
    </script>
@stop
