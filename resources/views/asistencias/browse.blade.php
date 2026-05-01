@extends('voyager::master')

@section('page_title', 'Asistencias')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-4" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-clipboard-user"></i> Asistencias
                            </h1>
                        </div>
                        <div class="col-md-8 text-right" style="margin-top: 30px">
                            @if(auth()->user()->hasPermission('add_asistencias'))
                                <a href="{{ route('voyager.asistencias.create') }}" class="btn btn-success">
                                    <i class="voyager-plus"></i> <span>Nueva Asistencia</span>
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
                                <div class="dataTables_length">
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
@stop

@section('javascript')
    <script>
        var countPage = 10, timeout = null;

        $(document).ready(function() {
            list();

            $('#input-search').on('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(list, 1000);
            }).on('keyup', function(e) {
                if (e.keyCode == 13) { clearTimeout(timeout); list(); }
            });

            $('#select-paginate').change(function() {
                countPage = $(this).val();
                list();
            });

            $(document).on('click', '#div-results .pagination a', function(e) {
                e.preventDefault();
                let link = $(this).attr('href');
                if (link) {
                    let page = new URL(link).searchParams.get('page') || 1;
                    list(page);
                }
            });
        });

        function list(page = 1) {
            $('#div-results').loading({ message: 'Cargando...' });
            let search = $('#input-search').val() || '';
            $.ajax({
                url: `{{ url('admin/asistencias/ajax/list') }}?search=${search}&paginate=${countPage}&page=${page}`,
                type: 'get',
                success: function(result) {
                    $('#div-results').html(result);
                    $('#div-results').loading('toggle');
                }
            });
        }
    </script>
@stop
