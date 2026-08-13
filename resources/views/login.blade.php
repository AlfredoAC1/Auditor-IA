<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inicia sesión en Auditor-IA para comenzar a analizar tus contratos legales.">
    <title>Iniciar Sesión - Auditor-IA</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Tailwind / Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #0b0b0b;
        }
    </style>
</head>
<body class="bg-[#0b0b0b] text-gray-200 antialiased min-h-screen flex items-center justify-center p-4">

    <!-- Login Card Container -->
    <div class="w-full max-w-[460px] bg-[#161616] border border-zinc-800/60 rounded-3xl p-8 md:p-10 shadow-2xl shadow-black/80 flex flex-col gap-8 transition-all duration-300 hover:border-zinc-700/60">
        
        <!-- Header & Logo -->
        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/10">
                    <!-- Sleek Document/AI SVG Icon -->
                    <svg class="w-5.5 h-5.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-white">Auditor-IA</span>
            </div>
            
            <div class="flex flex-col gap-2">
                <h1 id="login-title" class="text-[32px] font-bold text-white tracking-tight leading-none">Bienvenido</h1>
                <p class="text-[14px] text-zinc-400">Analiza contratos con IA antes de firmar.</p>
            </div>
        </div>

        <!-- Action Area -->
        <div class="flex flex-col gap-6">
            <!-- Google Sign-In Button -->
            <button class="w-full py-3.5 px-4 rounded-xl bg-transparent border border-zinc-700/60 hover:bg-zinc-800/30 text-[14px] font-medium text-zinc-300 flex items-center justify-center gap-3 transition-all duration-200 cursor-pointer">
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
                    <div class="w-full border-t border-zinc-800/80"></div>
                </div>
                <div class="relative bg-[#161616] px-3 text-[12px] font-medium text-zinc-500 uppercase tracking-widest">o</div>
            </div>

            <!-- Form -->
            <form action="{{ route('login.store') }}" method="POST" class="flex flex-col gap-5">
                @csrf
                @if(session('error'))
                    <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl text-[13px]">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="bg-zinc-900/50 border border-zinc-800/60 rounded-xl px-4 py-3 text-[12px] text-zinc-500 leading-relaxed">
                    <p class="font-semibold text-zinc-400 mb-1">Cuentas de prueba</p>
                    <p>Free: <span class="text-zinc-300">free@auditor-ia.test</span></p>
                    <p>Pro: <span class="text-violet-300">pro@auditor-ia.test</span></p>
                    <p class="mt-1">Contraseña: <span class="text-zinc-300">password</span></p>
                </div>
                <!-- Email Field -->
                <div class="flex flex-col gap-2">
                    <label for="email" class="text-[13px] font-semibold text-zinc-400">Correo electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="pro@auditor-ia.test" value="{{ old('email') }}"
                           class="w-full py-3.5 px-4 rounded-xl bg-zinc-900/40 border border-zinc-850 focus:border-violet-500/60 focus:bg-zinc-900/60 text-[14px] text-white placeholder-zinc-600 outline-none transition-all duration-200" />
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-2">
                    <label for="password" class="text-[13px] font-semibold text-zinc-400">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" 
                           class="w-full py-3.5 px-4 rounded-xl bg-zinc-900/40 border border-zinc-850 focus:border-violet-500/60 focus:bg-zinc-900/60 text-[14px] text-white placeholder-zinc-600 outline-none transition-all duration-200" />
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full mt-2 py-3.5 px-4 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[14px] font-bold text-white shadow-lg transition-all duration-200 cursor-pointer select-none text-center">
                    Iniciar sesión
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex flex-col gap-2 text-center text-[13px] text-zinc-500">
            <div>
                ¿No tienes cuenta? <a href="#" class="text-violet-400 hover:text-violet-300 font-medium hover:underline transition-colors">Regístrate gratis</a>
            </div>
            <div class="text-[12px] text-zinc-600">
                Al ingresar o registrarte, aceptas nuestros <a href="{{ route('terms') }}" class="text-zinc-400 hover:text-zinc-300 underline">Términos y Condiciones</a>.
            </div>
        </div>

    </div>

    <x-terms-popup-modal />

</body>
</html>

