<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Crea tu cuenta en Auditor-IA para comenzar a auditar y analizar tus contratos legales con inteligencia artificial.">
    <title>Crear Cuenta - Auditor-IA</title>
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
<body class="bg-gunmetal-dark text-icy antialiased min-h-screen flex items-center justify-center p-4 py-10">

    <!-- Register Card Container -->
    <div class="w-full max-w-120 bg-gunmetal-card border-2 border-gunmetal p-8 md:p-10 shadow-brutal flex flex-col gap-6 my-auto">
        
        <!-- Header & Logo -->
        <div class="flex flex-col gap-5">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA Logo" class="h-12 w-auto object-contain transition-transform group-hover:scale-105 drop-shadow-[0_0_8px_rgba(164,216,255,0.25)]">
                <img src="{{ asset('images/text-logo.png') }}" alt="Auditor-IA" class="h-8 w-auto object-contain group-hover:brightness-110 transition-all">
            </a>
            
            <div class="flex flex-col gap-1.5">
                <h1 id="register-title" class="text-[30px] font-serif font-bold text-icy tracking-tight leading-tight">Crea tu cuenta</h1>
                <p class="text-[13px] font-mono text-icy/70">Comienza gratis con análisis impulsados por IA.</p>
            </div>
        </div>

        <!-- Action Area / Form -->
        <div class="flex flex-col gap-5">
            @if ($errors->any())
                <div class="bg-navy-dark border-2 border-red-500/60 text-icy p-4 text-[13px] font-mono shadow-brutal-sm">
                    <p class="font-bold text-red-400 uppercase tracking-wider mb-1">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside space-y-1 text-icy/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.store') }}" method="POST" class="flex flex-col gap-4.5">
                @csrf

                <!-- Name Field -->
                <div class="flex flex-col gap-1.5">
                    <label for="name" class="text-[13px] font-mono font-bold text-icy flex items-center justify-between">
                        <span>Nombre completo <span class="text-red-400">*</span></span>
                    </label>
                    <input type="text" id="name" name="name" required placeholder="Ej. Constanza Silva" value="{{ old('name') }}"
                           @class([
                               'w-full py-2.5 px-4 bg-gunmetal-dark border-2 focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all',
                               'border-red-400' => $errors->has('name'),
                               'border-gunmetal' => !$errors->has('name'),
                           ]) />
                    @error('name')
                        <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-[13px] font-mono font-bold text-icy flex items-center justify-between">
                        <span>Correo electrónico <span class="text-red-400">*</span></span>
                    </label>
                    <input type="email" id="email" name="email" required placeholder="tu-correo@empresa.com" value="{{ old('email') }}"
                           @class([
                               'w-full py-2.5 px-4 bg-gunmetal-dark border-2 focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all',
                               'border-red-400' => $errors->has('email'),
                               'border-gunmetal' => !$errors->has('email'),
                           ]) />
                    @error('email')
                        <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Organization Name Field (Optional) -->
                <div class="flex flex-col gap-1.5">
                    <label for="organization_name" class="text-[13px] font-mono font-bold text-icy flex items-center justify-between">
                        <span>Organización o Estudio <span class="text-[11px] font-normal text-icy/50">(Opcional)</span></span>
                    </label>
                    <input type="text" id="organization_name" name="organization_name" placeholder="Ej. Silva & Asociados" value="{{ old('organization_name') }}"
                           @class([
                               'w-full py-2.5 px-4 bg-gunmetal-dark border-2 focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all',
                               'border-red-400' => $errors->has('organization_name'),
                               'border-gunmetal' => !$errors->has('organization_name'),
                           ]) />
                    @error('organization_name')
                        <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-[13px] font-mono font-bold text-icy flex items-center justify-between">
                        <span>Contraseña <span class="text-red-400">*</span></span>
                        <span class="text-[11px] font-normal text-icy/50">Mínimo 8 caracteres</span>
                    </label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" 
                           @class([
                               'w-full py-2.5 px-4 bg-gunmetal-dark border-2 focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all',
                               'border-red-400' => $errors->has('password'),
                               'border-gunmetal' => !$errors->has('password'),
                           ]) />
                    @error('password')
                        <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Confirmation Field -->
                <div class="flex flex-col gap-1.5">
                    <label for="password_confirmation" class="text-[13px] font-mono font-bold text-icy">
                        Confirmar contraseña <span class="text-red-400">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" 
                           class="w-full py-2.5 px-4 bg-gunmetal-dark border-2 border-gunmetal focus:border-icy text-[14px] font-mono text-icy placeholder-icy/40 outline-none transition-all" />
                </div>

                <!-- Terms & Conditions Agreement -->
                <div class="flex items-start gap-3 mt-1">
                    <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required
                           class="mt-1 h-4 w-4 rounded-none bg-gunmetal-dark border-2 border-gunmetal text-navy-dark focus:ring-icy focus:ring-offset-gunmetal-dark cursor-pointer" />
                    <label for="terms" class="text-[12px] font-mono text-icy/80 leading-relaxed cursor-pointer select-none">
                        Acepto los <a href="{{ route('terms') }}" class="text-icy underline hover:text-white font-bold" target="_blank">Términos y Condiciones</a> del servicio de Auditor-IA.
                    </label>
                </div>
                @error('terms')
                    <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
                @enderror

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full mt-2 py-3 px-4 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[14px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all cursor-pointer uppercase tracking-wider text-center">
                    Crear cuenta gratis
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex flex-col gap-2 text-center text-[13px] font-mono text-icy/70 border-t border-gunmetal pt-4">
            <div>
                ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-icy font-bold hover:underline transition-colors">Inicia sesión</a>
            </div>
        </div>

    </div>

    <x-terms-popup-modal />

</body>
</html>
