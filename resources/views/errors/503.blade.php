<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ Voyager::setting("admin.title") }} - Mantenimiento</title>
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
        .maintenance-message {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <h1 class="display-1 fw-bold">503</h1>
            <p class="fs-3"> <span class="text-danger">Sistema en mantenimiento!</span></p>
            
            <div class="maintenance-message">
                <p class="lead">
                    Estamos realizando tareas de mantenimiento para mejorar nuestro servicio.
                    <br>
                    Por favor, intente nuevamente más tarde. Gracias por su comprensión.
                </p>
            </div>
            
            <div class="error-details text-start">
                <p><strong>¿Qué está ocurriendo?</strong></p>
                <ul>
                    <li>Actualización del sistema para mejor rendimiento</li>
                    <li>Implementación de nuevas características</li>
                    <li>Resolución de problemas técnicos</li>
                    <li>Mejoras de seguridad</li>
                </ul>
                <p><strong>Tiempo estimado:</strong></p>
                <ul>
                    <li>El servicio volverá lo antes posible</li>
                    <li>Normalmente estas operaciones tardan menos de 1 hora</li>
                    <li>Le recomendamos intentar de nuevo en 30 minutos</li>
                </ul>
            </div>
            
            {{-- <img src="{{asset('images/errors/503.gif')}}" width="250" height="200" border="0" alt="Sistema en mantenimiento"> --}}
            <br>
            
            <div class="d-flex justify-content-center gap-3 mt-3">
                {{-- <button onclick="window.location.reload()" class="btn btn-primary refresh-btn">
                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                </button> --}}
                <a href="{{ url('/') }}" class="btn btn-secondary"> <i class="bi bi-arrow-clockwise"></i> Reintentar</a>

            </div>

            
            <p class="mt-3 text-muted small">
                Disculpe las molestias ocasionadas. Para consultas urgentes, contacte al soporte técnico.
                <br>
                Código de error: ERR_SERVICE_UNAVAILABLE
            </p>
        </div>
    </div>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <script>
        // Intenta reconectar automáticamente cada 5 minutos
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 300000 ms = 5 minutos
    </script>
</body>

</html>