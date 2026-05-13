@extends('voyager::master')

@section('page_title', 'Ordenar Grados')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-7" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="fa-solid fa-sort"></i> Ordenar Grados
                            </h1>
                        </div>
                        <div class="col-md-5 text-right" style="margin-top: 28px; display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                            <span class="text-muted" style="font-size:13px;">
                                <i class="fa-solid fa-circle-info"></i>
                                Arrastrá las filas para definir la progresión
                            </span>
                            <a href="{{ route('voyager.grados.index') }}" class="btn btn-default btn-sm">
                                <i class="voyager-list"></i> Volver
                            </a>
                            <button id="btn-save-order" class="btn btn-primary btn-sm" disabled>
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Orden
                            </button>
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
        <div class="col-md-10 col-md-offset-1">

            <div class="alert alert-info" style="margin-bottom:16px;">
                <i class="fa-solid fa-circle-info"></i>
                <strong>Regla japonesa:</strong>
                Los Kyu cuentan hacia abajo (10mo Kyu → 1er Kyu); los Dan cuentan hacia arriba (1er Dan → Dan superior).
                La posición <strong>#1</strong> es el primer grado que cursa un alumno principiante.
            </div>

            <div class="panel panel-bordered">
                <div class="panel-body" style="padding: 0;">
                    <table class="table table-bordered" style="margin:0;">
                        <thead>
                            <tr>
                                <th style="width:40px; text-align:center;"></th>
                                <th style="width:44px; text-align:center;">#</th>
                                <th style="width:60px; text-align:center;"></th>
                                <th>Grado</th>
                                <th style="width:80px; text-align:center;">Tipo</th>
                                <th style="width:80px; text-align:center;">Puntas</th>
                                <th style="width:80px; text-align:center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-grados">
                            @forelse ($grados as $item)
                                @php
                                    $gradoLabel = trim(($item->nombre ?? ''));
                                    $isDan = $item->isDan();
                                @endphp
                                <tr data-id="{{ $item->id }}" style="cursor: default;">
                                    <td style="text-align:center; vertical-align:middle; color:#aaa; font-size:18px; cursor:grab;" class="drag-handle">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        <span class="orden-badge label"
                                              style="background:{{ $isDan ? '#1a1a1a' : '#5b9bd5' }}; color:#fff; font-size:12px; padding:3px 8px; min-width:24px; display:inline-block;">
                                            {{ $item->orden ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        @if($item->image)
                                            @php $imgSrc = asset('storage/' . str_replace('.avif', '', $item->image) . '-cropped.webp'); @endphp
                                            <img src="{{ $imgSrc }}" alt="{{ $gradoLabel }}"
                                                 style="width:44px; height:44px; border-radius:6px; object-fit:cover;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                            <span style="display:none; width:44px; height:44px; border-radius:6px; background:#f0f4f8;
                                                         align-items:center; justify-content:center; color:#ccc; font-size:18px;">
                                                <i class="fa-solid fa-image"></i>
                                            </span>
                                        @else
                                            <span style="display:inline-flex; width:44px; height:44px; border-radius:6px; background:#f0f4f8;
                                                         align-items:center; justify-content:center; color:#ccc; font-size:18px;">
                                                <i class="fa-solid fa-image"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <strong>{{ $gradoLabel ?: 'Sin nombre' }}</strong>
                                        @if($item->numero)
                                            <small class="text-muted"> — {{ $item->tipo }} {{ $item->numero }}</small>
                                        @endif
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        @if($isDan)
                                            <span class="label" style="background:#1a1a1a; color:#fff;">Dan</span>
                                        @else
                                            <span class="label label-primary">Kyu</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        @if($item->isDan())
                                            <span class="text-muted" style="font-size:11px;">Sin puntas</span>
                                        @else
                                            <span class="label label-info">{{ $item->puntas }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        @if($item->status == 1)
                                            <span class="label label-success">Activo</span>
                                        @else
                                            <span class="label label-warning">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <h5 class="text-center" style="margin: 30px 0;">No hay grados registrados.</h5>
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
@stop

@section('css')
<style>
    .drag-handle { user-select: none; }
    .sortable-ghost { opacity: 0.4; background: #e8f0fe; }
    .sortable-chosen { background: #f0f7ff; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
    #sortable-grados tr { transition: background 0.15s; }
</style>
@stop

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    var reorderUrl  = '{{ route("grados.reorder") }}';
    var csrfToken   = '{{ csrf_token() }}';
    var changed     = false;

    var sortable = Sortable.create(document.getElementById('sortable-grados'), {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function () {
            changed = true;
            document.getElementById('btn-save-order').removeAttribute('disabled');
            refreshOrdenBadges();
        }
    });

    function refreshOrdenBadges() {
        var rows = document.querySelectorAll('#sortable-grados tr[data-id]');
        rows.forEach(function (row, idx) {
            var badge = row.querySelector('.orden-badge');
            if (badge) badge.textContent = idx + 1;
        });
    }

    document.getElementById('btn-save-order').addEventListener('click', function () {
        var btn = this;
        btn.setAttribute('disabled', true);
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';

        var ids = Array.from(
            document.querySelectorAll('#sortable-grados tr[data-id]')
        ).map(function (row) {
            return parseInt(row.getAttribute('data-id'));
        });

        $.ajax({
            url: reorderUrl,
            type: 'POST',
            data: JSON.stringify({ ids: ids }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                changed = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Guardado';
                setTimeout(function () {
                    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Orden';
                }, 2000);

                toastr.success('Orden guardado correctamente.');
            },
            error: function () {
                btn.removeAttribute('disabled');
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Orden';
                toastr.error('No se pudo guardar el orden. Intente nuevamente.');
            }
        });
    });

    window.addEventListener('beforeunload', function (e) {
        if (changed) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@stop
