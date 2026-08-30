<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Auditor-IA — Analiza contratos legales con inteligencia artificial antes de firmar.')">
    <title>@yield('title', 'Auditor-IA')</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

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
<body class="bg-gunmetal-dark text-[#E2F1FF] antialiased min-h-screen flex flex-col selection:bg-navy-dark selection:text-icy">

    <header class="w-full border-b-2 border-gunmetal bg-gunmetal-dark sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center group">
                <img src="{{ asset('images/text-logo.png') }}" alt="Auditor-IA" class="h-14 md:h-16 w-auto object-contain group-hover:brightness-110 transition-all">
            </a>

            <nav class="flex items-center gap-3">
                <a href="{{ route('landing') }}#planes" class="hidden sm:inline-flex px-4 py-2 text-[13px] font-mono font-medium text-icy/80 hover:text-icy hover:underline transition-colors uppercase tracking-wider">
                    Planes
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2 text-[13px] font-mono font-medium text-icy/80 hover:text-icy hover:underline transition-colors uppercase tracking-wider">
                    Iniciar sesión
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                    Comenzar gratis
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t-2 border-gunmetal bg-gunmetal-dark py-10">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6 text-[13px] text-icy/70">
            <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 text-center sm:text-left">
                <a href="{{ route('landing') }}" class="group inline-flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA Logo" class="h-12 w-auto object-contain transition-transform group-hover:scale-105 drop-shadow-[0_0_8px_rgba(164,216,255,0.25)]">
                </a>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-6">
                    <p class="font-mono">&copy; {{ date('Y') }} Auditor-IA. Todos los derechos reservados.</p>
                    <a href="{{ route('terms') }}" class="hover:text-icy underline underline-offset-2 transition-colors">Términos y Condiciones</a>
                </div>
            </div>
            <p class="font-mono text-[12px] text-icy/60 text-center md:text-right">No constituye asesoría legal. Consulta siempre con un abogado.</p>
        </div>
    </footer>

    <x-terms-popup-modal />

</body>
</html>
