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
    $scale = max(3, (int) floor(120 / $matrixSize));
    $imgSize = $matrixSize * $scale + 16;
    $qrImg = imagecreatetruecolor($imgSize, $imgSize);
    $white = imagecolorallocate($qrImg, 255, 255, 255);
    $black = imagecolorallocate($qrImg, 0, 0, 0);
    imagefill($qrImg, 0, 0, $white);
    $pad = 8;
    for ($y = 0; $y < $matrixSize; $y++) {
        for ($x = 0; $x < $matrixSize; $x++) {
            if ($matrix->get($x, $y) === 1) {
                imagefilledrectangle($qrImg, $pad + $x * $scale, $pad + $y * $scale, $pad + ($x + 1) * $scale - 1, $pad + ($y + 1) * $scale - 1, $black);
            }
        }
    }
    $qrTmpDir = public_path('tmp');
    if (!is_dir($qrTmpDir)) { mkdir($qrTmpDir, 0755, true); }
    $qrTmpPath = $qrTmpDir . '/qr_alumno_' . $pago->id . '_' . time() . '.png';
    imagepng($qrImg, $qrTmpPath);
    imagedestroy($qrImg);
    $qrSrc = $isPdf ? ('file://' . $qrTmpPath) : '';
    $qrSvgRaw = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($qrText);
    $qrSvgInline = trim(preg_replace('/<\?xml[^?]*\?>/', '', $qrSvgRaw));
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Mensualidad #{{ $pago->id }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 18px; }
        body {
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 14px 0;
        }
        .receipt {
            border: 1px solid #111;
            margin: 0 auto;
            min-height: 520px;
            padding: 18px;
            width: 760px;
        }
        .header-table,
        .info-table,
        .footer-table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table td,
        .info-table td,
        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .title { font-size: 20px; font-weight: 700; margin: 0 0 7px; }
        .subtitle { font-size: 12px; line-height: 1.15; margin: 0; }
        .receipt-no {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.25;
            text-align: right;
            text-transform: uppercase;
        }
        .divider {
            border-top: 1px solid #111;
            height: 1px;
            margin: 14px 0 12px;
        }
        .qr-box {
            border: 1px solid #111;
            display: inline-block;
            padding: 4px;
        }
        .qr-box svg { display: block; height: 120px; width: 120px; }
        .qr-box img {
            display: block;
            height: 120px;
            width: 120px;
        }
        .footer-note { color: #444; font-size: 11px; }
        .thanks-note {
            font-size: 13px;
            font-weight: 700;
            margin-top: 8px;
            text-align: center;
        }
        .qr-footer { text-align: right; width: 140px; }
        .section-title {
            border-bottom: 1px solid #999;
            font-size: 12px;
            font-weight: 700;
            margin: 14px 0 8px;
            padding-bottom: 4px;
            text-transform: uppercase;
        }
        .label { color: #444; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .value { font-size: 13px; line-height: 1.25; margin-top: 2px; }
        .info-table td {
            padding: 0 0 9px;
            width: 50%;
        }
        .detail-table {
            border-collapse: collapse;
            margin-top: 8px;
            width: 100%;
        }
        .detail-table th,
        .detail-table td {
            border: 1px solid #111;
            padding: 7px;
        }
        .detail-table th { background: #eee; text-align: left; }
        .detail-table td { font-weight: 700; }
        .right { text-align: right; }
        .observation-text { margin: 12px 0 18px; }
        .footer-table {
            border-top: 1px solid #999;
            margin-top: 18px;
            padding-top: 10px;
        }
        .footer-table td { padding-top: 10px; }
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
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <table class="header-table">
            <colgroup><col width="60%"><col width="40%"></colgroup>
            <tr>
                <td width="60%">
                    <h1 class="title">{{ optional($dojo)->nombre ?: 'Dojo' }}</h1>
                    @if(optional($dojo)->address)
                        <p class="subtitle">{{ $dojo->address }}</p>
                    @endif
                    @if(optional($dojo)->phone)
                        <p class="subtitle">
                            {{ $dojo->phone }}
                            @if(optional($dojo)->email) · {{ $dojo->email }} @endif
                        </p>
                    @endif
                </td>
                <td width="40%" class="receipt-no">
                    COMPROBANTE DE MENSUALIDAD<br>
                    Nro. {{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="section-title">Información del pago</div>
        <table class="info-table">
            <colgroup><col width="50%"><col width="50%"></colgroup>
            <tr>
                <td width="50%">
                    <div class="label">Alumno</div>
                    <div class="value">{{ optional($person)->first_name ?: 'N/A' }}</div>
                </td>
                <td width="50%">
                    <div class="label">Documento</div>
                    <div class="value">{{ optional($person)->documentType ?: 'CI' }} {{ optional($person)->ci ?: 'N/A' }}</div>
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <div class="label">Dojo / Sucursal</div>
                    <div class="value">{{ optional($dojo)->nombre ?: 'Sin dojo' }}</div>
                </td>
                <td width="50%">
                    <div class="label">Período</div>
                    <div class="value">{{ $periodo }}</div>
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <div class="label">Fecha de pago</div>
                    <div class="value">{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</div>
                </td>
                <td width="50%">
                    <div class="label">Cobrado por</div>
                    <div class="value">{{ $cobrador }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">{{ $esPagoCompleto ? 'Detalle de mensualidad' : 'Pago parcial' }}</div>
        <table class="detail-table">
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
                    <th>Pago recibido — {{ $periodo }}</th>
                    <th class="right">Bs {{ number_format((float) $pago->monto, 2, '.', ',') }}</th>
                </tr>
                @endif
            </tbody>
        </table>

        @if($pago->observacion)
            <div class="section-title">Observación</div>
            <p class="observation-text">{{ $pago->observacion }}</p>
        @endif

        <table class="footer-table">
            <colgroup><col width="75%"><col width="25%"></colgroup>
            <tr>
                <td width="75%" class="footer-note">
                    Comprobante generado por Kaiteki.<br>
                    Escanee el QR para ver alumno, mes y monto.
                </td>
                <td width="25%" class="qr-footer">
                    <div class="qr-box">
                        @if($isPdf)
                            <img src="{{ $qrSrc }}" width="120" height="120" alt="QR">
                        @else
                            {!! $qrSvgInline !!}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="thanks-note">
            Gracias por su preferencia.
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
