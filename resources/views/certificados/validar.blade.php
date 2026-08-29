@php
    $alumno      = optional($alumnoGrado)->alumno;
    $person      = optional($alumno)->person;
    $dojo        = optional($alumno)->dojo;
    $responsable = optional($dojo)->person;
    $grado       = optional($alumnoGrado)->grado;

    $esExamen = $tipo === 'examen';

    $gradoNumero = trim((string) optional($grado)->numero);
    $gradoTipo   = trim((string) optional($grado)->tipo);
    $gradoNombre = trim((string) optional($grado)->nombre);
    $cinta       = trim(str_ireplace('Cinturon', '', $gradoNombre));
    $gradoTexto  = trim($gradoNumero . ' ' . $gradoTipo) ?: $gradoNombre;
    $cintaTexto  = mb_strtoupper($cinta ?: $gradoNombre, 'UTF-8');

    $fechaCarbon = $fecha ? \Carbon\Carbon::parse($fecha)->locale('es') : null;

    // El documento se muestra parcialmente enmascarado: la pagina es publica y
    // solo tiene que permitir confirmar identidad, no exponer el CI completo.
    $ci = trim((string) optional($person)->ci);
    $ciVisible = $ci !== ''
        ? str_repeat('*', max(mb_strlen($ci) - 3, 0)) . mb_substr($ci, -3)
        : 'No registrado';

    $foto = asset('images/default.jpg');
    if (optional($person)->image) {
        $foto = \Storage::disk(env('FILESYSTEM_DRIVER'))->url(str_replace('.avif', '', $person->image) . '-cropped.webp');
    }

    $logoDojo = optional($dojo)->logo
        ? \Storage::disk(env('FILESYSTEM_DRIVER'))->url($dojo->logo)
        : asset('images/default.jpg');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Validacion de certificado | {{ optional($dojo)->nombre ?: 'Kaiteki' }}</title>
    <style>
        :root {
            --tinta: #16202b;
            --tenue: #64748b;
            --linea: #e2e8f0;
            --ok: #15803d;
            --ok-suave: #dcfce7;
            --curso: #b45309;
            --curso-suave: #fef3c7;
            --marca: #1f95d0;
        }

        * { box-sizing: border-box; }

        body {
            background: #f1f5f9;
            color: var(--tinta);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 18px 14px 40px;
        }

        .hoja {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .10);
            margin: 0 auto;
            max-width: 640px;
            overflow: hidden;
        }

        .cabecera {
            align-items: center;
            background: linear-gradient(135deg, #1f95d0, #2d78b9);
            color: #fff;
            display: flex;
            gap: 12px;
            padding: 16px 20px;
        }

        .cabecera img {
            background: rgba(255, 255, 255, .16);
            border-radius: 10px;
            height: 46px;
            object-fit: cover;
            padding: 4px;
            width: 46px;
        }

        .cabecera .dojo { font-size: 16px; font-weight: 700; line-height: 1.2; }
        .cabecera .sub { font-size: 11px; letter-spacing: .08em; opacity: .85; text-transform: uppercase; }

        .estado {
            align-items: center;
            display: flex;
            gap: 10px;
            font-weight: 700;
            justify-content: center;
            padding: 14px 20px;
            text-align: center;
        }

        .estado.valido { background: var(--ok-suave); color: var(--ok); }
        .estado.curso { background: var(--curso-suave); color: var(--curso); }
        .estado .icono { font-size: 20px; line-height: 1; }
        .estado .detalle { display: block; font-size: 11px; font-weight: 500; opacity: .85; }

        .alumno {
            align-items: center;
            border-bottom: 1px solid var(--linea);
            display: flex;
            gap: 16px;
            padding: 20px;
        }

        .alumno img {
            border: 3px solid var(--linea);
            border-radius: 50%;
            height: 92px;
            object-fit: cover;
            width: 92px;
        }

        .alumno .nombre { font-size: 20px; font-weight: 700; line-height: 1.25; }
        .alumno .meta { color: var(--tenue); font-size: 12px; margin-top: 4px; }

        .cinta-actual {
            border-bottom: 1px solid var(--linea);
            padding: 20px;
            text-align: center;
        }

        .cinta-actual .rotulo {
            color: var(--tenue);
            font-size: 11px;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .cinta-actual .grado { font-size: 26px; font-weight: 800; margin-top: 6px; }
        .cinta-actual .cinta { color: var(--marca); font-size: 14px; font-weight: 700; letter-spacing: .04em; margin-top: 2px; }

        .datos { display: grid; grid-template-columns: repeat(2, 1fr); }

        .dato { border-bottom: 1px solid var(--linea); padding: 14px 20px; }
        .dato:nth-child(odd) { border-right: 1px solid var(--linea); }
        .dato .rotulo { color: var(--tenue); font-size: 10px; letter-spacing: .08em; text-transform: uppercase; }
        .dato .valor { font-size: 14px; font-weight: 600; margin-top: 3px; word-break: break-word; }

        .bloque { padding: 18px 20px; }
        .bloque h2 {
            color: var(--tenue);
            font-size: 11px;
            letter-spacing: .1em;
            margin: 0 0 10px;
            text-transform: uppercase;
        }

        table { border-collapse: collapse; font-size: 13px; width: 100%; }
        th, td { padding: 8px 6px; text-align: left; }
        th { border-bottom: 2px solid var(--linea); color: var(--tenue); font-size: 10px; letter-spacing: .06em; text-transform: uppercase; }
        td { border-bottom: 1px solid var(--linea); }
        td.fecha, th.fecha { text-align: right; white-space: nowrap; }

        .pie {
            border-top: 1px solid var(--linea);
            color: var(--tenue);
            font-size: 11px;
            line-height: 1.6;
            padding: 16px 20px 20px;
            text-align: center;
        }

        @media (max-width: 460px) {
            .alumno { flex-direction: column; text-align: center; }
            .datos { grid-template-columns: 1fr; }
            .dato:nth-child(odd) { border-right: 0; }
        }
    </style>
</head>
<body>
    <div class="hoja">
        <div class="cabecera">
            <img src="{{ $logoDojo }}" alt="{{ optional($dojo)->nombre }}" onerror="this.onerror=null;this.src='{{ asset('images/default.jpg') }}';">
            <div>
                <div class="sub">Verificacion de certificado</div>
                <div class="dojo">{{ optional($dojo)->nombre ?: 'Dojo' }}</div>
            </div>
        </div>

        @if ($esExamen)
            <div class="estado valido">
                <span class="icono">&#10004;</span>
                <span>
                    CERTIFICADO VALIDO
                    <span class="detalle">Examen de grado aprobado y registrado en el sistema</span>
                </span>
            </div>
        @else
            <div class="estado curso">
                <span class="icono">&#9679;</span>
                <span>
                    GRADO EN CURSO
                    <span class="detalle">El alumno se encuentra cursando este grado</span>
                </span>
            </div>
        @endif

        <div class="alumno">
            <img src="{{ $foto }}" alt="{{ optional($person)->first_name }}" onerror="this.onerror=null;this.src='{{ asset('images/default.jpg') }}';">
            <div>
                <div class="nombre">{{ mb_strtoupper(trim((string) optional($person)->first_name) ?: 'Alumno', 'UTF-8') }}</div>
                <div class="meta">
                    Registro N&deg; {{ $regId }}
                    @if (optional($alumno)->fechaIngreso)
                        &middot; Ingreso {{ \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') }}
                    @endif
                </div>
            </div>
        </div>

        <div class="cinta-actual">
            <div class="rotulo">{{ $esExamen ? 'Grado obtenido' : 'Grado en curso' }}</div>
            <div class="grado">{{ $gradoTexto ?: 'Grado' }}</div>
            @if ($cintaTexto)
                <div class="cinta">CINTA {{ $cintaTexto }}</div>
            @endif
        </div>

        <div class="datos">
            <div class="dato">
                <div class="rotulo">{{ $esExamen ? 'Fecha de examen' : 'Inicio del grado' }}</div>
                <div class="valor">{{ $fechaCarbon ? $fechaCarbon->format('d/m/Y') : 'Sin fecha' }}</div>
            </div>
            <div class="dato">
                <div class="rotulo">Documento</div>
                <div class="valor">{{ $ciVisible }}</div>
            </div>
            <div class="dato">
                <div class="rotulo">Dojo</div>
                <div class="valor">{{ optional($dojo)->nombre ?: 'Sin dojo' }}</div>
            </div>
            <div class="dato">
                <div class="rotulo">Instructor responsable</div>
                <div class="valor">
                    {{ trim((string) optional($responsable)->first_name) ?: 'No registrado' }}
                    @if (trim((string) optional($dojo)->grado_responsable))
                        <br><span style="color:var(--tenue); font-size:11px; font-weight:500;">{{ $dojo->grado_responsable }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if ($historial->isNotEmpty())
            <div class="bloque">
                <h2>Grados anteriores ({{ $historial->count() }})</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Grado</th>
                            <th class="fecha">Aprobado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($historial as $item)
                            @php
                                $g = $item->grado;
                                $etiqueta = trim(trim((string) optional($g)->numero) . ' ' . trim((string) optional($g)->tipo)) ?: optional($g)->nombre;
                                $aprobado = $item->examenes->where('aprobado', 1)->last();
                            @endphp
                            <tr>
                                <td>
                                    {{ $etiqueta ?: 'Grado' }}
                                    @if (optional($g)->nombre)
                                        <br><span style="color:var(--tenue); font-size:11px;">{{ $g->nombre }}</span>
                                    @endif
                                </td>
                                <td class="fecha">
                                    {{ optional($aprobado)->fecha ? \Carbon\Carbon::parse($aprobado->fecha)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="pie">
            Consultado el {{ now()->format('d/m/Y H:i') }}<br>
            Esta pagina se genera desde el registro oficial del dojo.<br>
            Si los datos no coinciden con el documento impreso, el certificado no es valido.
        </div>
    </div>
</body>
</html>
