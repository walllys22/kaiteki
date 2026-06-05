@php
    $mensualidad = $pago->mensualidad;
    $dojo        = optional($mensualidad)->dojo;
    $isPdf       = $isPdf ?? false;
    $numero      = str_pad($pago->id, 6, '0', STR_PAD_LEFT);
    $fechaPago   = optional($pago->fecha)->format('d/m/Y') ?: 'N/A';
    $dojoNombre  = optional($dojo)->nombre ?: 'Dojo';
    $periodo     = optional($mensualidad)->fecha_inicio
        ? optional($mensualidad->fecha_inicio)->format('d/m/Y') . ' al ' . optional($mensualidad->fecha_fin)->format('d/m/Y')
        : 'N/A';
    $logo        = ($dojo && $dojo->logo) ? asset($dojo->logo) : null;
    $cobrador    = optional($pago->registerUser)->name ?: 'Sistema';

    $qrText = implode("\n", [
        'COMPROBANTE MENSUALIDAD DOJO',
        'Nro: ' . $numero,
        'Dojo: ' . $dojoNombre,
        'Período: ' . $periodo,
        'Pago: Bs ' . number_format((float) $pago->monto, 2),
        'Fecha: ' . $fechaPago,
    ]);

    $qrCode = \BaconQrCode\Encoder\Encoder::encode($qrText, \BaconQrCode\Common\ErrorCorrectionLevel::M(), 'UTF-8');
    $matrix = $qrCode->getMatrix();
    $matrixSize = $matrix->getWidth();
    $scale = max(3, (int) floor(120 / $matrixSize));
    $imgSize = $matrixSize * $scale + 16;
    $qrImg = imagecreatetruecolor($imgSize, $imgSize);
    imagefill($qrImg, 0, 0, imagecolorallocate($qrImg, 255, 255, 255));
    $black = imagecolorallocate($qrImg, 0, 0, 0);
    $pad = 8;
    for ($y = 0; $y < $matrixSize; $y++) {
        for ($x = 0; $x < $matrixSize; $x++) {
            if ($matrix->get($x, $y) === 1) {
                imagefilledrectangle($qrImg, $pad + $x * $scale, $pad + $y * $scale, $pad + ($x + 1) * $scale - 1, $pad + ($y + 1) * $scale - 1, $black);
            }
        }
    }
    ob_start(); imagepng($qrImg); $qrPng = ob_get_clean(); imagedestroy($qrImg);
    $qrDataUri = 'data:image/png;base64,' . base64_encode($qrPng);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante Dojo #{{ $numero }}</title>
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
        .observation-text {
            margin: 12px 0 18px;
        }
        .footer-table {
            border-top: 1px solid #999;
            margin-top: 18px;
            padding-top: 10px;
        }
        .footer-table td {
            padding-top: 10px;
        }
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
                    <h1 class="title">{{ $dojoNombre }}</h1>
                    @if(optional($dojo)->address)
                        <p class="subtitle">{{ $dojo->address }}</p>
                    @endif
                    @if(optional($dojo)->phone)
                        <p class="subtitle">{{ $dojo->phone }}</p>
                    @endif
                </td>
                <td width="40%" class="receipt-no">
                    COMPROBANTE DE MENSUALIDAD<br>
                    Nro. {{ $numero }}
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="section-title">Información del pago</div>
        <table class="info-table">
            <colgroup><col width="50%"><col width="50%"></colgroup>
            <tr>
                <td width="50%">
                    <div class="label">Dojo / Sucursal</div>
                    <div class="value">{{ $dojoNombre }}</div>
                </td>
                <td width="50%">
                    <div class="label">Período</div>
                    <div class="value">{{ $periodo }}</div>
                </td>
            </tr>
            <tr>
                <td width="50%">
                    <div class="label">Fecha de pago</div>
                    <div class="value">{{ $fechaPago }}</div>
                </td>
                <td width="50%">
                    <div class="label">Cobrado por</div>
                    <div class="value">{{ $cobrador }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Detalle</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Pago recibido — {{ $periodo }}</th>
                    <th class="right">Bs {{ number_format((float) $pago->monto, 2, '.', ',') }}</th>
                </tr>
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
                    Escanee el QR para verificar datos del pago.
                </td>
                <td width="25%" class="qr-footer">
                    <div class="qr-box">
                        <img src="{{ $qrDataUri }}" width="120" height="120" alt="QR">
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
                setTimeout(function() { window.print(); }, 300);
            });
        </script>
    @endunless
</body>
</html>
