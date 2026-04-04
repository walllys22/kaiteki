<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ Voyager::setting("admin.title") }} - Error de conexión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Favicon -->
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ asset('images/icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif
    <style>
        .error-details {
            max-width: 600px;
            margin: 20px auto;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .refresh-btn {
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <h1 class="display-1 fw-bold">500</h1>
            <p class="fs-3"> <span class="text-danger">Error!</span> Problemas de conexión con el servidor.</p>
            <p class="lead">
                No se ha podido establecer conexión con el servidor. Por favor, intente nuevamente más tarde.
            </p>
            
            <div class="error-details text-start">
                <p><strong>Posibles causas:</strong></p>
                <ul>
                    <li>El servidor no está respondiendo</li>
                    <li>Problemas de red o conexión a internet</li>
                    <li>El servidor está sobrecargado</li>
                    <li>Problemas temporales del servicio</li>
                </ul>
                <p><strong>¿Qué puede hacer?</strong></p>
                <ul>
                    <li>Verificar su conexión a internet</li>
                    <li>Recargar la página después de unos minutos</li>
                    <li>Contactar al administrador si el problema persiste</li>
                </ul>
            </div>
            
            <img src="{{asset('images/errors/connection-error.gif')}}" width="250" height="200" border="0" alt="Error de conexión">
            <br>
            
            <div class="d-flex justify-content-center gap-3 mt-3">
                {{-- <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
                <button onclick="window.location.reload()" class="btn btn-secondary refresh-btn">
                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                </button> --}}

                <a href="{{ url('/') }}" class="btn btn-secondary"> <i class="bi bi-arrow-clockwise"></i> Reintentar</a>
            </div>
            
            <p class="mt-3 text-muted small">
                Si el problema persiste, por favor contacte al soporte técnico.
                <br>
                Código de error: ERR_CONNECTION_FAILED
            </p>
        </div>
    </div>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <script>
        // Intenta reconectar automáticamente cada 30 segundos
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>

</html>