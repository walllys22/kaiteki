<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ Voyager::setting("admin.title") }} - Servicio Suspendido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ asset('images/icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif
</head>

<body>
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center" style="max-width:560px;">
            <div style="font-size:5rem; color:#dc3545;">
                <i class="bi bi-lock-fill"></i>
            </div>
            <h1 class="display-4 fw-bold mt-2">Servicio Suspendido</h1>
            <p class="fs-5 text-danger">Su sucursal no tiene una mensualidad vigente.</p>

            <div class="bg-light p-4 rounded text-start mb-4">
                <p class="mb-2"><strong>¿Por qué estoy viendo esto?</strong></p>
                <ul class="mb-0">
                    <li>La mensualidad de su sucursal está pendiente de pago o ha vencido.</li>
                    <li>El acceso al sistema fue suspendido automáticamente.</li>
                </ul>
                <p class="mt-3 mb-0"><strong>¿Qué hacer?</strong></p>
                <ul class="mb-0">
                    <li>Contacte al administrador del sistema para regularizar el pago.</li>
                </ul>
            </div>

            <a href="{{ url('/') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-clockwise"></i> Reintentar
            </a>
            <a href="{{ route('voyager.logout') }}" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
            </a>

            <p class="mt-4 text-muted small">Código: ERR_SERVICE_SUSPENDED</p>
        </div>
    </div>
</body>

</html>
