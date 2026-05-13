@php
    $person   = $alumno->person;
    $dojo     = $alumno->dojo;
    $logo     = $dojo && $dojo->logo ? asset('storage/' . $dojo->logo) : asset('images/default.jpg');
    $photo    = asset('images/default.jpg');
    if (optional($person)->image) {
        $photoPath = public_path('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp');
        $photo = file_exists($photoPath)
            ? asset('storage/' . str_replace('.avif', '', $person->image) . '-cropped.webp')
            : asset('storage/' . $person->image);
    }
    $nombre   = optional($person)->first_name ?? 'Sin nombre';
    $ci       = optional($person)->ci ? (optional($person)->documentType ?? 'CI') . ': ' . $person->ci : 'No registrado';
    $genero   = optional($person)->gender ?? null;
    $generoLabel = $genero === 'M' ? 'Masculino' : ($genero === 'F' ? 'Femenino' : 'No registrado');
    $nacimiento   = optional($person)->birth_date ? \Carbon\Carbon::parse($person->birth_date)->format('d/m/Y') : 'No registrado';
    $edad = optional($person)->birth_date ? \Carbon\Carbon::parse($person->birth_date)->age . ' año(s)' : 'No registrado';
    $telefono = optional($person)->phone ? (optional($person)->country_code ? '+' . $person->country_code . ' ' : '') . $person->phone : 'No registrado';
    $correo   = optional($person)->email ?? 'No registrado';
    $direccion = optional($person)->address ?? 'No registrada';
    $observacion = $alumno->observacion ?? 'Sin observaciones registradas.';
    $fechaIngreso = $alumno->fechaIngreso ? \Carbon\Carbon::parse($alumno->fechaIngreso)->format('d/m/Y') : 'No registrado';
    $dojoNombre = optional($dojo)->nombre ?? 'No registrado';
    $activo = (int) $alumno->status === 1;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex del Alumno - {{ $nombre }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #ccc;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222;
        }
        .actions {
            padding: 10px 20px;
            text-align: right;
            background: #555;
        }
        .actions button {
            background: #fff;
            border: 1px solid #333;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            padding: 7px 18px;
        }
        .actions a {
            background: #fff;
            border: 1px solid #333;
            border-radius: 3px;
            color: #333;
            font-size: 13px;
            margin-left: 8px;
            padding: 7px 18px;
            text-decoration: none;
            display: inline-block;
        }
        .sheet {
            background: #fff;
            margin: 18px auto;
            padding: 28px 32px;
            width: 900px;
        }

        /* ── Encabezado ─────────────────────────── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .header-logo { width: 90px; vertical-align: middle; }
        .header-logo img { width: 80px; height: auto; }
        .header-center { text-align: center; vertical-align: middle; }
        .header-center .dojo-name {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-center .doc-title {
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .header-right { text-align: right; vertical-align: top; font-size: 11px; color: #555; width: 140px; }

        /* ── Secciones ──────────────────────────── */
        .section-header {
            background: #d9d9d9;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 1px;
            padding: 6px 10px;
            text-transform: uppercase;
            margin-top: 16px;
            margin-bottom: 0;
        }
        .section-body { padding: 12px 4px 4px; }

        /* ── Datos Generales ────────────────────── */
        .datos-table { width: 100%; border-collapse: collapse; }
        .datos-table td { vertical-align: top; }
        .foto-col { width: 130px; padding-right: 16px; }
        .foto-col img {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border: 1px solid #bbb;
        }
        .foto-label { font-size: 10px; color: #777; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
        .alumno-col { width: 100%; }

        .alumno-name-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .alumno-name {
            font-size: 20px;
            font-weight: bold;
        }
        .badge-activo {
            background: #2ecc71;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 12px;
            border-radius: 3px;
        }
        .badge-inactivo {
            background: #e74c3c;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 12px;
            border-radius: 3px;
        }

        /* info boxes */
        .info-grid { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 6px; }
        .info-box {
            background: #f2f2f2;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            flex: 1;
            min-width: 140px;
            padding: 7px 10px;
        }
        .info-box.full { flex: 0 0 100%; min-width: 100%; }
        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #777;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value { font-size: 12px; color: #222; }

        /* ── Tutores ────────────────────────────── */
        .tutores-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .tutores-table th {
            background: #f2f2f2;
            border-bottom: 1px solid #ccc;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .tutores-table td {
            border-bottom: 1px solid #eee;
            font-size: 12px;
            padding: 8px 8px;
            vertical-align: middle;
        }
        .tutores-table tr:last-child td { border-bottom: none; }

        /* ── Enfermedades ───────────────────────── */
        .enf-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .enf-table th {
            background: #f2f2f2;
            border-bottom: 1px solid #ccc;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .enf-table td {
            border-bottom: 1px solid #eee;
            font-size: 12px;
            padding: 8px 8px;
            vertical-align: middle;
        }
        .enf-table tr:last-child td { border-bottom: none; }
        .ninguna { text-align: center; color: #888; padding: 14px 0; font-size: 13px; }

        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .sheet { margin: 0; padding: 20px 24px; width: 100%; box-shadow: none; }
            @page { size: letter; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button onclick="window.print()">&#128438; Imprimir</button>
    <a href="{{ route('voyager.alumnos.show', $alumno->id) }}">&#8592; Volver</a>
</div>

<div class="sheet">

    {{-- ── Encabezado ── --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ $logo }}" alt="Logo">
            </td>
            <td class="header-center">
                <div class="dojo-name">{{ strtoupper($dojoNombre) }}</div>
                <div class="doc-title">Kardex del Alumno</div>
            </td>
            <td class="header-right">
                Impreso<br>
                {{ now()->format('d/m/Y') }}<br>
                {{ now()->format('g:i a') }}<br>
                Página 1 de 1
            </td>
        </tr>
    </table>

    {{-- ── Datos Generales ── --}}
    <div class="section-header">Datos Generales</div>
    <div class="section-body">
        <table class="datos-table">
            <tr>
                <td class="foto-col">
                    <div class="foto-label">Foto del Alumno</div>
                    <img src="{{ $photo }}" alt="Foto">
                </td>
                <td class="alumno-col">
                    <div style="font-size:10px; font-weight:bold; color:#777; text-transform:uppercase; margin-bottom:6px;">Alumno</div>
                    <div class="alumno-name-row">
                        <div class="alumno-name">
                            {{ $nombre }} - 
                            @if($activo)
                                Activo
                            @else
                                Inactivo
                            @endif
                        </div>
                        
                    </div>

                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Dojo</div>
                            <div class="info-value">{{ $dojoNombre }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Fecha de Ingreso</div>
                            <div class="info-value">{{ $fechaIngreso }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Documento</div>
                            <div class="info-value">{{ $ci }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Género</div>
                            <div class="info-value">{{ $generoLabel }}</div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Fecha de Nacimiento</div>
                            <div class="info-value">{{ $nacimiento }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Edad</div>
                            <div class="info-value">{{ $edad }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Teléfono</div>
                            <div class="info-value">{{ $telefono }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Correo</div>
                            <div class="info-value">{{ $correo }}</div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-box full">
                            <div class="info-label">Dirección</div>
                            <div class="info-value">{{ $direccion }}</div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-box full">
                            <div class="info-label">Observaciones</div>
                            <div class="info-value">{{ $observacion }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Tutores ── --}}
    <div class="section-header">Tutores</div>
    <div class="section-body" style="padding-top:6px;">
        @if($tutores->count())
        <table class="tutores-table">
            <thead>
                <tr>
                    <th>CI/NIT</th>
                    <th>Tutor</th>
                    <th>Parentesco</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tutores as $t)
                <tr>
                    <td>{{ optional($t->tutor)->ci ?? '—' }}</td>
                    <td>{{ optional($t->tutor)->first_name ?? '—' }}</td>
                    <td>{{ optional($t->pariente)->nombre ?? '—' }}</td>
                    <td>
                        @php
                            $tp = $t->tutor;
                            echo $tp ? (($tp->country_code ? '+' . $tp->country_code . ' ' : '') . ($tp->phone ?? '—')) : '—';
                        @endphp
                    </td>
                    <td>{{ optional($t->tutor)->address ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="ninguna">Ninguno</div>
        @endif
    </div>

    {{-- ── Enfermedades ── --}}
    <div class="section-header">Enfermedades</div>
    <div class="section-body" style="padding-top:6px;">
        @if($enfermedades->count())
        <table class="enf-table">
            <thead>
                <tr>
                    <th>Condición</th>
                    <th>Medicamento</th>
                    <th>Administración</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enfermedades as $e)
                <tr>
                    <td>{{ $e->nombre ?? '—' }}</td>
                    <td>{{ $e->medicamento ?? '—' }}</td>
                    <td>{{ $e->administracion ?? '—' }}</td>
                    <td>{{ $e->observacion ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="ninguna">Ninguna</div>
        @endif
    </div>

</div>
</body>
</html>
