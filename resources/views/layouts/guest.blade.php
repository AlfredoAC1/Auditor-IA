<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Auditor-IA — Analiza contratos legales con inteligencia artificial antes de firmar.')">
    <title>@yield('title', 'Auditor-IA')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #15181B;
            color: #E2F1FF;
        }
        h1, h2, h3, .font-serif, .font-heading {
            font-family: 'Space Grotesk', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="bg-[#15181B] text-[#E2F1FF] antialiased min-h-screen flex flex-col selection:bg-[#0F1E36] selection:text-[#A4D8FF]">

    <header class="w-full border-b-2 border-[#35393C] bg-[#15181B] sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA Logo" class="h-9 w-auto object-contain transition-transform group-hover:scale-105 drop-shadow-[0_0_8px_rgba(164,216,255,0.2)]">
                <img src="{{ asset('images/text-logo.png') }}" alt="Auditor-IA" class="h-6 w-auto object-contain group-hover:brightness-110 transition-all">
            </a>

            <nav class="flex items-center gap-3">
                <a href="{{ route('landing') }}#planes" class="hidden sm:inline-flex px-4 py-2 text-[13px] font-mono font-medium text-[#A4D8FF]/80 hover:text-[#A4D8FF] hover:underline transition-colors uppercase tracking-wider">
                    Planes
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2 text-[13px] font-mono font-medium text-[#A4D8FF]/80 hover:text-[#A4D8FF] hover:underline transition-colors uppercase tracking-wider">
                    Iniciar sesión
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                    Comenzar gratis
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t-2 border-[#35393C] bg-[#15181B] py-8">
        <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[13px] text-[#A4D8FF]/70">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-6">
                <p class="font-mono">&copy; {{ date('Y') }} Auditor-IA. Todos los derechos reservados.</p>
                <a href="{{ route('terms') }}" class="hover:text-[#A4D8FF] underline underline-offset-2 transition-colors">Términos y Condiciones</a>
            </div>
            <p class="font-mono text-[12px] text-[#A4D8FF]/60">No constituye asesoría legal. Consulta siempre con un abogado.</p>
        </div>
    </footer>

    <x-terms-popup-modal />

</body>
</html>


