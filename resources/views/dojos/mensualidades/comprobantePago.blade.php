@php
    $mensualidad = $pago->mensualidad;
    $dojo        = optional($mensualidad)->dojo;
    $isPdf       = $isPdf ?? false;
    $periodo     = optional($mensualidad)->fecha_inicio
        ? $mensualidad->fecha_inicio->format('d/m/Y') . ' al ' . $mensualidad->fecha_fin->format('d/m/Y')
        : 'N/A';
    $logo        = ($dojo && $dojo->logo) ? asset($dojo->logo) : null;
    $cobrador    = optional($pago->registerUser)->name ?: 'Sistema';

    $qrText = implode("\n", [
        'COMPROBANTE MENSUALIDAD DOJO',
        'Nro: ' . str_pad($pago->id, 6, '0', STR_PAD_LEFT),
        'Dojo: ' . optional($dojo)->nombre,
        'Período: ' . $periodo,
        'Pago: Bs ' . number_format((float) $pago->monto, 2),
        'Fecha: ' . $pago->fecha->format('d/m/Y'),
    ]);

    $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($qrText);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante Dojo #{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</title>
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
        .brand { align-items: center; display: flex; gap: 12px; }
        .dojo-logo {
            border: 1px solid #111;
            filter: grayscale(100%);
            height: 72px;
            object-fit: contain;
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
        .qr-box svg {
            display: block;
            width: 120px;
            height: 120px;
        }
        .footer {
            align-items: flex-end;
            border-top: 1px solid #999;
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 10px;
        }
        .footer-note { color: #444; font-size: 11px; }
        .thanks-note {
            font-size: 13px;
            font-weight: 700;
            margin-top: 14px;
            text-align: center;
        }
        .qr-footer { text-align: center; width: 136px; }
        .section-title {
            border-bottom: 1px solid #999;
            font-size: 12px;
            font-weight: 700;
            margin: 14px 0 8px;
            padding-bottom: 4px;
            text-transform: uppercase;
        }
        .grid { display: grid; gap: 8px 16px; grid-template-columns: 1fr 1fr; }
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
                @if($logo)
                    <img src="{{ $logo }}" class="dojo-logo" alt="Logo" onerror="this.style.display='none';">
                @endif
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

        <div class="section-title">Información del pago</div>
        <div class="grid">
            <div>
                <div class="label">Dojo / Sucursal</div>
                <div class="value">{{ optional($dojo)->nombre ?: 'N/A' }}</div>
            </div>
            <div>
                <div class="label">Período</div>
                <div class="value">{{ $periodo }}</div>
            </div>
            <div>
                <div class="label">Fecha de pago</div>
                <div class="value">{{ $pago->fecha->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="label">Cobrado por</div>
                <div class="value">{{ $cobrador }}</div>
            </div>
        </div>

        <div class="section-title">Detalle</div>
        <table>
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
            <p>{{ $pago->observacion }}</p>
        @endif

        <div class="footer">
            <div class="footer-note">
                Comprobante generado por Kaiteki.<br>
                Escanee el QR para verificar datos del pago.
            </div>
            <div class="qr-footer">
                <div class="qr-box">
                    {!! $qrSvg !!}
                </div>
            </div>
        </div>

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
