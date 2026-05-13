@php
    $isCursando  = $isCursando ?? false;

    // Normalizar fuente de datos según el modo
    if ($isCursando) {
        // Viene del controlador certificadoCursando() con $alumnoGrado directo
        $alumno      = optional($alumnoGrado)->alumno;
        $fechaRaw    = $alumnoGrado->fecha;
        $regId       = $alumnoGrado->id;
    } else {
        // Viene del controlador certificadoExamen() con $examen
        $alumnoGrado = $examen->alumnoGrado;
        $alumno      = optional($alumnoGrado)->alumno;
        $fechaRaw    = $examen->fecha;
        $regId       = optional($alumnoGrado)->id;
    }

    $dojo        = optional($alumno)->dojo;
    $person      = optional($alumno)->person;
    $responsable = optional($dojo)->person;
    $grado       = optional($alumnoGrado)->grado;

    $gradoNumero      = trim((string) optional($grado)->numero);
    $gradoTipo        = trim((string) optional($grado)->tipo);
    $gradoNombre      = trim((string) optional($grado)->nombre);
    $cinta            = trim(str_ireplace('Cinturon', '', $gradoNombre));
    $gradoCertificado = trim($gradoNumero . ' ' . $gradoTipo . ' - CINTA ' . mb_strtoupper($cinta ?: $gradoNombre, 'UTF-8'));

    $fecha = \Carbon\Carbon::parse($fechaRaw)->locale('es');
    $dia   = $fecha->format('d');
    $mes   = ucfirst($fecha->translatedFormat('F'));
    $anio  = $fecha->format('Y');

    $responsableNombre = trim((string) optional($responsable)->first_name) ?: 'Responsable';
    $responsableGrado  = trim((string) optional($dojo)->grado_responsable);

    $dojoNombre   = trim((string) optional($dojo)->nombre) ?: 'Dojo';
    $alumnoNombre = trim((string) optional($person)->first_name) ?: 'Alumno no registrado';
    $qrTexto      = "Alumno: {$alumnoNombre}\nDojo: {$dojoNombre}\nGrado: {$gradoCertificado}\nFecha: {$dia}/{$mes}/{$anio}";
    $qrSvg        = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->generate($qrTexto);

    $template = asset('images/dojos/ljp/certificados/examen.png');
    $photo    = asset('images/default.jpg');
    if (optional($person)->image) {
        $photo = asset('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp');
    }

    $gradoImage = null;
    if (optional($grado)->image) {
        $gradoImage = asset('storage/' . $grado->image);
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Examen</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body {
            background: #222;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 16px;
        }
        .actions {
            margin: 0 auto 12px;
            text-align: right;
            width: min(100%, 1120px);
        }
        .actions button {
            background: #fff;
            border: 1px solid #111;
            color: #111;
            cursor: pointer;
            font-size: 13px;
            margin-left: 6px;
            padding: 8px 13px;
        }
        .certificate {
            aspect-ratio: 4 / 3;
            background-image: url('{{ $template }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            margin: 0 auto;
            position: relative;
            width: min(100%, 1120px);
        }
        .student-photo {
            height: 23.5%;
            left: 71%;
            object-fit: cover;
            position: absolute;
            top: 9.2%;
            width: 17.1%;
        }
        .reg {
            color: #f20b3f;
            font-size: clamp(18px, 3vw, 36px);
            font-style: italic;
            font-weight: 800;
            left: 74.2%;
            position: absolute;
            text-align: center;
            top: 30.4%;
            width: 13%;
        }
        .certificate-text {
            color: #000;
            font-size: clamp(18px, 3vw, 34px);
            font-weight: 600;
            left: 12%;
            letter-spacing: .02em;
            line-height: 1.65;
            position: absolute;
            top: 43%;
            width: 76%;
        }
        .certificate-text strong {
            font-weight: 800;
        }
        .certificate-text .belt {
            font-weight: 800;
        }
        .line {
            display: block;
            text-align: justify;
            text-align-last: left;
        }
        .certificate-footer {
            align-items: flex-end;
            bottom: 5%;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            left: 12%;
            position: absolute;
            width: 76%;
        }
        .grade-image {
            display: block;
            margin: 0 auto;
            max-height: 110px;
            max-width: 110px;
            object-fit: contain;
        }
        .signature-box {
            color: #000;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
        }
        .qr-box {
            align-items: center;
            display: flex;
            flex-direction: column;
            gap: 3px;
            text-align: center;
        }
        .qr-box svg { display: block; height: 110px; margin: 0 auto; width: 110px; }
        .qr-label {
            color: #000;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.3;
            margin-top: 3px;
            white-space: pre-line;
        }
        .signature-line {
            border-top: 2px solid #000;
            margin-bottom: 10px;
            width: 100%;
        }
        .signature-name {
            line-height: 1.2;
        }
        .signature-grade {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            margin-top: 4px;
        }
        .signature-label {
            font-size: 16px;
            font-weight: 600;
            margin-top: 4px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .actions { display: none; }
            .certificate {
                height: 210mm;
                width: 297mm;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.close()">Cancelar</button>
        <button type="button" onclick="window.print()">Imprimir</button>
    </div>

    <div class="certificate">
        <img src="{{ $photo }}" class="student-photo" alt="Foto alumno" onerror="this.style.display='none';">
        <div class="reg">Reg: {{ $regId }}</div>

        <div class="certificate-text">
            <span class="line" style="font-size: 28px">
                @if($isCursando)
                    A: <strong>{{ $alumnoNombre }}</strong>,
                    se certifica que actualmente se encuentra cursando el grado
                    <span class="belt">{{ $gradoCertificado }}</span>,
                    a partir de los {{ $dia }} dias del mes de {{ $mes }} del Año {{ $anio }},
                    en la ciudad de la Santisima Trinidad, Departamento del Beni, Bolivia.
                @else
                    A: <strong>{{ optional($person)->first_name ?: 'Alumno no registrado' }}</strong>, por haber vencido las pruebas fisicas y teoricas, se lo promueve al grado <span class="belt">{{ $gradoCertificado }}</span>, es dado a los {{ $dia }} dias del mes de {{ $mes }} del Año {{ $anio }}, en la ciudad de la Santisima Trinidad, Departamento del Beni, Bolivia.
                @endif
            </span>
        </div>

        <div class="certificate-footer">
            <div class="qr-box">
                {!! $qrSvg !!}
            </div>
            <div style="text-align:center;">
                @if($gradoImage)
                    <img src="{{ $gradoImage }}" class="grade-image" alt="Imagen del grado" onerror="this.style.display='none';">
                @endif
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">{{ $responsableNombre }}</div>
                @if($responsableGrado)
                    <div class="signature-grade">{{ $responsableGrado }}</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
