<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Auditor-IA - Analiza contratos con inteligencia artificial antes de firmar. Detecta cláusulas de riesgo, normativas aplicables y obtén resúmenes simplificados.">
    <title>@yield('title', 'Auditor-IA - Análisis de Contratos con IA')</title>

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
<body class="bg-[#15181B] text-[#E2F1FF] antialiased min-h-screen md:h-screen flex flex-col md:flex-row overflow-x-hidden md:overflow-y-hidden selection:bg-[#0F1E36] selection:text-[#A4D8FF]">

    <!-- Sidebar component -->
    <x-sidebar />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col md:h-screen overflow-y-auto bg-[#15181B] border-t md:border-t-0 md:border-l border-[#35393C]">
        @yield('content')
    </main>

    <!-- Interactive onboarding guide -->
    <x-tutorial-guide />

</body>
</html>


