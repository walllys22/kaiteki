<div class="side-menu sidebar-inverse">
    <style>
        @keyframes rubberBand {
            0% { transform: scale3d(1, 1, 1); }
            30% { transform: scale3d(1.25, 0.75, 1); }
            40% { transform: scale3d(0.75, 1.25, 1); }
            50% { transform: scale3d(1.15, 0.85, 1); }
            65% { transform: scale3d(0.95, 1.05, 1); }
            75% { transform: scale3d(1.05, 0.95, 1); }
            100% { transform: scale3d(1, 1, 1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        .side-menu .nav li a .icon {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .side-menu .nav li a:hover .icon {
            animation: rubberBand 1s both;
            text-shadow: 0 0 10px rgba(255,255,255,0.8);
        }

        .logo-icon-container img {
            animation: float 4s ease-in-out infinite;
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .navbar-brand:hover .logo-icon-container img {
            transform: rotate(360deg) scale(1.1);
        }

        .side-menu .avatar {
            transition: transform 0.3s ease;
        }

        .side-menu .avatar:hover {
            transform: scale(1.15);
            animation: pulse-glow 1.5s infinite;
        }

        .dojo-badge {
            margin-top: 10px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .dojo-badge img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.75);
            background: #fff;
            margin-bottom: 8px;
        }

        .dojo-badge .dojo-label {
            font-size: 11px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .dojo-badge .dojo-name {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }
    </style>

    <nav class="navbar navbar-default" role="navigation">
        <div class="side-menu-container">
            <div class="navbar-header" style="background-color: #28467e">
                <a class="navbar-brand" href="{{ route('voyager.dashboard') }}">
                    <div class="logo-icon-container">
                        <?php $admin_logo_img = Voyager::setting('admin.icon_image', ''); ?>
                        @if($admin_logo_img == '')
                            <img src="{{ asset('images/icon.png') }}" alt="Logo Icon">
                        @else
                            <img src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
                        @endif
                    </div>
                    <div class="title">{{ \Illuminate\Support\Str::limit(Voyager::setting('admin.title', 'VOYAGER'), 18, '') }}</div>
                </a>
            </div>

            <div class="panel widget center bgimage"
                 style="background-image:url({{ Voyager::image(Voyager::setting('admin.bg_image'), asset('images/bg_image.png')) }}); background-size: cover; background-position: 0px;">
                <div class="dimmer"></div>
                <div class="panel-content">
                    @php
                        $user = \App\Models\User::with(['person', 'dojo'])->find(Auth::id());
                        $userAvatar = asset('images/default.jpg');
                        $dojoLogo = null;

                        if ($user && $user->person && $user->person->image) {
                            $imagePath = $user->person->image;
                            $baseImagePath = str_ends_with(strtolower($imagePath), '.avif')
                                ? str_replace('.avif', '', $imagePath)
                                : pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME);

                            $baseImagePath = str_replace('\\', '/', $baseImagePath);
                            $baseImagePath = preg_replace('#/+#', '/', $baseImagePath);
                            $userAvatar = asset('storage/' . trim($baseImagePath, '/') . '-cropped.webp');
                        }

                        if ($user && $user->dojo && $user->dojo->logo) {
                            $logoPath = $user->dojo->logo;
                            $baseLogoPath = str_ends_with(strtolower($logoPath), '.avif')
                                ? str_replace('.avif', '', $logoPath)
                                : pathinfo($logoPath, PATHINFO_DIRNAME) . '/' . pathinfo($logoPath, PATHINFO_FILENAME);

                            $baseLogoPath = str_replace('\\', '/', $baseLogoPath);
                            $baseLogoPath = preg_replace('#/+#', '/', $baseLogoPath);
                            $dojoLogo = asset('storage/' . trim($baseLogoPath, '/') . '-cropped.webp');
                        }
                    @endphp

                    <img src="{{ $userAvatar }}" class="avatar" alt="{{ Auth::user()->name }} avatar">
                    <h4 style="color: rgb(255, 255, 255) !important">{{ ucwords(Auth::user()->name) }}</h4>
                    <p>{{ Auth::user()->email }}</p>

                    @if($user && $user->dojo_id && $user->dojo)
                        <div class="dojo-badge">
                            @if($dojoLogo)
                                <img src="{{ $dojoLogo }}" alt="{{ $user->dojo->nombre }}">
                            @endif
                            <div class="dojo-label">Sucursal / Dojo</div>
                            <div class="dojo-name">{{ $user->dojo->nombre }}</div>
                        </div>
                    @endif

                    <a href="{{ route('voyager.profile') }}" class="btn btn-primary" style="margin-top: 12px;">
                        {{ __('voyager::generic.profile') }}
                    </a>
                    <div style="clear:both"></div>
                </div>
            </div>
        </div>

        <div id="adminmenu">
            <admin-menu :items="{{ menu('admin', '_json') }}"></admin-menu>
        </div>
    </nav>
</div>
