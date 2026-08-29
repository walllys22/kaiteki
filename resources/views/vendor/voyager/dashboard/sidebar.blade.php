@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $user = \App\Models\User::with(['person', 'dojo'])->find(Auth::id());

    $resolvePublicImage = function (?string $path, string $defaultAsset) {
        if (!$path) {
            return asset($defaultAsset);
        }

        $normalizedPath = str_replace('\\', '/', trim($path, '/'));
        $filename = pathinfo($normalizedPath, PATHINFO_FILENAME);
        $directory = pathinfo($normalizedPath, PATHINFO_DIRNAME);
        $directory = $directory === '.' ? '' : trim($directory, '/');

        $croppedRelativePath = ltrim(($directory ? $directory . '/' : '') . $filename . '-cropped.webp', '/');

        if (Storage::disk('public')->exists($croppedRelativePath)) {
            return asset('storage/' . $croppedRelativePath);
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            return asset('storage/' . $normalizedPath);
        }

        return asset($defaultAsset);
    };

    $adminLogoImage = Voyager::setting('admin.icon_image', '');
    $sidebarLogo = $adminLogoImage ? Voyager::image($adminLogoImage) : asset('images/icon.png');
    $userAvatar = $resolvePublicImage(optional($user?->person)->image, 'images/default.jpg');
    $dojoLogo = $user && $user->dojo ? $resolvePublicImage($user->dojo->logo, 'images/default.jpg') : asset('images/default.jpg');

    // Selector de dojo activo: solo para usuarios globales (users.dojo_id NULL en base)
    $esGlobal = $user ? $user->isGlobal() : false;
    $dojoActivoId = $esGlobal ? session(\App\Models\User::DOJO_ACTIVO_SESSION_KEY) : null;
    $dojosDisponibles = $esGlobal
        ? \App\Models\Dojo::whereNull('deleted_at')->orderBy('nombre')->get(['id', 'nombre'])
        : collect();
@endphp

<div class="side-menu sidebar-inverse">
    <style>
        .sidebar-brand {
            background: linear-gradient(135deg, #1f95d0, #2d78b9);
            box-shadow: inset 0 -1px 0 rgba(255,255,255,0.08);
        }

        .sidebar-brand .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 64px;
            padding: 12px 16px;
        }

        .sidebar-brand .logo-icon-container img {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            object-fit: cover;
            background: rgba(255,255,255,0.14);
            padding: 4px;
        }

        .sidebar-brand .title {
            color: #fff;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .sidebar-user-panel {
            padding: 16px 14px 14px;
            background: linear-gradient(180deg, #2f3b46 0%, #26313a 100%);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-user-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.75);
            background: #fff;
            flex-shrink: 0;
        }

        .sidebar-user-meta {
            min-width: 0;
            flex: 1;
        }

        .sidebar-user-name {
            margin: 0;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-email {
            margin: 3px 0 0;
            color: rgba(255,255,255,0.7);
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-dojo-card {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
        }

        .sidebar-dojo-logo {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: cover;
            background: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            flex-shrink: 0;
        }

        .sidebar-dojo-meta {
            min-width: 0;
            flex: 1;
        }

        .sidebar-dojo-label {
            display: block;
            color: rgba(255,255,255,0.62);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 2px;
        }

        .sidebar-dojo-name {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-profile-link {
            display: inline-block;
            margin-top: 12px;
            color: #9fd7ff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .sidebar-profile-link:hover,
        .sidebar-profile-link:focus {
            color: #fff;
            text-decoration: none;
        }
        .sidebar-dojo-switcher {
            padding: 10px 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-dojo-switcher .sidebar-dojo-label {
            display: block;
            font-size: 10px;
            letter-spacing: .08em;
            opacity: .7;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .sidebar-dojo-switcher select {
            width: 100%;
            padding: 6px 8px;
            font-size: 12px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.10);
            color: #fff;
        }

        .sidebar-dojo-switcher select option {
            color: #333;
        }

        .sidebar-dojo-switcher .sidebar-dojo-hint {
            display: block;
            margin-top: 5px;
            font-size: 10px;
            opacity: .65;
        }
    </style>

    <nav class="navbar navbar-default" role="navigation">
        <div class="side-menu-container">
            <div class="navbar-header sidebar-brand">
                <a class="navbar-brand" href="{{ route('voyager.dashboard') }}">
                    <div class="logo-icon-container">
                        <img src="{{ $sidebarLogo }}" alt="Logo">
                    </div>
                    <div class="title">{{ Str::limit(Voyager::setting('admin.title', 'VOYAGER'), 18, '') }}</div>
                </a>
            </div>

            <div class="sidebar-user-panel">
                {{-- <div class="sidebar-user-row">
                    <img src="{{ $userAvatar }}" class="sidebar-user-avatar" alt="{{ Auth::user()->name }}">
                    <div class="sidebar-user-meta">
                        <p class="sidebar-user-name">{{ ucwords(Auth::user()->name) }}</p>
                        <p class="sidebar-user-email">{{ Auth::user()->email }}</p>
                    </div>
                </div> --}}

                @if($esGlobal)
                    <div class="sidebar-dojo-switcher">
                        <span class="sidebar-dojo-label">Dojo activo</span>
                        <form method="POST" action="{{ route('contexto.dojo.update') }}" id="form-contexto-dojo">
                            @csrf
                            <select name="dojo_id" onchange="document.getElementById('form-contexto-dojo').submit();">
                                {{-- Opcion "todas las sucursales" deshabilitada a proposito: el usuario
                                     global siempre debe estar parado en un dojo concreto para no ver
                                     informacion mezclada. Descomentar para volver a la vista global.
                                <option value="">Todos los dojos</option>
                                --}}
                                @foreach($dojosDisponibles as $dojoOption)
                                    <option value="{{ $dojoOption->id }}" {{ (int) $dojoActivoId === (int) $dojoOption->id ? 'selected' : '' }}>
                                        {{ $dojoOption->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        <span class="sidebar-dojo-hint">Viendo solo esta sucursal.</span>
                    </div>
                @endif

                @if($user && $user->dojo_id && $user->dojo)
                    <div class="sidebar-dojo-card">
                        <img src="{{ $dojoLogo }}" class="sidebar-dojo-logo" alt="{{ $user->dojo->nombre }}">
                        <div class="sidebar-dojo-meta">
                            <span class="sidebar-dojo-label">DOJO</span>
                            <div class="sidebar-dojo-name">{{ $user->dojo->nombre }}</div>
                        </div>
                    </div>
                @endif

                <a href="{{ route('voyager.profile') }}" class="sidebar-profile-link">
                    {{ __('voyager::generic.profile') }}
                </a>
            </div>
        </div>

        <div id="adminmenu">
            <admin-menu :items="{{ menu('admin', '_json') }}"></admin-menu>
        </div>
    </nav>
</div>
