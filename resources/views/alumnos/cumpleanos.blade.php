@php
    // El dojo siempre viene resuelto: la lista es de una sola sucursal.
    $logo = $dojo->logo ? \Storage::disk(env('FILESYSTEM_DRIVER'))->url($dojo->logo) : null;
    $dojoNombre = $dojo->nombre;

    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
    $mesesCorto = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];
    $diasSemana = [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
    ];

    $etiquetaEstado = ['1' => 'Solo activos', '0' => 'Solo inactivos', 'todos' => 'Activos e inactivos'];

    $hoyMMDD = (int) now()->format('md');

    // Agrupado por mes, respetando el orden del rango (que puede cruzar el fin de anio).
    $porMes = $filas->groupBy(fn($fila) => $fila['cumple']->month);

    $hayHoy = $filas->contains(fn($fila) => (int) $fila['cumple']->format('md') === $hoyMMDD);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cumpleaños - {{ $dojoNombre }}</title>
    <style>
        :root {
            --tinta:    #23201d;
            --suave:    #7a7069;
            --tenue:    #a89e96;
            --acento:   #b03a2e;
            --acento-2: #d9a441;
            --linea:    #e2dcd4;
            --papel:    #ffffff;
            --crema:    #faf7f2;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #d8d3cc;
            color: var(--tinta);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Barra de acciones (no se imprime) ── */
        .actions {
            background: #2f2b28;
            padding: 12px 24px;
            text-align: right;
        }
        .actions button, .actions a {
            background: transparent;
            border: 1px solid #6f665f;
            border-radius: 4px;
            color: #f3efe9;
            cursor: pointer;
            display: inline-block;
            font-family: inherit;
            font-size: 13px;
            margin-left: 8px;
            padding: 8px 20px;
            text-decoration: none;
            transition: background .15s, border-color .15s;
        }
        .actions button:hover, .actions a:hover { background: #433d38; border-color: #9c9089; }
        .actions .primaria { background: var(--acento); border-color: var(--acento); font-weight: 600; }
        .actions .primaria:hover { background: #8f2f25; border-color: #8f2f25; }

        .sheet {
            background: var(--papel);
            box-shadow: 0 2px 14px rgba(0,0,0,.18);
            margin: 22px auto;
            padding: 34px 38px 28px;
            width: 860px;
        }

        /* ── Encabezado ── */
        .encabezado {
            align-items: center;
            border-bottom: 2px solid var(--tinta);
            display: flex;
            gap: 18px;
            padding-bottom: 16px;
        }
        .encabezado .logo {
            flex: 0 0 62px;
            height: 62px;
            width: 62px;
        }
        .encabezado .logo img {
            height: 100%;
            object-fit: contain;
            width: 100%;
        }
        .encabezado .logo .inicial {
            align-items: center;
            background: var(--crema);
            border: 1px solid var(--linea);
            border-radius: 50%;
            color: var(--tenue);
            display: flex;
            font-size: 26px;
            font-weight: 700;
            height: 100%;
            justify-content: center;
            width: 100%;
        }
        .encabezado .centro { flex: 1 1 auto; }
        .encabezado .dojo {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }
        .encabezado .titulo {
            font-size: 30px;
            font-weight: 300;
            letter-spacing: -0.5px;
            line-height: 1.15;
            margin-top: 2px;
        }
        .encabezado .titulo strong { color: var(--acento); font-weight: 700; }
        .encabezado .derecha {
            border-left: 1px solid var(--linea);
            color: var(--suave);
            flex: 0 0 128px;
            font-size: 10.5px;
            padding-left: 16px;
            text-align: right;
        }
        .encabezado .derecha .grande {
            color: var(--tinta);
            display: block;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.1;
        }
        .encabezado .derecha .rotulo {
            display: block;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        /* ── Franja de filtros ── */
        .franja {
            border-bottom: 1px solid var(--linea);
            color: var(--suave);
            display: flex;
            flex-wrap: wrap;
            font-size: 11px;
            gap: 26px;
            padding: 11px 2px;
        }
        .franja b {
            color: var(--tinta);
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .franja .rotulo {
            color: var(--tenue);
            letter-spacing: 1px;
            margin-right: 5px;
            text-transform: uppercase;
        }

        /* ── Aviso de cumpleaños de hoy ── */
        .aviso-hoy {
            background: #fdf6e3;
            border-left: 3px solid var(--acento-2);
            color: #7d6320;
            font-size: 11.5px;
            margin-top: 16px;
            padding: 8px 12px;
        }

        /* ── Bloque de mes ── */
        .mes { margin-top: 24px; }
        .mes-cabecera {
            align-items: baseline;
            border-bottom: 1px solid var(--tinta);
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 5px;
        }
        .mes-nombre {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .mes-linea { border-top: 1px dotted var(--linea); flex: 1 1 auto; }
        .mes-conteo {
            color: var(--suave);
            font-size: 10.5px;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* ── Grilla de tarjetas ── */
        .grilla {
            display: grid;
            gap: 9px 14px;
            grid-template-columns: 1fr 1fr;
        }

        .tarjeta {
            align-items: center;
            background: var(--crema);
            border: 1px solid var(--linea);
            border-radius: 5px;
            display: flex;
            gap: 11px;
            padding: 9px 12px 9px 9px;
        }
        .tarjeta.hoy {
            background: #fdf6e3;
            border-color: var(--acento-2);
            box-shadow: inset 3px 0 0 var(--acento-2);
        }

        /* Sello del dia */
        .dia-sello {
            background: var(--papel);
            border: 1px solid var(--linea);
            border-radius: 4px;
            flex: 0 0 42px;
            overflow: hidden;
            text-align: center;
            width: 42px;
        }
        .dia-sello .num {
            color: var(--tinta);
            display: block;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.15;
            padding-top: 3px;
        }
        .dia-sello .mes-abrev {
            background: var(--tinta);
            color: #fff;
            display: block;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 1.2px;
            padding: 2px 0;
            text-transform: uppercase;
        }
        .tarjeta.hoy .dia-sello { border-color: var(--acento-2); }
        .tarjeta.hoy .dia-sello .mes-abrev { background: var(--acento); }

        /* Retrato */
        .retrato {
            border-radius: 50%;
            flex: 0 0 38px;
            height: 38px;
            overflow: hidden;
            width: 38px;
        }
        .retrato img { height: 100%; object-fit: cover; width: 100%; }
        .retrato .iniciales {
            align-items: center;
            background: #e8e1d7;
            color: #8d8177;
            display: flex;
            font-size: 14px;
            font-weight: 700;
            height: 100%;
            justify-content: center;
            width: 100%;
        }

        /* Datos */
        .datos { flex: 1 1 auto; min-width: 0; }
        .datos .nombre {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        /* Una sola linea: si no entra se recorta, para que las tarjetas de una
           misma fila conserven la altura y la lista no quede despareja. */
        .datos .meta {
            color: var(--suave);
            font-size: 10.5px;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .datos .meta .sep { color: var(--tenue); margin: 0 5px; }
        .datos .tel { white-space: nowrap; }

        /* Edad */
        .edad {
            border-left: 1px solid var(--linea);
            flex: 0 0 auto;
            padding-left: 10px;
            text-align: center;
        }
        .edad .n {
            color: var(--acento);
            display: block;
            font-size: 19px;
            font-weight: 700;
            line-height: 1;
        }
        .edad .u {
            color: var(--tenue);
            display: block;
            font-size: 8.5px;
            letter-spacing: 1px;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .marca-hoy {
            background: var(--acento);
            border-radius: 2px;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-left: 6px;
            padding: 1px 5px;
            vertical-align: 1px;
        }
        .marca-inactivo {
            background: #8d8177;
            border-radius: 2px;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-left: 6px;
            padding: 1px 5px;
            vertical-align: 1px;
        }

        /* ── Vacio y pie ── */
        .vacio {
            border: 1px dashed var(--linea);
            border-radius: 5px;
            color: var(--suave);
            margin-top: 26px;
            padding: 44px 20px;
            text-align: center;
        }
        .vacio .grande { display: block; font-size: 16px; margin-bottom: 5px; }

        .pie {
            border-top: 1px solid var(--linea);
            color: var(--tenue);
            display: flex;
            font-size: 10px;
            justify-content: space-between;
            margin-top: 26px;
            padding-top: 10px;
        }

        /* ── Impresion ── */
        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .sheet {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .mes, .tarjeta { break-inside: avoid; page-break-inside: avoid; }
            .mes-cabecera { break-after: avoid; page-break-after: avoid; }
            .tarjeta, .dia-sello .mes-abrev, .edad .n, .aviso-hoy, .tarjeta.hoy {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page { size: letter portrait; margin: 12mm 12mm 10mm; }
        }

        /* Pantallas chicas: una columna */
        @media screen and (max-width: 900px) {
            .sheet { padding: 20px; width: auto; }
            .grilla { grid-template-columns: 1fr; }
            .encabezado { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button class="primaria" onclick="window.print()">Imprimir</button>
    <a href="#" onclick="window.close(); return false;">Cerrar</a>
</div>

<div class="sheet">

    {{-- ── Encabezado ── --}}
    <div class="encabezado">
        <div class="logo">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $dojoNombre }}"
                     onerror="this.style.display='none'; this.parentNode.innerHTML='<span class=&quot;inicial&quot;>{{ mb_substr($dojoNombre, 0, 1) }}</span>';">
            @else
                <span class="inicial">{{ mb_substr($dojoNombre, 0, 1) }}</span>
            @endif
        </div>
        <div class="centro">
            <div class="dojo">{{ $dojoNombre }}</div>
            <div class="titulo">Lista de <strong>Cumpleaños</strong></div>
        </div>
        <div class="derecha">
            <span class="grande">{{ $filas->count() }}</span>
            <span class="rotulo">{{ $filas->count() === 1 ? 'alumno' : 'alumnos' }}</span>
        </div>
    </div>

    {{-- ── Franja de filtros ── --}}
    <div class="franja">
        <span>
            <span class="rotulo">Periodo</span>
            <b>{{ $desde->format('d/m') }} &ndash; {{ $hasta->format('d/m') }}</b>@if($cruzaAnio) <span style="color:var(--tenue);">(cruza el año)</span>@endif
        </span>
        <span><span class="rotulo">Incluye</span><b>{{ $etiquetaEstado[$estado] ?? 'Solo activos' }}</b></span>
        <span style="margin-left:auto;"><span class="rotulo">Emitido</span><b>{{ now()->format('d/m/Y') }}</b></span>
    </div>

    @if($hayHoy)
        <div class="aviso-hoy">
            <strong>Hoy hay cumpleaños.</strong> Las filas resaltadas cumplen años el {{ now()->format('d/m') }}.
        </div>
    @endif

    {{-- ── Meses ── --}}
    @forelse($porMes as $mes => $delMes)
        <div class="mes">
            <div class="mes-cabecera">
                <span class="mes-nombre">{{ $meses[$mes] }}</span>
                <span class="mes-linea"></span>
                <span class="mes-conteo">{{ $delMes->count() }} {{ $delMes->count() === 1 ? 'cumpleaños' : 'cumpleaños' }}</span>
            </div>

            <div class="grilla">
                @foreach($delMes as $fila)
                    @php
                        $alumno = $fila['alumno'];
                        $person = $alumno->person;
                        $cumple = $fila['cumple'];
                        $esHoy  = (int) $cumple->format('md') === $hoyMMDD;
                        $activo = (int) $alumno->status === 1;

                        $nombre = optional($person)->first_name ?: 'Sin nombre';

                        // Iniciales para cuando no hay foto cargada.
                        $partes    = preg_split('/\s+/', trim($nombre));
                        $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1));

                        $foto = optional($person)->image
                            ? \Storage::disk(env('FILESYSTEM_DRIVER'))->url(str_replace('.avif', '', $person->image) . '-cropped.webp')
                            : null;

                        // Solo mostrar telefono si hay numero: el prefijo suelto no sirve.
                        $telefono = trim(optional($person)->phone ?? '');
                        if ($telefono !== '') {
                            $telefono = trim((optional($person)->country_code ?? '') . ' ' . $telefono);
                        }
                    @endphp
                    <div class="tarjeta {{ $esHoy ? 'hoy' : '' }}">
                        <div class="dia-sello">
                            <span class="num">{{ $cumple->format('d') }}</span>
                            <span class="mes-abrev">{{ $mesesCorto[$mes] }}</span>
                        </div>

                        <div class="retrato">
                            @if($foto)
                                <img src="{{ $foto }}" alt="{{ $nombre }}"
                                     onerror="this.style.display='none'; this.parentNode.innerHTML='<span class=&quot;iniciales&quot;>{{ $iniciales }}</span>';">
                            @else
                                <span class="iniciales">{{ $iniciales }}</span>
                            @endif
                        </div>

                        <div class="datos">
                            <div class="nombre">
                                {{ $nombre }}
                                @if($esHoy)<span class="marca-hoy">HOY</span>@endif
                                @if(!$activo)<span class="marca-inactivo">INACTIVO</span>@endif
                            </div>
                            {{-- El dia/mes ya esta en el sello, asi que aca alcanza
                                 con el dia de la semana y el año de nacimiento. --}}
                            <div class="meta">
                                {{ $diasSemana[(int) $cumple->dayOfWeek] }}
                                <span class="sep">&middot;</span>nació en {{ $fila['nacimiento']->format('Y') }}
                                @if($telefono !== '')
                                    <span class="sep">&middot;</span><span class="tel">{{ $telefono }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="edad">
                            <span class="n">{{ $fila['edad'] }}</span>
                            <span class="u">{{ $fila['edad'] === 1 ? 'año' : 'años' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="vacio">
            <span class="grande">Sin cumpleaños en el periodo</span>
            Ningún alumno de {{ $dojoNombre }} cumple años entre el
            {{ $desde->format('d/m') }} y el {{ $hasta->format('d/m') }}.
        </div>
    @endforelse

    <div class="pie">
        <span>{{ $dojoNombre }} &middot; Lista de cumpleaños</span>
        <span>Emitido el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}</span>
    </div>

</div>
</body>
</html>
