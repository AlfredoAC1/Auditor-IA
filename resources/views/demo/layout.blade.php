<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Auditor-IA Demo Pro - Entorno demostrativo estático de análisis de contratos legales con IA.">
    <title>@yield('title', 'Dashboard Pro Demo - Auditor-IA')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans, Space Grotesk, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind / Assets -->
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
<body class="bg-gunmetal-dark text-[#E2F1FF] antialiased min-h-screen md:h-screen flex flex-col md:flex-row overflow-x-hidden md:overflow-y-hidden selection:bg-navy-dark selection:text-icy">

    @php
        $demoUser = \App\Support\Demo\StaticDemoDataset::user();
        $recentDocs = \App\Support\Demo\StaticDemoDataset::documents();
        $firstDoc = $recentDocs[0] ?? null;
        $activeSection = $activeSection ?? 'dashboard';
    @endphp

    <!-- Static Demo Sidebar -->
    <aside class="w-full md:w-64 md:h-screen md:sticky md:top-0 bg-gunmetal-dark flex flex-col justify-between border-b-2 md:border-b-0 md:border-r-2 border-gunmetal px-4 py-6 shrink-0">
        <div class="flex flex-col gap-8 overflow-y-auto flex-1 min-h-0">
            <!-- Logo and Brand -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3 px-2 py-1 group">
                <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA Logo" class="h-11 w-auto object-contain transition-transform group-hover:scale-110 drop-shadow-[0_0_8px_rgba(164,216,255,0.25)] shrink-0">
                <img src="{{ asset('images/text-logo.png') }}" alt="Auditor-IA" class="h-7.5 w-auto object-contain group-hover:brightness-110 transition-all">
            </a>

            <!-- Main Navigation Links -->
            <nav class="flex flex-col gap-2" aria-label="Menú principal demo">
                <a href="{{ route('demo.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono transition-all {{ $activeSection === 'dashboard' ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard Pro
                </a>

                @if($firstDoc)
                    <a href="{{ route('demo.analysis.show', $firstDoc['slug']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono transition-all {{ $activeSection === 'analysis' ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Auditoría de Prueba
                    </a>
                @endif

                <a href="{{ route('landing') }}#planes"
                   class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent transition-all">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.773-.564-.373-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
                    </svg>
                    Planes
                </a>

                <a href="{{ route('landing') }}"
                   class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent transition-all">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Landing Page
                </a>
            </nav>

            <!-- Recents List Section -->
            <div id="sidebar-recents" class="flex flex-col gap-3">
                <span class="text-[11px] font-mono font-bold text-icy/60 tracking-widest px-3 uppercase border-b border-gunmetal pb-1">Documentos de Prueba</span>
                <div class="flex flex-col gap-1 px-1">
                    @foreach($recentDocs as $rDoc)
                        <a href="{{ route('demo.analysis.show', $rDoc['slug']) }}"
                           class="flex items-center gap-2 px-2.5 py-1.5 text-[12.5px] font-mono text-icy/80 hover:text-icy hover:bg-navy-light border border-transparent transition-colors truncate"
                           title="{{ $rDoc['original_filename'] }}">
                            <svg class="w-3.5 h-3.5 text-icy/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="truncate">{{ $rDoc['original_filename'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Demo User Profile Card -->
        <div class="mt-8 pt-4 border-t-2 border-gunmetal flex items-center justify-between px-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 bg-navy-dark border border-icy/40 flex items-center justify-center text-icy text-[13px] font-mono font-bold shadow-brutal-sm shrink-0">
                    {{ $demoUser['initials'] }}
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-[13px] font-bold text-icy truncate">{{ $demoUser['name'] }}</span>
                    <span class="text-[11px] font-mono uppercase tracking-wider text-icy font-bold">Plan {{ $demoUser['plan_name'] }} (Demo)</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col md:h-screen overflow-y-auto bg-gunmetal-dark border-t md:border-t-0 md:border-l border-gunmetal">
        <!-- Static Demo Environment Banner -->
        <div class="bg-[#0F1E36] border-b-2 border-[#35393C] px-6 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11.5px] font-mono text-icy">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-[#F5C87A] shrink-0"></span>
                <span>
                    <strong class="uppercase tracking-wider">Modo Demostración Estático (Vercel):</strong>
                    Solo lectura · Datos de prueba locales · Sin conexión a APIs IA, PostgreSQL ni almacenamiento externo.
                </span>
            </div>
            <a href="{{ route('landing') }}" class="text-icy/80 hover:text-icy underline underline-offset-2 shrink-0 self-start sm:self-auto">
                Volver al inicio
            </a>
        </div>

        @yield('content')
    </main>

    @stack('scripts')

</body>
</html>

