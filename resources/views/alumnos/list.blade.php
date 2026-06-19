<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    <th style="text-align: center">Nombre Dojo</th>                    
                    <th style="text-align: center">Nombre Completo</th>
                    <th style="text-align: center">Horario</th>
                    <th style="text-align: center">Ultimo Actual</th>
                    <th style="text-align: center">Ingreso</th>
                    <th style="text-align: center">Registrado por</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody style="color: black;">
                @forelse ($data as $item)
                @php
                    $image = asset('images/default.jpg');
                    if(optional($item->person)->image){
                        $image = \Storage::disk(env('FILESYSTEM_DRIVER'))->url(str_replace('.avif', '', $item->person->image) . '-cropped.webp');
                    }
                    $personName = optional($item->person)->first_name ?: 'Persona no disponible';
                    $dojoName = optional($item->dojo)->nombre ?: 'Sin dojo';
                    $ultimoAlumnoGrado = $item->ultimoGrado;
                    $grado = optional($ultimoAlumnoGrado)->grado;
                    $gradoLabel = trim(($grado->tipo ?? '') . ' ' . ($grado->numero ?? '') . ' ' . ($grado->nombre ?? ''));
                    $gradoStatus = optional($ultimoAlumnoGrado)->status;
                    $tieneGradoActual = $ultimoAlumnoGrado && (string) $gradoStatus !== '1';
                    // Progreso de puntas del grado activo
                    $usaRepasosGrado = $grado ? $grado->usaRepasos() : false;
                    $puntasReq = (int) optional($grado)->puntas;
                    $puntasAprob = ($ultimoAlumnoGrado && $ultimoAlumnoGrado->relationLoaded('repasos'))
                        ? $ultimoAlumnoGrado->repasos->where('aprobado', 1)->count()
                        : null;
                    $cumplePuntas = $usaRepasosGrado ? ($puntasAprob !== null && $puntasAprob >= $puntasReq) : true;
                    $horariosAsignados = $item->alumnoHorarios->filter(function ($alumnoHorario) {
                        return $alumnoHorario->horario;
                    });
                @endphp
                <tr>
                    <td>{{ $item->id }}</td>
                    <td style="text-align: center">{{ $dojoName }} </td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="{{ $image }}" alt="{{ $personName }}" class="image-expandable" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px; object-fit: cover;">
                            <div>
                                {{ $personName }} 
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center">
                        @forelse ($horariosAsignados as $alumnoHorario)
                            @php
                                $horario = $alumnoHorario->horario;
                            @endphp
                            <div style="{{ !$loop->last ? 'margin-bottom: 6px;' : '' }}">
                                <strong>{{ $horario->nombre ?: 'Sin nombre' }}</strong>
                                @if ($horario->tipo)
                                    <br><small class="text-muted">{{ $horario->tipo }}</small>
                                @endif
                                @if ((string) $alumnoHorario->status !== '1')
                                    <br><label class="label label-default">Inactivo</label>
                                @endif
                            </div>
                        @empty
                            <span class="text-muted">Sin horario asignado</span>
                        @endforelse
                    </td>
                    <td style="text-align: center">
                        @if ($grado)
                            <strong>{{ $gradoLabel ?: 'Sin nombre' }}</strong><br>
                            @if ((string) $gradoStatus === '1')
                                <label class="label label-success">Completado</label>
                            @else
                                <label class="label label-info">En progreso</label>
                            @endif
                            @if ($tieneGradoActual && $puntasAprob !== null)
                                <br>
                                @if ($usaRepasosGrado)
                                    <small class="label {{ $cumplePuntas ? 'label-success' : 'label-warning' }}" style="font-size:11px;">
                                        <i class="fa-solid fa-star"></i> {{ $puntasAprob }}/{{ $puntasReq }} puntas
                                    </small>
                                    @if ($cumplePuntas)
                                        <br><small class="text-success" style="font-size:11px;"><i class="fa-solid fa-graduation-cap"></i> Listo p/ examen</small>
                                    @endif
                                @else
                                    <br><small class="text-success" style="font-size:11px;"><i class="fa-solid fa-graduation-cap"></i> Examen directo</small>
                                @endif
                            @endif
                        @else
                            <span class="text-muted">Sin grado registrado</span>
                        @endif
                    </td>
                    <td style="text-align: center">
                        {{ $item->fechaIngreso ? \Carbon\Carbon::parse($item->fechaIngreso)->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        @if($item->register)
                            <div style="font-size:12px; font-weight:600;">{{ $item->register->name }}</div>
                        @endif
                        <div style="font-size:11px; color:#888; margin-top:2px;">
                            {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '—' }}
                        </div>
                    </td>
                    <td style="text-align: center">
                        @if ($item->status==1)
                            <label class="label label-success">Activo</label>
                        @else
                            <label class="label label-warning">Inactivo</label>
                        @endif
                    </td>
                    <td style="width: 22%" class="no-sort no-click bread-actions text-right">

                            @if (auth()->user()->hasPermission('read_alumnos'))
                            <a href="{{ route('voyager.alumnos.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-primary view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('read_alumnos') && $tieneGradoActual)
                            <a href="{{ route('alumno.grado.certificado.cursando', $ultimoAlumnoGrado->id) }}" target="_blank" title="Imprimir certificado actual" class="btn btn-sm btn-warning">
                                <i class="fa-solid fa-print"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_alumnos'))
                            <a href="javascript:void(0);" onclick="deleteItem('{{ $item->id }}', '{{ route('voyager.alumnos.destroy', ['id' => $item->id]) }}')" title="Eliminar" class="btn btn-sm btn-danger">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('edit_alumnos'))
                            <a href="javascript:void(0);" onclick="statusItem('{{ $item->id }}', '{{ $personName }}', '{{ $dojoName }}', '{{ $item->status }}')" title="{{ $item->status == 1 ? 'Deshabilitar' : 'Habilitar' }}" class="btn btn-sm {{ $item->status == 1 ? 'btn-dark' : 'btn-success' }}">
                                <i class="fa-solid {{ $item->status == 1 ? 'fa-person-circle-xmark' : 'fa-person-circle-check' }}"></i> <span class="hidden-xs hidden-sm"></span>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <h5 class="text-center" style="margin-top: 50px">
                                <img src="{{ asset('images/empty.png') }}" width="120px" alt="" style="opacity: 0.8">
                                <br><br>
                                No hay resultados
                            </h5>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4" style="overflow-x:auto">
        @if(count($data)>0)
            <p class="text-muted">Mostrando del {{$data->firstItem()}} al {{$data->lastItem()}} de {{$data->total()}} registros.</p>
        @endif
    </div>
    <div class="col-md-8" style="overflow-x:auto">
        <nav class="text-right">
            {{ $data->links() }}
        </nav>
    </div>
</div>

<script>
   
   var page = "{{ request('page') }}";
    $(document).ready(function(){
        $('.page-link').click(function(e){
            e.preventDefault();
            let link = $(this).attr('href');
            if(link){
                page = link.split('=')[1];
                list(page);
            }
        });
    });
</script>
