@php
    $mensualidad = $pago->mensualidad;
    $alumno = $pago->alumno;
    $person = optional($alumno)->person;
    $dojo = optional($alumno)->dojo;
    $isPdf = $isPdf ?? false;
    $periodoInicio = optional($mensualidad)->periodo ? \Carbon\Carbon::parse($mensualidad->periodo)->startOfDay() : null;
    $periodoFin = $mensualidad && $mensualidad->fecha_fin
        ? \Carbon\Carbon::parse($mensualidad->fecha_fin)->startOfDay()
        : ($periodoInicio ? $periodoInicio->copy()->addMonthNoOverflow()->subDay() : null);
    $periodo = $periodoInicio && $periodoFin
        ? $periodoInicio->format('d/m/Y') . ' al ' . $periodoFin->format('d/m/Y')
        : 'N/A';
    $total = $mensualidad ? $mensualidad->total() : 0;
    $logo = optional($dojo)->logo ? asset('storage/' . $dojo->logo) : asset('images/default.jpg');
    $cobrador = optional($pago->registerUser)->name ?: 'Sistema';
    $pagadoHastaEstePago = $mensualidad
        ? $mensualidad->pagos
            ->filter(function ($item) use ($pago) {
                if ($item->fecha < $pago->fecha) {
                    return true;
                }

                return $item->fecha === $pago->fecha && $item->id <= $pago->id;
            })
            ->sum(fn($item) => (float) $item->monto)
        : (float) $pago->monto;
    $esPagoCompleto = $total > 0 && $pagadoHastaEstePago >= $total;
    $qrText = implode("\n", [
        'COMPROBANTE DE MENSUALIDAD',
        'Nro: ' . str_pad($pago->id, 6, '0', STR_PAD_LEFT),
        'Alumno: ' . (optional($person)->first_name ?: 'N/A'),
        'Documento: ' . (optional($person)->documentType ?: 'CI') . ' ' . (optional($person)->ci ?: 'N/A'),
        'Mensualidad: ' . $periodo,
        'Pago: Bs ' . number_format((float) $pago->monto, 2, '.', ','),
        'Fecha pago: ' . \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y'),
    ]);

    $qrCode = \BaconQrCode\Encoder\Encoder::encode($qrText, \BaconQrCode\Common\ErrorCorrectionLevel::M(), 'UTF-8');
    $matrix = $qrCode->getMatrix();
    $matrixSize = $matrix->getWidth();
    $margin = 4;
    $scale = max(2, (int) floor(120 / ($matrixSize + ($margin * 2))));
    $imageSize = ($matrixSize + ($margin * 2)) * $scale;
    $qrImage = imagecreatetruecolor($imageSize, $imageSize);
    $white = imagecolorallocate($qrImage, 255, 255, 255);
    $black = imagecolorallocate($qrImage, 0, 0, 0);
    imagefill($qrImage, 0, 0, $white);
    for ($y = 0; $y < $matrixSize; $y++) {
        for ($x = 0; $x < $matrixSize; $x++) {
            if ($matrix->get($x, $y) === 1) {
                imagefilledrectangle(
                    $qrImage,
                    ($x + $margin) * $scale,
                    ($y + $margin) * $scale,
                    (($x + $margin + 1) * $scale) - 1,
                    (($y + $margin + 1) * $scale) - 1,
                    $black
                );
            }
        }
    }
    ob_start();
    imagepng($qrImage);
    $qrPng = ob_get_clean();
    imagedestroy($qrImage);
    $qrDataUri = 'data:image/png;base64,' . base64_encode($qrPng);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Mensualidad #{{ $pago->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 22px;
        }
        .receipt {
            border: 1px solid #111;
            margin: 0 auto;
            max-width: 760px;
            padding: 18px;
        }
        .header {
            align-items: flex-start;
            border-bottom: 1px solid #111;
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 12px;
        }
        .brand {
            align-items: center;
            display: flex;
            gap: 12px;
        }
        .dojo-logo {
            border: 1px solid #111;
            filter: grayscale(100%);
            height: 72px;
            object-fit: cover;
            width: 72px;
        }
        .title { font-size: 18px; font-weight: 700; margin: 0 0 5px; }
        .subtitle { font-size: 12px; margin: 0; }
        .receipt-no { font-size: 13px; font-weight: 700; text-align: right; }
        .qr-box {
            border: 1px solid #111;
            display: inline-block;
            padding: 4px;
        }
        .qr-box img {
            display: block;
            height: 124px;
            width: 124px;
        }
        .qr-hint {
            font-size: 9px;
            font-weight: 400;
            margin-top: 4px;
            max-width: 158px;
            word-break: break-word;
        }
        .footer {
            align-items: flex-end;
            border-top: 1px solid #999;
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 10px;
        }
        .footer-note {
            color: #444;
            font-size: 11px;
        }
        .qr-footer {
            text-align: center;
            width: 136px;
        }
        .section-title {
            border-bottom: 1px solid #999;
            font-size: 12px;
            font-weight: 700;
            margin: 14px 0 8px;
            padding-bottom: 4px;
            text-transform: uppercase;
        }
        .grid {
            display: grid;
            gap: 8px 16px;
            grid-template-columns: 1fr 1fr;
        }
        .label { color: #444; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .value { font-size: 13px; margin-top: 2px; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border: 1px solid #111; padding: 7px; }
        th { background: #eee; text-align: left; }
        .right { text-align: right; }
        .actions { margin: 16px auto 0; max-width: 760px; text-align: right; }
        .btn {
            background: #fff;
            border: 1px solid #111;
            color: #111;
            cursor: pointer;
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
        }
        @media print {
            body { padding: 0; }
            .receipt { border: 0; max-width: none; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="brand">
                <img src="{{ $logo }}" class="dojo-logo" alt="Logo del dojo" onerror="this.style.display='none';">
                <div>
                    <h1 class="title">{{ optional($dojo)->nombre ?: 'Dojo' }}</h1>
                    <p class="subtitle">{{ optional($dojo)->address ?: '' }}</p>
                    <p class="subtitle">
                        {{ optional($dojo)->phone ?: '' }}
                        @if(optional($dojo)->email) · {{ $dojo->email }} @endif
                    </p>
                </div>
            </div>
            <div class="receipt-no">
                COMPROBANTE DE MENSUALIDAD<br>
                Nro. {{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="section-title">Alumno</div>
        <div class="grid">
            <div>
                <div class="label">Nombre</div>
                <div class="value">{{ optional($person)->first_name ?: 'N/A' }}</div>
            </div>
            <div>
                <div class="label">Documento</div>
                <div class="value">{{ optional($person)->documentType ?: 'CI' }} {{ optional($person)->ci ?: 'N/A' }}</div>
            </div>
            <div>
                <div class="label">Dojo</div>
                <div class="value">{{ optional($dojo)->nombre ?: 'Sin dojo' }}</div>
            </div>
            <div>
                <div class="label">Fecha de pago</div>
                <div class="value">{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="label">Cobrado por</div>
                <div class="value">{{ $cobrador }}</div>
            </div>
        </div>

        <div class="section-title">{{ $esPagoCompleto ? 'Detalle de mensualidad' : 'Pago parcial' }}</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @if($esPagoCompleto)
                <tr>
                    <td>Mensualidad del {{ $periodo }}</td>
                    <td class="right">Bs {{ number_format((float) optional($mensualidad)->monto, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td>Descuento</td>
                    <td class="right">Bs {{ number_format((float) optional($mensualidad)->descuento, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <th>Total a cobrar</th>
                    <th class="right">Bs {{ number_format($total, 2, '.', ',') }}</th>
                </tr>
                <tr>
                    <th>Pago total</th>
                    <th class="right">Bs {{ number_format($total, 2, '.', ',') }}</th>
                </tr>
                @else
                <tr>
                    <th>Pago recibido</th>
                    <th class="right">Bs {{ number_format((float) $pago->monto, 2, '.', ',') }}</th>
                </tr>
                @endif
            </tbody>
        </table>

        @if($pago->observacion)
            <div class="section-title">Observación</div>
            <p>{{ $pago->observacion }}</p>
        @endif

        <div class="footer">
            <div class="footer-note">
                Comprobante generado por Kaiteki.<br>
                Escanee el QR para ver alumno, mes y monto.
            </div>
            <div class="qr-footer">
                <div class="qr-box">
                    <img src="{{ $qrDataUri }}" alt="QR">
                </div>
                <div class="qr-hint">Datos del pago</div>
            </div>
        </div>
    </div>

    @unless($isPdf)
    <div class="actions">
        <button class="btn" onclick="window.print()">Imprimir</button>
        <button class="btn" onclick="window.close()">Cerrar</button>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 350);
        });
    </script>
    @endunless
</body>
</html>
