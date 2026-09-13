<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inicia sesión en Auditor-IA para comenzar a analizar tus contratos legales.">
    <title>Iniciar Sesión - Auditor-IA</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400..700;1,400..700&family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tailwind / Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #15181B;
        }
    </style>
</head>
<body class="bg-gunmetal-dark text-icy antialiased min-h-screen flex items-center justify-center p-4">

    <!-- Login Card Container -->
    <div class="w-full max-w-115 bg-gunmetal-card border-2 border-gunmetal p-8 md:p-10 shadow-brutal flex flex-col gap-8">
        
        <!-- Header & Logo -->
        <div class="flex flex-col gap-6">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA Logo" class="h-12 w-auto object-contain transition-transform group-hover:scale-105 drop-shadow-[0_0_8px_rgba(164,216,255,0.25)]">
                <img src="{{ asset('images/text-logo.png') }}" alt="Auditor-IA" class="h-8 w-auto object-contain group-hover:brightness-110 transition-all">
            </a>
            
            <div class="flex flex-col gap-2">
                <h1 id="login-title" class="text-[32px] font-serif font-bold text-icy tracking-tight leading-none">Bienvenido</h1>
                <p class="text-[14px] font-mono text-icy/70">Analiza contratos con IA antes de firmar.</p>
            </div>
        </div>

        <!-- Action Area -->
        <div class="flex flex-col gap-6">
            <!-- Google Sign-In Button -->
            <button class="w-full py-3 px-4 bg-gunmetal-dark border-2 border-gunmetal hover:bg-navy-light text-[13px] font-mono font-bold text-icy flex items-center justify-center gap-3 shadow-brutal-sm transition-all cursor-pointer uppercase tracking-wider">
                <!-- SVG Google Icon -->
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                Continuar con Google
            </button>

            <!-- Divider -->
            <div class="relative flex items-center justify-center">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gunmetal"></div>
                </div>
                <div class="relative bg-gunmetal-card px-3 text-[12px] font-mono font-bold text-icy/60 uppercase tracking-widest">o</div>
            </div>

            <!-- Form -->
            <form action="{{ route('login.store') }}" method="POST" class="flex flex-col gap-5">
                @csrf
                @if(session('error') || $errors->any())
                    <div class="bg-navy-dark border-2 border-gunmetal-dark text-icy px-4 py-3 text-[13px] font-mono">
                        {{ session('error') ?? $errors->first() }}
                    </div>
                @endif

                <div class="bg-gunmetal-dark border-2 border-gunmetal p-4 text-[12px] font-mono text-icy/70 leading-relaxed shadow-brutal-sm">
                    <p class="font-bold text-icy uppercase mb-1">Cuentas de prueba</p>
                    <p>Free: <span class="text-icy font-bold">free@auditor-ia.test</span></p>
                    <p>Pro: <span class="text-icy font-bold">pro@auditor-ia.test</span></p>
                    <p class="mt-1">Contraseña: <span class="text-icy font-bold">password</span></p>
                </div>
                <!-- Email Field -->
                <div class="flex flex-col gap-2">
                    <label for="email" class="text-[13px] font-mono font-bold text-icy">Correo electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="pro@auditor-ia.test" value="{{ old('email') }}"
                           class="w-full py-3 px-4 bg-gunmetal-dark border-2 border-gunmetal focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all" />
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-2">
                    <label for="password" class="text-[13px] font-mono font-bold text-icy">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" 
                           class="w-full py-3 px-4 bg-gunmetal-dark border-2 border-gunmetal focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all" />
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full mt-2 py-3 px-4 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[14px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all cursor-pointer uppercase tracking-wider text-center">
                    Iniciar sesión
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex flex-col gap-2 text-center text-[13px] font-mono text-icy/70">
            <div>
                ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-icy font-bold hover:underline transition-colors">Regístrate gratis</a>
            </div>
            <div class="text-[12px] text-icy/50">
                Al ingresar o registrarte, aceptas nuestros <a href="{{ route('terms') }}" class="text-icy/80 hover:text-icy underline">Términos y Condiciones</a>.
            </div>
        </div>

    </div>

    <x-terms-popup-modal />

</body>
</html>
