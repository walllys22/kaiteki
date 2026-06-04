<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kaiteki</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:   #c0392b;
            --dark:  #0d0f14;
            --card:  #13161f;
            --line:  #1e2130;
            --text:  #dde2f0;
            --muted: #6b7290;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { text-decoration: none; color: inherit; }

        /* NAV */
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 2rem;
            border-bottom: 1px solid var(--line);
        }

        .brand {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -.4px;
        }
        .brand b { color: var(--red); font-weight: 800; }

        .nav-links { display: flex; gap: .5rem; }

        .btn {
            display: inline-flex; align-items: center;
            padding: .42rem 1rem;
            border-radius: 7px;
            font-size: .82rem;
            font-weight: 500;
            transition: .15s;
            border: 1px solid transparent;
        }
        .btn-outline {
            border-color: var(--line);
            color: var(--muted);
        }
        .btn-outline:hover { border-color: var(--muted); color: var(--text); }
        .btn-solid {
            background: var(--red);
            color: #fff;
        }
        .btn-solid:hover { opacity: .88; }

        /* HERO */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1.5rem 3rem;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0; left: 50%; transform: translateX(-50%);
            width: 600px; height: 300px;
            background: radial-gradient(ellipse at 50% 0%, rgba(192,57,43,.2) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-eyebrow {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 1.1rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5.5vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -1.5px;
            line-height: 1.1;
            max-width: 640px;
        }

        .hero h1 em {
            font-style: normal;
            color: var(--red);
        }

        .hero-desc {
            margin-top: 1rem;
            font-size: .95rem;
            color: var(--muted);
            max-width: 400px;
        }

        .hero-cta {
            margin-top: 2rem;
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-cta {
            padding: .65rem 1.5rem;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
        }

        /* FEATURES */
        .features {
            max-width: 860px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            width: 100%;
        }

        .feat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: var(--line);
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
        }

        .feat {
            background: var(--card);
            padding: 1.4rem 1.3rem;
            transition: background .15s;
        }
        .feat:hover { background: #161929; }

        .feat-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: rgba(192,57,43,.13);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: .85rem;
            color: var(--red);
        }
        .feat-icon svg { width: 17px; height: 17px; }

        .feat h3 {
            font-size: .82rem;
            font-weight: 600;
            margin-bottom: .3rem;
        }
        .feat p {
            font-size: .77rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* FOOTER */
        footer {
            border-top: 1px solid var(--line);
            padding: .9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .75rem;
            color: var(--muted);
            flex-wrap: wrap;
            gap: .4rem;
        }

        @media (max-width: 640px) {
            nav { padding: .9rem 1rem; }
            .feat-grid { grid-template-columns: repeat(2, 1fr); }
            .features { padding: 2rem 1rem; }
            footer { justify-content: center; }
        }

        @media (max-width: 400px) {
            .feat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="brand">kai<b>teki</b></div>

        @if (Route::has('login'))
            <div class="nav-links">
                @auth
                    <a href="{{ url('/admin') }}" class="btn btn-solid">Ir al panel</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Iniciar sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-solid">Registrarse</a>
                    @endif
                @endauth
            </div>
        @endif
    </nav>

    <section class="hero">
        <p class="hero-eyebrow">Sistema de gestión de dojo</p>

        <h1>Gestiona tu dojo<br>con <em>orden y precisión</em></h1>

        <p class="hero-desc">Alumnos, grados, pagos y asistencias — todo en un solo lugar para cada sucursal.</p>

        <div class="hero-cta">
            @auth
                <a href="{{ url('/admin') }}" class="btn btn-solid btn-cta">Ir al panel →</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-solid btn-cta">Iniciar sesión →</a>
            @endauth
        </div>
    </section>

    <div class="features">
        <div class="feat-grid">

            <div class="feat">
                <div class="feat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3>Alumnos</h3>
                <p>Ficha completa, tutores y condiciones de salud por sucursal.</p>
            </div>

            <div class="feat">
                <div class="feat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3>Grados</h3>
                <p>Repasos, exámenes y certificados con progresión Kyu → Dan.</p>
            </div>

            <div class="feat">
                <div class="feat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3>Asistencias</h3>
                <p>Registro diario por horario con historial por alumno.</p>
            </div>

            <div class="feat">
                <div class="feat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3>Mensualidades</h3>
                <p>Planes automáticos, descuentos y comprobantes con QR.</p>
            </div>

            <div class="feat">
                <div class="feat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3>Multi-sucursal</h3>
                <p>Aislamiento total por dojo. Globales ven todo, operadores solo su rama.</p>
            </div>

            <div class="feat">
                <div class="feat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3>Consulta inter-dojo</h3>
                <p>Busca alumnos de otras sucursales en modo solo lectura.</p>
            </div>

        </div>
    </div>

    <footer>
        <span>© {{ date('Y') }} Kaiteki</span>
        <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }} · PHP v{{ PHP_VERSION }}</span>
    </footer>

</body>
</html>
