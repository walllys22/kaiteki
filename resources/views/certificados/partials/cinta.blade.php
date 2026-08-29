{{--
    Cinta (obi) dibujada en SVG con el color del grado y sus franjas.
    Espera $cinta = ['base' => '#hex', 'franja' => '#hex|null', 'puntas' => int, 'texto' => 'NEGRO']
--}}
<svg class="obi" viewBox="0 0 320 108" role="img" aria-label="Cinta {{ $cinta['texto'] }}" preserveAspectRatio="xMidYMid meet">
    <defs>
        <linearGradient id="obi-luz" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" stop-opacity=".22"/>
            <stop offset="45%" stop-color="#ffffff" stop-opacity="0"/>
            <stop offset="100%" stop-color="#000000" stop-opacity=".28"/>
        </linearGradient>
        <filter id="obi-sombra" x="-20%" y="-40%" width="140%" height="200%">
            <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#2b1d12" flood-opacity=".32"/>
        </filter>
    </defs>

    <g filter="url(#obi-sombra)">
        <rect x="6"   y="34" width="126" height="30" rx="3" fill="{{ $cinta['base'] }}"/>
        <rect x="188" y="34" width="126" height="30" rx="3" fill="{{ $cinta['base'] }}"/>

        <path d="M136 62 h26 l-6 42 h-26 z" fill="{{ $cinta['base'] }}"/>
        <path d="M164 62 h26 l6 42 h-26 z" fill="{{ $cinta['base'] }}"/>

        <rect x="128" y="28" width="64" height="42" rx="7" fill="{{ $cinta['base'] }}"/>
        <path d="M128 44 q32 -13 64 0 v14 q-32 13 -64 0 z" fill="#000" opacity=".16"/>

        @if ($cinta['franja'])
            <rect x="6"   y="43" width="126" height="12" fill="{{ $cinta['franja'] }}"/>
            <rect x="188" y="43" width="126" height="12" fill="{{ $cinta['franja'] }}"/>
        @endif

        @for ($i = 0; $i < $cinta['puntas']; $i++)
            <rect x="140" y="{{ 74 + ($i * 9) }}" width="18" height="5" rx="1" fill="#f5f3ee" opacity=".92"/>
            <rect x="168" y="{{ 74 + ($i * 9) }}" width="18" height="5" rx="1" fill="#f5f3ee" opacity=".92"/>
        @endfor

        <rect x="6"   y="34" width="126" height="30" rx="3" fill="url(#obi-luz)"/>
        <rect x="188" y="34" width="126" height="30" rx="3" fill="url(#obi-luz)"/>
        <rect x="128" y="28" width="64"  height="42" rx="7" fill="url(#obi-luz)"/>
    </g>
</svg>
