<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#16110d">
    <title>Certificado no valido</title>
    <style>
        :root {
            --sumi:      #1b1512;
            --sumi-2:    #4a3f38;
            --washi:     #f6f1e6;
            --linea:     #ddd2bd;
            --bermellon: #b3271e;

            --pad:      clamp(18px, 6vw, 32px);
            --t-titulo: clamp(21px, 6.4vw, 27px);
        }

        * { box-sizing: border-box; }

        html {
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }

        img, svg { height: auto; max-width: 100%; }

        body {
            align-items: center;
            background:
                radial-gradient(circle at 50% 0%, rgba(179, 39, 30, .22), transparent 55%),
                #16110d;
            color: var(--sumi);
            display: flex;
            font-family: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            overflow-x: hidden;
            padding: clamp(16px, 5vw, 32px);
        }

        .aviso {
            animation: aparecer .5s cubic-bezier(.22, .9, .28, 1) both;
            background: var(--washi);
            background-image:
                repeating-linear-gradient(90deg, rgba(120, 96, 66, .05) 0 1px, transparent 1px 4px),
                repeating-linear-gradient(0deg,  rgba(120, 96, 66, .035) 0 1px, transparent 1px 7px);
            border-radius: 3px;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, .3), 0 26px 60px rgba(0, 0, 0, .5);
            max-width: 470px;
            overflow: hidden;
            text-align: center;
            width: 100%;
        }

        @keyframes aparecer {
            from { opacity: 0; transform: translateY(16px) scale(.97); }
            to   { opacity: 1; transform: none; }
        }

        .franja-alerta {
            background: repeating-linear-gradient(-45deg, var(--bermellon) 0 12px, #8f1d16 12px 24px);
            height: 8px;
        }

        .cuerpo { padding: var(--pad); }

        /* Sello de rechazo: se estampa torcido y queda tachado. */
        .sello-nulo {
            animation: estampar-nulo .55s cubic-bezier(.2, 1.25, .5, 1) .1s both;
            border: 3px double var(--bermellon);
            border-radius: 50%;
            color: var(--bermellon);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: clamp(11px, 3.4vw, 13px);
            font-weight: 800;
            height: clamp(84px, 26vw, 104px);
            letter-spacing: .08em;
            margin-bottom: 18px;
            position: relative;
            transform: rotate(-11deg);
            width: clamp(84px, 26vw, 104px);
        }

        .sello-nulo::after {
            background: var(--bermellon);
            content: "";
            height: 3px;
            left: 8%;
            position: absolute;
            top: 50%;
            transform: rotate(-16deg);
            width: 84%;
        }

        @keyframes estampar-nulo {
            0%   { opacity: 0; transform: rotate(-11deg) scale(2.2); }
            65%  { opacity: 1; transform: rotate(-11deg) scale(.94); }
            100% { opacity: 1; transform: rotate(-11deg) scale(1); }
        }

        h1 {
            color: var(--bermellon);
            font-size: var(--t-titulo);
            letter-spacing: .01em;
            line-height: 1.25;
            margin: 0 0 12px;
        }

        p {
            color: var(--sumi-2);
            font-size: clamp(14px, 3.8vw, 15px);
            line-height: 1.65;
            margin: 0 auto 14px;
            max-width: 38ch;
        }

        .motivo {
            background: rgba(179, 39, 30, .07);
            border-left: 3px solid var(--bermellon);
            border-radius: 2px;
            color: var(--sumi);
            font-size: clamp(13px, 3.6vw, 14px);
            line-height: 1.6;
            margin: 18px 0 0;
            padding: 14px 16px;
            text-align: left;
        }

        .pie {
            border-top: 1px solid var(--linea);
            color: var(--sumi-2);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            line-height: 1.7;
            padding: 16px var(--pad) 20px;
        }

        .ref {
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
            font-size: 11px;
            letter-spacing: .06em;
            opacity: .7;
        }

        @media (prefers-reduced-motion: reduce) {
            .aviso, .sello-nulo { animation: none; }
        }
    </style>
</head>
<body>
    <main class="aviso">
        <div class="franja-alerta" aria-hidden="true"></div>

        <div class="cuerpo">
            <div class="sello-nulo" role="img" aria-label="Certificado no valido">NO<br>VALIDO</div>

            <h1>Este certificado no pudo verificarse</h1>

            {{-- Mensaje unico a proposito: no se informa el motivo del rechazo.
                 Distinguir "enlace alterado" de "registro inexistente" le indica
                 a quien manipula la direccion por donde seguir probando. --}}
            <p>
                No corresponde a ningun certificado verificable emitido por el dojo.
            </p>

            <div class="motivo">
                Escanea el codigo QR directamente del documento impreso. Si aun asi no se
                verifica, consulta con el dojo que emitio el certificado.
            </div>
        </div>

        <div class="pie">
            Ante cualquier duda, verifica el documento con el dojo emisor.<br>
            <span class="ref">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </main>
</body>
</html>
