@php
    $mensualidad = $pago->mensualidad;
    $alumno = $pago->alumno;
    $person = optional($alumno)->person;
    $dojo = optional($alumno)->dojo;
    $periodo = optional($mensualidad)->periodo ? \Carbon\Carbon::parse($mensualidad->periodo)->format('d/m/Y') : 'N/A';
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
    $qrUrl = \Illuminate\Support\Facades\URL::signedRoute('alumno.mensualidades.pago.comprobante.public', ['id' => $pago->id]);
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
            margin-top: 10px;
            padding: 6px;
        }
        .qr-hint {
            font-size: 9px;
            font-weight: 400;
            margin-top: 4px;
            max-width: 130px;
            word-break: break-word;
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
                <div class="qr-box">
                    <div id="qr-comprobante"></div>
                </div>
                <div class="qr-hint">Escanee para ver el comprobante detallado.</div>
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
                    <td>Mensualidad con fecha {{ $periodo }}</td>
                    <td class="right">Bs {{ number_format((float) optional($mensualidad)->monto, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td>Descuento</td>
                    <td class="right">Bs {{ number_format((float) optional($mensualidad)->descuento, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td>Beca</td>
                    <td class="right">Bs {{ number_format((float) optional($mensualidad)->beca, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td>Mora</td>
                    <td class="right">Bs {{ number_format((float) optional($mensualidad)->mora, 2, '.', ',') }}</td>
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
    </div>

    <div class="actions">
        <button class="btn" onclick="window.print()">Imprimir</button>
        <button class="btn" onclick="window.close()">Cerrar</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            if (window.QRCode) {
                new QRCode(document.getElementById('qr-comprobante'), {
                    text: @json($qrUrl),
                    width: 118,
                    height: 118,
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            setTimeout(function() {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>
