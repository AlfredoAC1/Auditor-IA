<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Auditor-IA — Analiza contratos legales con inteligencia artificial antes de firmar.')">
    <title>@yield('title', 'Auditor-IA')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #0b0b0b;
        }
    </style>
</head>
<body class="bg-[#0b0b0b] text-gray-200 antialiased min-h-screen flex flex-col">

    <header class="w-full border-b border-zinc-800/60 bg-[#0b0b0b]/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/10">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-tight text-white group-hover:text-zinc-200 transition-colors">Auditor-IA</span>
            </a>

            <nav class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-[13px] font-medium text-zinc-400 hover:text-white transition-colors">
                    Iniciar sesión
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[13px] font-bold text-white transition-all">
                    Comenzar gratis
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-zinc-800/60 py-8">
        <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[13px] text-zinc-500">
            <p>&copy; {{ date('Y') }} Auditor-IA. Todos los derechos reservados.</p>
            <p class="text-zinc-600">No constituye asesoría legal. Consulta siempre con un abogado.</p>
        </div>
    </footer>

</body>
</html>
