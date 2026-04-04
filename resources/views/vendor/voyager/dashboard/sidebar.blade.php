<div class="side-menu sidebar-inverse">
    <style>
        /* Keyframes para animaciones avanzadas */
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

        /* Iconos: Efecto elástico y brillo */
        .side-menu .nav li a .icon {
            display: inline-block;
            transition: all 0.3s ease;
        }
        .side-menu .nav li a:hover .icon {
            animation: rubberBand 1s both;
            text-shadow: 0 0 10px rgba(255,255,255,0.8);
        }

        /* Logo: Flotación constante y giro al hover */
        .logo-icon-container img {
            animation: float 4s ease-in-out infinite;
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .navbar-brand:hover .logo-icon-container img {
            transform: rotate(360deg) scale(1.1);
        }

        /* Avatar: Zoom y onda expansiva */
        .side-menu .avatar {
            transition: transform 0.3s ease;
        }
        .side-menu .avatar:hover {
            transform: scale(1.15);
            animation: pulse-glow 1.5s infinite;
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
            </div><!-- .navbar-header -->

            <div class="panel widget center bgimage"
                 style="background-image:url({{ Voyager::image( Voyager::setting('admin.bg_image'), asset('images/bg_image.png') ) }}); background-size: cover; background-position: 0px;">
                <div class="dimmer"></div>
                <div class="panel-content">
                    @php
                        $user = App\Models\User::where('id', Auth::user()->id)->first();
                        if($user->worker)
                        {
                            // if($user->person->image)
                            // {
                            //     $user_avatar = asset('storage/'.str_replace('.', '-cropped.', $user->person->image));
                            // }

                            if ($user->worker->image) {
                                $pathInfo = pathinfo($user->worker->image);
                                $extension = strtolower($pathInfo['extension'] ?? '');

                                if (str_contains($extension, 'avif')) {
                                    $image = str_replace('.avif', '', $user->worker->image);
                                }
                                $user_avatar = asset('storage/' . $image . '-cropped.webp');
                            }
                        }
                    @endphp
                    <img src="{{ $user_avatar }}" class="avatar" alt="{{ Auth::user()->name }} avatar">
                    <h4 style="color:rgb(255, 255, 255) !important">{{ ucwords(Auth::user()->name) }}</h4>
                    <p>{{ Auth::user()->email }}</p>

                    <a href="{{ route('voyager.profile') }}" class="btn btn-primary">{{ __('voyager::generic.profile') }}</a>
                    <div style="clear:both"></div>
                </div>
            </div>

        </div>
        <div id="adminmenu">
            <admin-menu :items="{{ menu('admin', '_json') }}"></admin-menu>
        </div>
    </nav>
</div>
