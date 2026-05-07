@php
    $alumnoGrado = $examen->alumnoGrado;
    $alumno = optional($alumnoGrado)->alumno;
    $person = optional($alumno)->person;
    $dojo = optional($alumno)->dojo;
    $grado = optional($alumnoGrado)->grado;
    $gradoLabel = trim(($grado->tipo ?? '') . ' ' . ($grado->numero ?? '') . ' ' . ($grado->nombre ?? ''));
    $monto = (float) ($examen->monto ?? 0);
    $pagado = (float) ($examen->monto_pagado ?? 0);
    $saldo = max(0, $monto - $pagado);
    $estadoPago = $monto <= 0 ? 'Sin monto' : ($pagado >= $monto ? 'Pagado' : 'Pendiente');
    $logo = optional($dojo)->logo ? asset('storage/' . $dojo->logo) : asset('images/default.jpg');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Examen</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 24px;
        }
        .actions {
            margin: 0 auto 14px;
            text-align: right;
            width: 760px;
        }
        .actions button,
        .actions a {
            background: #fff;
            border: 1px solid #000;
            border-radius: 0;
            color: #000;
            cursor: pointer;
            display: inline-block;
            font-size: 13px;
            margin-left: 6px;
            padding: 8px 13px;
            text-decoration: none;
        }
        .actions button:hover,
        .actions a:hover {
            background: #000;
            color: #fff;
        }
        .sheet {
            background: #fff;
            border: 2px solid #000;
            margin: 0 auto;
            min-height: 480px;
            padding: 28px 32px;
            width: 760px;
        }
        .header {
            align-items: center;
            border-bottom: 2px solid #000;
            display: flex;
            justify-content: space-between;
            padding-bottom: 16px;
        }
        .brand {
            align-items: center;
            display: flex;
            gap: 14px;
        }
        .logo {
            border: 1px solid #000;
            border-radius: 0;
            filter: grayscale(100%);
            height: 72px;
            object-fit: cover;
            width: 72px;
        }
        .dojo-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .dojo-meta {
            color: #000;
            line-height: 1.45;
            margin: 0;
        }
        .receipt-title { text-align: right; }
        .receipt-title h1 {
            color: #000;
            font-size: 22px;
            margin: 0 0 6px;
            text-transform: uppercase;
        }
        .receipt-number {
            color: #000;
            font-size: 12px;
        }
        .section {
            margin-top: 20px;
        }
        .section-title {
            border-bottom: 1px solid #000;
            color: #000;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
            padding-bottom: 6px;
            text-transform: uppercase;
        }
        .grid {
            display: grid;
            gap: 10px 16px;
            grid-template-columns: repeat(2, 1fr);
        }
        .field {
            border: 1px solid #000;
            border-radius: 0;
            padding: 9px 10px;
        }
        .field.full { grid-column: 1 / -1; }
        .label {
            color: #000;
            display: block;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .value {
            color: #000;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.35;
        }
        .amounts {
            border: 1px solid #000;
            border-radius: 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            overflow: hidden;
        }
        .amount-box {
            border-right: 1px solid #000;
            padding: 12px;
            text-align: center;
        }
        .amount-box:last-child { border-right: 0; }
        .amount-box strong {
            display: block;
            font-size: 18px;
            margin-top: 5px;
        }
        .status {
            border: 1px solid #000;
            border-radius: 0;
            color: #000;
            display: inline-block;
            font-weight: 700;
            margin-top: 6px;
            padding: 5px 12px;
            text-transform: uppercase;
        }
        .footer {
            border-top: 1px solid #000;
            color: #000;
            display: flex;
            font-size: 12px;
            justify-content: space-between;
            margin-top: 26px;
            padding-top: 12px;
        }
        .signature-row {
            display: grid;
            gap: 40px;
            grid-template-columns: repeat(2, 1fr);
            margin-top: 48px;
        }
        .signature {
            border-top: 1px solid #000;
            padding-top: 7px;
            text-align: center;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .actions { display: none; }
            .sheet { border: 0; margin: 0; padding: 18px; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="btn-back" onclick="window.close()">Cancelar</button>
        <button type="button" class="btn-print" onclick="window.print()">Imprimir</button>
    </div>

    <div class="sheet">
        <div class="header">
            <div class="brand">
                <img src="{{ $logo }}" class="logo" alt="Logo dojo" onerror="this.style.visibility='hidden';">
                <div>
                    <p class="dojo-name">{{ optional($dojo)->nombre ?: 'Dojo no registrado' }}</p>
                    <p class="dojo-meta">
                        {{ optional($dojo)->address ?: 'Dirección no registrada' }}<br>
                        Tel: {{ optional($dojo)->phone ?: 'No registrado' }}
                        @if(optional($dojo)->email)
                            · {{ $dojo->email }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="receipt-title">
                <h1>Comprobante de Examen</h1>
                <div class="receipt-number">Nro. {{ str_pad($examen->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="receipt-number">Emitido: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Información del Alumno</div>
            <div class="grid">
                <div class="field full">
                    <span class="label">Alumno</span>
                    <span class="value">{{ optional($person)->first_name ?: 'Persona no disponible' }}</span>
                </div>
                <div class="field">
                    <span class="label">Documento</span>
                    <span class="value">{{ optional($person)->documentType ?: 'CI' }}: {{ optional($person)->ci ?: 'No registrado' }}</span>
                </div>
                <div class="field">
                    <span class="label">Teléfono</span>
                    <span class="value">
                        @if(optional($person)->phone)
                            +{{ $person->country_code ?: '591' }} {{ $person->phone }}
                        @else
                            No registrado
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Detalle del Examen</div>
            <div class="grid">
                <div class="field">
                    <span class="label">Grado</span>
                    <span class="value">{{ $gradoLabel ?: 'Grado no disponible' }}</span>
                </div>
                <div class="field">
                    <span class="label">Fecha del examen</span>
                    <span class="value">{{ \Carbon\Carbon::parse($examen->fecha)->format('d/m/Y') }}</span>
                </div>
                <div class="field">
                    <span class="label">Resultado</span>
                    <span class="value">{{ $examen->aprobado ? 'Aprobado - grado completado' : 'Aplazado' }}</span>
                </div>
                <div class="field full">
                    <span class="label">Observación</span>
                    <span class="value">{{ $examen->observacion ?: 'Sin observaciones.' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="field">
                <span class="label">Monto</span>
                <span class="value">Bs {{ number_format($monto, 2, '.', ',') }}</span>
            </div>
        </div>
        <br><br><br>

        <div class="signature-row">
            <div class="signature">Firma del responsable</div>
            <div class="signature">Firma del alumno / tutor</div>
        </div>

        <div class="footer">
            <span>Registrado por: {{ optional($examen->registerUser)->name ?? auth()->user()->name ?? 'Sistema' }}</span>
            <span>Kaiteki · {{ date('Y') }}</span>
        </div>
    </div>
</body>
</html>
