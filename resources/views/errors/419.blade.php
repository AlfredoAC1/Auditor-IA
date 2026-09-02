@extends('layouts.guest')

@section('title', 'Sesión Expirada — 419 | Auditor-IA')
@section('meta_description', 'La sesión o el token de seguridad CSRF ha expirado en Auditor-IA. Por favor, recarga la página e intenta nuevamente.')

@section('content')
<div class="relative overflow-hidden min-h-[calc(100vh-160px)] flex items-center justify-center py-16 px-6 bg-[#15181B]">

    <div class="relative max-w-2xl w-full text-center flex flex-col items-center gap-8">

        {{-- Legal / Tech Audit Badge --}}
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-[#0F1E36] border border-[#A4D8FF]/40 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm uppercase tracking-wider">
            <span class="w-2 h-2 bg-[#A4D8FF]"></span>
            Error 419 — Sesión Expirada / Token CSRF Inválido
        </div>

        {{-- Main Visual 419 Graphic --}}
        <div class="flex flex-col items-center justify-center gap-4">
            <div class="text-[110px] sm:text-[140px] font-serif font-extrabold tracking-tighter text-[#A4D8FF] leading-none select-none">
                419
            </div>

            {{-- Clock / Lock Badge --}}
            <div class="p-4 bg-[#15181B] border-2 border-[#35393C] shadow-brutal flex items-center gap-3">
                <div class="w-10 h-10 bg-[#0F1E36] border border-[#A4D8FF]/40 flex items-center justify-center text-[#A4D8FF] shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-left font-mono">
                    <p class="text-xs font-bold text-[#A4D8FF]">Tiempo de Sesión Agotado</p>
                    <p class="text-[11px] text-[#A4D8FF]/70">El token de autenticación del formulario ha caducado</p>
                </div>
            </div>
        </div>

        {{-- Text Content --}}
        <div class="space-y-3 max-w-lg">
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight">
                La sesión ha caducado por inactividad
            </h1>
            <p class="font-mono text-[#A4D8FF]/80 text-sm leading-relaxed">
                Para proteger la integridad de tus documentos y la seguridad de la sesión, los tokens de validación expiran tras un periodo de inactividad. Por favor, vuelve a cargar la página o inicia sesión para continuar.
            </p>
        </div>

        {{-- Quick Links / Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
            <button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-xs sm:text-sm font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all flex items-center justify-center gap-2 uppercase tracking-wider cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Recargar Página
            </button>

            <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3.5 bg-[#15181B] border-2 border-[#35393C] hover:bg-[#162A45] text-xs sm:text-sm font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all flex items-center justify-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Iniciar Sesión
            </a>

            <a href="{{ route('landing') }}" class="w-full sm:w-auto px-5 py-3.5 font-mono text-xs sm:text-sm font-bold text-[#A4D8FF]/80 hover:text-[#A4D8FF] hover:underline transition-all flex items-center justify-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Ir al Inicio
            </a>
        </div>

        {{-- Assistance Help Box --}}
        <div class="mt-4 p-4 bg-[#222629] border-2 border-[#35393C] max-w-md text-xs font-mono text-[#A4D8FF]/80 flex items-center gap-3 shadow-brutal-sm">
            <svg class="w-5 h-5 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Si vuelves a ver esta pantalla de error, intenta limpiar la caché de tu navegador o vuelve a iniciar sesión.</span>
        </div>

    </div>
</div>
@endsection
