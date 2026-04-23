<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Alumnos</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 10px 0; font-size: 18px; color: #1e3a8a; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #777; }
        .status-active { color: #059669; }
        .status-inactive { color: #d97706; }
        .logo { position: absolute; left: 10px; top: 10px; width: 80px; z-index: 1000; }
        /* Estilos para los botones */
        .pdf-actions {
            text-align: right;
            margin-bottom: 15px;
            position: relative; /* Para que no interfiera con el logo */
            z-index: 999;
        }
        .pdf-actions a {
            display: inline-block;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            font-size: 12px;
            cursor: pointer;
        }
        .pdf-actions .print-button {
            background-color: #4CAF50; /* Verde */
        }
        .pdf-actions .cancel-button {
            background-color: #f44336; /* Rojo */
        }
        /* Ocultar botones al imprimir */
        @media print {
            .pdf-actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="pdf-actions">
        <a href="javascript:window.print()" class="print-button">Imprimir</a>
        <a href="javascript:history.back()" class="cancel-button">Cancelar</a>
    </div>
    @if($dojo && $dojo->logo)
        @php
            $logoPath = str_replace('\\', '/', $dojo->logo);
            $path = public_path('storage/'.$logoPath);
            if (!file_exists($path)) {
                $path = storage_path('app/public/'.$logoPath);
            }
            $imgData = null;
            $mimeType = 'image/jpeg';
            if (file_exists($path)) {
                $imgData = base64_encode(file_get_contents($path));
                $info = getimagesize($path);
                $mimeType = $info ? $info['mime'] : 'image/jpeg';
            }
        @endphp

        @if($imgData)
            <img src="data:{{ $mimeType }};base64,{{ $imgData }}" class="logo">
        @else
            <img src="{{ public_path('images/default.jpg') }}" class="logo">
        @endif
    @endif

    <div class="header">
        <h1>REPORTE GENERAL DE ALUMNOS</h1>
        <p>Generado el: {{ date('d/m/Y H:i A') }}</p>
        @if($search)
            <p><strong>Filtro aplicado:</strong> "{{ $search }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre del Alumno</th>
                <th>Dojo</th>
                <th>Grado</th>
                <th>Horario</th>
                <th>Fecha Ingreso</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $alumno)
            <tr>
                <td>{{ $alumno->person->first_name }}</td>
                <td>{{ $alumno->dojo->nombre ?? 'N/A' }}</td>
                <td>
                    {{ $alumno->grado->tipo ?? '' }} 
                    {{ $alumno->grado->numero ?? '' }} 
                    {{ $alumno->grado->nombre ?? '' }}
                </td>
                <td>
                    {{ $alumno->horario->tipo ?? '' }} 
                    {{ $alumno->horario->nombre ?? '' }}
                </td>
                <td>
                    {{ $alumno->entry_date ? \Carbon\Carbon::parse($alumno->entry_date)->format('d/m/Y') : 'N/A' }}
                </td>
                <td>
                    <span class="{{ $alumno->status == 1 ? 'status-active' : 'status-inactive' }}">
                        {{ $alumno->status == 1 ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Página <script type="text/php">
            if (isset($pdf)) {
                $x = 520; $y = 820; $text = "{PAGE_NUM} de {PAGE_COUNT}";
                $font = $fontMetrics->get_font("helvetica", "bold");
                $size = 8; $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>
    </div>
</body>
</html>
