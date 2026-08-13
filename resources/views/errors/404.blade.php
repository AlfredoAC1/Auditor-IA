@extends('layouts.guest')

@section('title', 'Página no encontrada — 404 | Auditor-IA')
@section('meta_description', 'La página o recurso al que intentas acceder en Auditor-IA no existe o ha sido movido.')

@section('content')
<div class="relative overflow-hidden min-h-[calc(100vh-160px)] flex items-center justify-center py-16 px-6">
    
    {{-- Decorative Background Glows & Floating Grid --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[500px] bg-violet-600/10 rounded-full blur-[140px]"></div>
        <div class="absolute top-1/3 right-1/4 w-[400px] h-[400px] bg-fuchsia-600/5 rounded-full blur-[110px]"></div>
    </div>

    <div class="relative max-w-2xl w-full text-center flex flex-col items-center gap-8">
        
        {{-- Legal / Tech Audit Badge --}}
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-violet-500/10 border border-violet-500/20 text-[12px] font-semibold text-violet-300">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            Error 404 — Cláusula No Encontrada
        </div>

        {{-- Main Visual 404 Graphic --}}
        <div class="relative flex items-center justify-center">
            <div class="text-[120px] sm:text-[160px] font-extrabold tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-violet-500 via-fuchsia-400 to-zinc-700 leading-none select-none drop-shadow-2xl">
                404
            </div>
            
            {{-- Floating Document Icon Badge --}}
            <div class="absolute -bottom-2 sm:-bottom-4 p-4 rounded-2xl bg-[#161616]/90 border border-zinc-800 backdrop-blur-xl shadow-2xl flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-xs font-bold text-white">Documento Inválido</p>
                    <p class="text-[11px] text-zinc-400">La ruta especificada no existe en el índice</p>
                </div>
            </div>
        </div>

        {{-- Text Content --}}
        <div class="space-y-3 max-w-lg">
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
                Parece que esta página se ha traspapelado
            </h1>
            <p class="text-zinc-400 text-sm leading-relaxed">
                La ruta o documento al que intentas acceder no existe, ha sido trasladado o no dispones de los permisos necesarios dentro del tenant actual.
            </p>
        </div>

        {{-- Quick Links / Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
            <a href="{{ route('landing') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-xs sm:text-sm font-bold text-white shadow-lg shadow-violet-500/20 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Volver al Inicio
            </a>
            
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-xs sm:text-sm font-bold text-white transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Ir al Panel de Control
            </a>

            <a href="{{ route('terms') }}" class="w-full sm:w-auto px-5 py-3.5 rounded-xl text-xs sm:text-sm font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Términos y Condiciones
            </a>
        </div>

        {{-- Assistance Help Box --}}
        <div class="mt-4 p-4 rounded-2xl bg-[#161616]/60 border border-zinc-800/60 max-w-md text-xs text-zinc-400 flex items-center gap-3">
            <svg class="w-5 h-5 text-violet-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Si crees que esto es un error de la plataforma Auditor-IA, puedes volver al panel principal para revisar tus análisis activos.</span>
        </div>

    </div>
</div>
@endsection
