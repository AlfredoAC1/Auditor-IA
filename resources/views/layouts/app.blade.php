<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Auditor-IA - Analiza contratos con inteligencia artificial antes de firmar. Detecta cláusulas de riesgo, normativas aplicables y obtén resúmenes simplificados.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auditor-IA - Análisis de Contratos con IA')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Tailwind / Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Premium styles for dark mode and scrollbar -->
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #0d0d0d;
        }
        /* Custom scrollbar for premium dark aesthetic */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0d0d0d;
        }
        ::-webkit-scrollbar-thumb {
            background: #262626;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3f3f46;
        }
    </style>
</head>
<body class="bg-[#0d0d0d] text-gray-200 antialiased min-h-screen md:h-screen flex flex-col md:flex-row overflow-x-hidden md:overflow-y-hidden">

    <!-- Sidebar component -->
    <x-sidebar />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col md:h-screen overflow-y-auto bg-[#0d0d0d] border-t md:border-t-0 md:border-l border-zinc-800/40">
        @yield('content')
    </main>

    <!-- Interactive onboarding guide -->
    <x-tutorial-guide />

    <!-- Stacked scripts from child views -->
    @stack('scripts')

</body>
</html>
