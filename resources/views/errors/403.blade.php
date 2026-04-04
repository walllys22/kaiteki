<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ Voyager::setting("admin.title") }} - Permiso denegado</title>
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
            <h1 class="display-1 fw-bold">403</h1>
            <p class="fs-3"> <span class="text-danger">Acceso denegado!</span> No tienes permisos suficientes.</p>
            <p class="lead">
                Tu cuenta no tiene los privilegios necesarios para acceder a esta página o realizar esta acción.
            </p>
            
            <div class="error-details text-start">
                <p><strong>Posibles causas:</strong></p>
                <ul>
                    <li>Tu cuenta no tiene asignados los permisos necesarios</li>
                    <li>Estás intentando acceder a un área restringida</li>
                    <li>Tu rol de usuario no permite esta acción</li>
                    <li>La página requiere autenticación adicional</li>
                </ul>
                <p><strong>¿Qué puede hacer?</strong></p>
                <ul>
                    <li>Verificar que has iniciado sesión correctamente</li>
                    <li>Contactar al administrador para solicitar los permisos</li>
                    <li>Volver a la página anterior o al inicio</li>
                </ul>
            </div>
            
            {{-- <img src="{{asset('images/errors/503.gif')}}" width="250" height="200" border="0" alt="Permiso denegado"> --}}
            <br>
            
            <div class="d-flex justify-content-center gap-3 mt-3">
                <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver atrás
                </a>
            </div>
            
            <p class="mt-3 text-muted small">
                Si crees que esto es un error, por favor contacta al administrador del sistema.
                <br>
                Código de error: ERR_ACCESS_DENIED
            </p>
        </div>
    </div>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>

</html>