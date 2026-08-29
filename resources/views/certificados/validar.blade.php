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
    $gradoTexto  = trim($gradoNumero . ' ' . $gradoTipo) ?: $gradoNombre;
    $esDan       = mb_stripos($gradoTipo, 'dan') !== false;

    /**
     * Color de la cinta a partir del nombre del grado.
     * Contempla los nombres reales de la tabla grados: "Cinturon Blanco Franja
     * Amarilla", "Cinturon Marron Tres Puntas", etc.
     */
    $resolverCinta = function (?string $nombre) {
        $n = mb_strtolower((string) $nombre, 'UTF-8');
        $n = strtr($n, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        $paleta = [
            'negro'    => '#15171c',
            'negra'    => '#15171c',
            'marron'   => '#6b4423',
            'lila'     => '#8b5cf6',
            'violeta'  => '#8b5cf6',
            'morado'   => '#8b5cf6',
            'azul'     => '#2563eb',
            'verde'    => '#15803d',
            'naranja'  => '#e2680f',
            'amarillo' => '#eab308',
            'amarilla' => '#eab308',
            'blanco'   => '#f2efe6',
            'blanca'   => '#f2efe6',
        ];

        // El color base se busca ANTES de la palabra "franja" y el de la franja
        // despues. Si no se parte el nombre, "Cinturon Blanco Franja Amarilla"
        // resuelve a amarillo y pierde el blanco.
        $corte = mb_strpos($n, 'franja');
        $nBase   = $corte !== false ? mb_substr($n, 0, $corte) : $n;
        $nFranja = $corte !== false ? mb_substr($n, $corte) : '';

        $buscarColor = function ($texto) use ($paleta) {
            foreach ($paleta as $clave => $hex) {
                if (mb_strpos($texto, $clave) !== false) {
                    return [$hex, mb_strtoupper($clave, 'UTF-8')];
                }
            }

            return null;
        };

        [$base, $texto] = $buscarColor($nBase) ?? ['#c9c2b4', 'SIN CINTA'];
        $franja = $nFranja !== '' ? ($buscarColor($nFranja)[0] ?? null) : null;

        // Puntas del grado marron
        $puntas = 0;
        foreach (['una' => 1, 'dos' => 2, 'tres' => 3, 'cuatro' => 4] as $palabra => $cantidad) {
            if (mb_strpos($n, $palabra . ' punta') !== false) {
                $puntas = $cantidad;
                break;
            }
        }

        return ['base' => $base, 'franja' => $franja, 'puntas' => $puntas, 'texto' => $texto];
    };

    $cinta = $resolverCinta($gradoNombre);

    $fechaCarbon = $fecha ? \Carbon\Carbon::parse($fecha)->locale('es') : null;

    // Documento parcialmente enmascarado: la pagina es publica y solo tiene que
    // permitir confirmar identidad contra el papel.
    $ci = trim((string) optional($person)->ci);
    $ciVisible = $ci !== ''
        ? str_repeat('•', max(mb_strlen($ci) - 3, 0)) . mb_substr($ci, -3)
        : 'No registrado';

    $foto = asset('images/default.jpg');
    if (optional($person)->image) {
        $foto = \Storage::disk(env('FILESYSTEM_DRIVER'))->url(str_replace('.avif', '', $person->image) . '-cropped.webp');
    }

    $logoDojo = optional($dojo)->logo
        ? \Storage::disk(env('FILESYSTEM_DRIVER'))->url($dojo->logo)
        : null;

    $alumnoNombre = mb_strtoupper(trim((string) optional($person)->first_name) ?: 'Alumno', 'UTF-8');

    // Inicial del dojo, para cuando no hay logo cargado o falla la imagen.
    $inicialDojo = mb_strtoupper(mb_substr(trim((string) optional($dojo)->nombre) ?: 'D', 0, 1), 'UTF-8');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#16110d">
    <title>Verificacion de certificado | {{ optional($dojo)->nombre ?: 'Kaiteki' }}</title>
    <script>document.documentElement.classList.add('js');</script>
    <style>
        :root {
            --sumi:      #1b1512;   /* tinta */
            --sumi-2:    #4a3f38;
            --washi:     #f6f1e6;   /* papel */
            --washi-2:   #ede5d5;
            --linea:     #ddd2bd;
            --bermellon: #b3271e;   /* sello hanko */
            --oro:       #a9853f;
            --jade:      #1f6b3f;
            --ambar:     #a5610a;
            --obi:       {{ $cinta['base'] }};
        }

        * { box-sizing: border-box; }

        html {
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            scroll-behavior: smooth;
        }

        body {
            background:
                radial-gradient(circle at 12% 8%, rgba(179, 39, 30, .10), transparent 42%),
                radial-gradient(circle at 88% 92%, rgba(169, 133, 63, .12), transparent 46%),
                #16110d;
            color: var(--sumi);
            font-family: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, serif;
            margin: 0;
            min-height: 100vh;
            padding: 26px 14px 44px;
        }

        /* El rollo se despliega desde la varilla superior. */
        .kakejiku {
            animation: desplegar .85s cubic-bezier(.22, .9, .28, 1) both;
            transform-origin: top center;
            background: var(--washi);
            /* fibras del papel washi */
            background-image:
                repeating-linear-gradient(90deg, rgba(120, 96, 66, .05) 0 1px, transparent 1px 4px),
                repeating-linear-gradient(0deg,  rgba(120, 96, 66, .035) 0 1px, transparent 1px 7px);
            border-radius: 3px;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, .30),
                0 26px 60px rgba(0, 0, 0, .48);
            margin: 0 auto;
            max-width: 660px;
            overflow: hidden;
            position: relative;
        }

        /* varillas superior e inferior del rollo */
        /* Destello que recorre el papel una sola vez, al terminar de desplegarse. */
        .kakejiku > .lustre {
            animation: barrer 1.5s ease-in-out 1.05s 1 both;
            background: linear-gradient(105deg, transparent 38%, rgba(255, 255, 255, .60) 50%, transparent 62%);
            inset: 0;
            pointer-events: none;
            position: absolute;
            z-index: 3;
        }

        @keyframes barrer {
            from { opacity: 0; transform: translateX(-120%); }
            25%  { opacity: 1; }
            to   { opacity: 0; transform: translateX(120%); }
        }

        .kakejiku::before,
        .kakejiku::after {
            background: linear-gradient(180deg, #2c2118, #4a3728 45%, #1d1610);
            content: "";
            display: block;
            height: 12px;
            width: 100%;
        }

        /* ---------- encabezado ---------- */
        .torii {
            align-items: center;
            border-bottom: 1px solid var(--linea);
            display: flex;
            gap: 14px;
            padding: 20px 24px 18px;
        }

        .mon {
            align-items: center;
            background: #fff;
            border: 1px solid var(--linea);
            border-radius: 50%;
            display: flex;
            flex: 0 0 auto;
            height: 56px;
            justify-content: center;
            overflow: hidden;
            width: 56px;
        }

        .mon img { height: 100%; object-fit: cover; width: 100%; }

        .mon .inicial-mon {
            color: var(--bermellon);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .02em;
            line-height: 1;
        }

        .torii .titulos { flex: 1 1 auto; min-width: 0; }

        .torii .eyebrow {
            color: var(--sumi-2);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 10px;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .torii .dojo {
            font-size: 21px;
            font-weight: 700;
            letter-spacing: .01em;
            line-height: 1.2;
            margin-top: 3px;
        }

        .tategaki {
            color: var(--sumi);
            flex: 0 0 auto;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .34em;
            line-height: 1;
            opacity: .42;
            text-align: center;
            /* Texto latino en vertical: se rota, no se apila caracter por caracter. */
            writing-mode: vertical-rl;
            text-orientation: sideways;
        }

        /* ---------- veredicto + sello ---------- */
        .veredicto {
            padding: 26px 24px 20px;
            position: relative;
            text-align: center;
        }

        .veredicto .estado {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .veredicto.valido .estado { color: var(--jade); }
        .veredicto.curso  .estado { color: var(--ambar); }

        .veredicto .glosa {
            color: var(--sumi-2);
            font-size: 13px;
            margin: 8px auto 0;
            max-width: 40ch;
        }

        /* sello hanko estampado */
        .hanko {
            align-items: center;
            border: 3px double var(--bermellon);
            border-radius: 50%;
            color: var(--bermellon);
            display: flex;
            flex-direction: column;
            font-family: ui-sans-serif, system-ui, sans-serif;
            gap: 2px;
            height: 76px;
            justify-content: center;
            line-height: 1;
            opacity: .92;
            position: absolute;
            right: 20px;
            top: 12px;
            transform: rotate(-13deg);
            width: 76px;
            animation: estampar .5s cubic-bezier(.2, 1.3, .5, 1) .95s both;
        }

        /* Onda que se expande desde el sello, como el eco del golpe. */
        .hanko::after {
            animation: onda 1.4s ease-out 1.35s 2 both;
            border: 2px solid var(--bermellon);
            border-radius: 50%;
            content: "";
            inset: -4px;
            position: absolute;
        }

        @keyframes onda {
            from { opacity: .55; transform: scale(1); }
            to   { opacity: 0; transform: scale(1.9); }
        }

        .hanko span { display: block; }
        .hanko .hanko-linea { font-size: 12px; font-weight: 800; letter-spacing: .1em; }
        .hanko .hanko-marca { font-size: 18px; line-height: 1; }

        @keyframes desplegar {
            0%   { clip-path: inset(0 0 100% 0); opacity: 0; transform: scaleY(.82); }
            60%  { opacity: 1; }
            100% { clip-path: inset(0 0 0 0); opacity: 1; transform: scaleY(1); }
        }

        /* Cada seccion entra detras del despliegue, escalonada. */
        .torii,
        .veredicto,
        .alumno,
        .rango,
        .datos,
        .pie {
            animation: subir .6s cubic-bezier(.22, .9, .28, 1) both;
        }

        .torii     { animation-delay: .30s; }
        .veredicto { animation-delay: .44s; }
        .alumno    { animation-delay: .58s; }
        .rango     { animation-delay: .70s; }
        .datos     { animation-delay: .82s; }
        .pie       { animation-delay: .94s; }

        @keyframes subir {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: none; }
        }

        @keyframes estampar {
            0%   { opacity: 0; transform: rotate(-13deg) scale(2.4); }
            65%  { opacity: 1; transform: rotate(-13deg) scale(.94); }
            100% { opacity: .92; transform: rotate(-13deg) scale(1); }
        }

        /* ---------- alumno ---------- */
        .alumno {
            align-items: center;
            border-top: 1px solid var(--linea);
            display: flex;
            gap: 18px;
            padding: 20px 24px;
        }

        .retrato {
            animation: revelar .7s cubic-bezier(.22, .9, .28, 1) .72s both;
            transition: transform .35s ease, box-shadow .35s ease;
            border: 1px solid var(--linea);
            border-radius: 2px;
            box-shadow: 0 3px 10px rgba(27, 21, 18, .18);
            flex: 0 0 auto;
            height: 106px;
            object-fit: cover;
            padding: 4px;
            background: #fff;
            width: 88px;
        }

        .alumno .nombre {
            font-size: 25px;
            font-weight: 700;
            letter-spacing: .01em;
            line-height: 1.2;
        }

        .alumno .registro {
            color: var(--sumi-2);
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
            font-size: 12px;
            margin-top: 7px;
        }

        .retrato:hover {
            box-shadow: 0 8px 22px rgba(27, 21, 18, .30);
            transform: translateY(-2px) scale(1.02);
        }

        @keyframes revelar {
            from { opacity: 0; transform: scale(1.08); filter: saturate(.4); }
            to   { opacity: 1; transform: none; filter: none; }
        }

        .alumno { position: relative; }

        /* Halo que se abre detras del retrato, una vez. */
        .alumno::before {
            animation: halo 1.3s ease-out 1.15s 1 both;
            border: 2px solid var(--obi);
            border-radius: 50%;
            content: "";
            height: 110px;
            left: 24px;
            pointer-events: none;
            position: absolute;
            top: 18px;
            width: 92px;
        }

        @keyframes halo {
            from { opacity: .5; transform: scale(.9); }
            to   { opacity: 0; transform: scale(1.5); }
        }

        /* ---------- cinta ---------- */
        .rango {
            background: linear-gradient(180deg, var(--washi-2), var(--washi));
            border-block: 1px solid var(--linea);
            padding: 22px 24px 24px;
            text-align: center;
        }

        .obi {
            display: block;
            height: auto;
            margin: 0 auto 12px;
            max-width: 280px;
            transform-origin: 50% 30%;
            width: 100%;
            animation:
                anudar .6s cubic-bezier(.22, .9, .28, 1) 1.02s both,
                mecer 7s ease-in-out 1.7s infinite;
        }

        /* Balanceo minimo, como una cinta colgada. */
        @keyframes mecer {
            0%, 100% { transform: rotate(-.7deg); }
            50%      { transform: rotate(.7deg); }
        }

        @keyframes anudar {
            from { opacity: 0; transform: translateY(-8px) scale(.94); }
            to   { opacity: 1; transform: none; }
        }

        .rango .rotulo {
            color: var(--sumi-2);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 10px;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .rango .grado {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .02em;
            line-height: 1.1;
            margin-top: 6px;
        }

        .rango .cinta-nombre {
            color: var(--sumi-2);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 12px;
            letter-spacing: .14em;
            margin-top: 5px;
            text-transform: uppercase;
        }

        /* ---------- datos ---------- */
        .datos { display: grid; grid-template-columns: 1fr 1fr; }

        .dato {
            border-bottom: 1px solid var(--linea);
            padding: 15px 24px;
            transition: background-color .3s ease;
        }

        .dato:hover { background-color: rgba(169, 133, 63, .07); }
        .dato:active { background-color: rgba(169, 133, 63, .14); }

        /* --- revelado al entrar en pantalla ---
           Solo con JS: sin el, .js nunca se agrega y todo queda visible. */
        html.js .revelable {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .65s cubic-bezier(.22, .9, .28, 1), transform .65s cubic-bezier(.22, .9, .28, 1);
            will-change: opacity, transform;
        }

        html.js .revelable.a-la-vista {
            opacity: 1;
            transform: none;
        }

        /* El escalonado dentro de la grilla de datos. */
        html.js .datos .dato { transition-delay: calc(var(--orden, 0) * .07s); }
        .dato:nth-child(odd) { border-right: 1px solid var(--linea); }

        .dato .rotulo {
            color: var(--sumi-2);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 10px;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .dato .valor {
            font-size: 15px;
            font-weight: 600;
            margin-top: 4px;
            word-break: break-word;
        }

        .dato .valor small {
            color: var(--sumi-2);
            display: block;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            font-weight: 400;
            margin-top: 2px;
        }

        .mono { font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace; letter-spacing: .06em; }

        /* ---------- pie ---------- */
        .pie {
            border-top: 1px solid var(--linea);
            color: var(--sumi-2);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            line-height: 1.7;
            padding: 16px 24px 22px;
            text-align: center;
        }

        .pie strong { color: var(--sumi); }

        .firma-sello {
            color: var(--bermellon);
            font-size: 9px;
            letter-spacing: .3em;
            margin-bottom: 8px;
            opacity: .55;
        }

        @media (max-width: 470px) {
            body { padding: 14px 8px 30px; }
            .alumno { flex-direction: column; text-align: center; }
            .alumno::before { left: 50%; margin-left: -46px; top: 16px; }
            .retrato { height: 118px; width: 98px; }
            .datos { grid-template-columns: 1fr; }
            .dato:nth-child(odd) { border-right: 0; }
            .hanko { height: 64px; right: 12px; top: 10px; width: 64px; }
            .hanko .hanko-linea { font-size: 10px; }
            .hanko .hanko-marca { font-size: 15px; }
            .rango .grado { font-size: 27px; }
            .tategaki { display: none; }
        }

        /* Con movimiento reducido no se anima nada: todo entra ya visible. */
        @media (prefers-reduced-motion: reduce) {
            .kakejiku,
            .torii, .veredicto, .alumno, .rango, .datos, .pie,
            .hanko, .hanko::after, .obi, .retrato,
            .kakejiku > .lustre, .alumno::before {
                animation: none !important;
                clip-path: none !important;
                opacity: 1 !important;
                transform: none !important;
            }

            .kakejiku > .lustre, .alumno::before, .hanko::after { display: none !important; }

            html { scroll-behavior: auto; }
            html.js .revelable { opacity: 1 !important; transform: none !important; transition: none !important; }
            .retrato:hover { transform: none; }
        }

        /* Al imprimir tampoco: la hoja sale entera. */
        @media print {
            .kakejiku,
            .torii, .veredicto, .alumno, .rango, .datos, .pie,
            .hanko, .obi, .retrato {
                animation: none !important;
                clip-path: none !important;
                opacity: 1 !important;
                transform: none !important;
            }

            .kakejiku > .lustre, .alumno::before, .hanko::after { display: none !important; }
            html.js .revelable { opacity: 1 !important; transform: none !important; }
        }

        @media print {
            body { background: #fff; padding: 0; }
            .kakejiku { box-shadow: none; max-width: 100%; }
            .kakejiku::before, .kakejiku::after { display: none; }
        }
    </style>
</head>
<body>
    <main class="kakejiku">
        <span class="lustre" aria-hidden="true"></span>
        <header class="torii">
            <div class="mon">
                @if ($logoDojo)
                    <img src="{{ $logoDojo }}" alt="{{ optional($dojo)->nombre }}"
                         onerror="this.parentNode.innerHTML='<span class=&quot;inicial-mon&quot;>{{ $inicialDojo }}</span>';">
                @else
                    <span class="inicial-mon">{{ $inicialDojo }}</span>
                @endif
            </div>
            <div class="titulos">
                <div class="eyebrow">Verificacion de certificado</div>
                <div class="dojo">{{ optional($dojo)->nombre ?: 'Dojo' }}</div>
            </div>
            <div class="tategaki" aria-hidden="true">KARATE-DO</div>
        </header>

        <section class="veredicto {{ $esExamen ? 'valido' : 'curso' }}">
            <div class="hanko" role="img" aria-label="{{ $esExamen ? 'Certificado verificado' : 'Grado en curso' }}">
                @if ($esExamen)
                    <span class="hanko-linea">VALIDO</span>
                    <span class="hanko-marca">&#10004;</span>
                @else
                    <span class="hanko-linea">EN</span>
                    <span class="hanko-linea">CURSO</span>
                @endif
            </div>

            <div class="estado">
                {{ $esExamen ? 'Certificado verificado' : 'Grado en curso' }}
            </div>
            <p class="glosa">
                @if ($esExamen)
                    El examen de grado figura aprobado y registrado en el libro del dojo.
                @else
                    El alumno se encuentra cursando este grado. Aun no rindio el examen final.
                @endif
            </p>
        </section>

        <section class="alumno">
            <img class="retrato" src="{{ $foto }}" alt="{{ $alumnoNombre }}"
                 onerror="this.onerror=null;this.src='{{ asset('images/default.jpg') }}';">
            <div>
                <div class="nombre">{{ $alumnoNombre }}</div>
                <div class="registro">
                    REG. N&ordm; {{ $regId }}
                    @if (optional($alumno)->fechaIngreso)
                        &nbsp;&middot;&nbsp; INGRESO {{ \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') }}
                    @endif
                </div>
            </div>
        </section>

        <section class="rango">
            @include('certificados.partials.cinta', ['cinta' => $cinta])
            <div class="rotulo">{{ $esExamen ? 'Grado obtenido' : 'Grado en curso' }}</div>
            <div class="grado">{{ $gradoTexto ?: 'Grado' }}</div>
            @if ($gradoNombre)
                <div class="cinta-nombre">{{ $gradoNombre }}</div>
            @endif
        </section>

        <section class="datos revelable">
            <div class="dato" style="--orden: 0;">
                <div class="rotulo">{{ $esExamen ? 'Fecha de examen' : 'Inicio del grado' }}</div>
                <div class="valor">
                    {{ $fechaCarbon ? $fechaCarbon->format('d/m/Y') : 'Sin fecha' }}
                    @if ($fechaCarbon)
                        <small>{{ ucfirst($fechaCarbon->translatedFormat('l, d \d\e F \d\e Y')) }}</small>
                    @endif
                </div>
            </div>
            <div class="dato" style="--orden: 1;">
                <div class="rotulo">Documento</div>
                <div class="valor mono">{{ $ciVisible }}</div>
            </div>
            <div class="dato" style="--orden: 2;">
                <div class="rotulo">Dojo</div>
                <div class="valor">
                    {{ optional($dojo)->nombre ?: 'Sin dojo' }}
                    @if (trim((string) optional($dojo)->address))
                        <small>{{ $dojo->address }}</small>
                    @endif
                </div>
            </div>
            <div class="dato" style="--orden: 3;">
                <div class="rotulo">Instructor responsable</div>
                <div class="valor">
                    {{ trim((string) optional($responsable)->first_name) ?: 'No registrado' }}
                    @if (trim((string) optional($dojo)->grado_responsable))
                        <small>{{ $dojo->grado_responsable }}</small>
                    @endif
                </div>
            </div>
        </section>

        <footer class="pie revelable">
            <div class="firma-sello" aria-hidden="true">&#9670;&nbsp;&#9670;&nbsp;&#9670;</div>
            Consultado el <strong>{{ now()->format('d/m/Y \a \l\a\s H:i') }}</strong><br>
            Esta pagina se genera desde el registro oficial del dojo.<br>
            Si los datos no coinciden con el documento impreso, el certificado no es valido.
        </footer>
    </main>
    <script>
        (function () {
            var quietud = window.matchMedia('(prefers-reduced-motion: reduce)');
            var revelables = document.querySelectorAll('.revelable');

            function mostrarTodo() {
                revelables.forEach(function (el) { el.classList.add('a-la-vista'); });
            }

            // Sin soporte o con movimiento reducido: se muestra todo de una.
            if (quietud.matches || !('IntersectionObserver' in window)) {
                mostrarTodo();
                return;
            }

            var observador = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (entrada) {
                    if (entrada.isIntersecting) {
                        entrada.target.classList.add('a-la-vista');
                        observador.unobserve(entrada.target);
                    }
                });
            }, {
                // Se dispara un poco antes de que el bloque toque el borde,
                // asi en celular ya llega revelado al llegar scrolleando.
                rootMargin: '0px 0px -12% 0px',
                threshold: 0.12
            });

            revelables.forEach(function (el) { observador.observe(el); });

            // Red de seguridad: si algo quedo sin revelar, se muestra igual.
            window.addEventListener('load', function () {
                setTimeout(function () {
                    revelables.forEach(function (el) {
                        var caja = el.getBoundingClientRect();
                        if (caja.top < window.innerHeight) { el.classList.add('a-la-vista'); }
                    });
                }, 400);
            });
        })();
    </script>
</body>
</html>
