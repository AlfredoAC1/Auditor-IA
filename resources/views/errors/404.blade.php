@extends('layouts.guest')

@section('title', 'Página no encontrada — 404 | Auditor-IA')
@section('meta_description', 'La página o recurso al que intentas acceder en Auditor-IA no existe o ha sido movido.')

@section('content')
<div class="relative overflow-hidden min-h-[calc(100vh-160px)] flex items-center justify-center py-16 px-6 bg-[#15181B]">

    <div class="relative max-w-2xl w-full text-center flex flex-col items-center gap-8">
        
        {{-- Legal / Tech Audit Badge --}}
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-[#0F1E36] border border-[#A4D8FF]/40 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm uppercase tracking-wider">
            <span class="w-2 h-2 bg-[#A4D8FF]"></span>
            Error 404 — Cláusula No Encontrada
        </div>

        {{-- Main Visual 404 Graphic --}}
        <div class="flex flex-col items-center justify-center gap-4">
            <div class="text-[110px] sm:text-[140px] font-serif font-extrabold tracking-tighter text-[#A4D8FF] leading-none select-none">
                404
            </div>

            {{-- Document Icon Badge --}}
            <div class="p-4 bg-[#15181B] border-2 border-[#35393C] shadow-brutal flex items-center gap-3">
                <div class="w-10 h-10 bg-[#0F1E36] border border-[#A4D8FF]/40 flex items-center justify-center text-[#A4D8FF] shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="text-left font-mono">
                    <p class="text-xs font-bold text-[#A4D8FF]">Documento Inválido</p>
                    <p class="text-[11px] text-[#A4D8FF]/70">La ruta especificada no existe en el índice</p>
                </div>
            </div>
        </div>

        {{-- Text Content --}}
        <div class="space-y-3 max-w-lg">
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight">
                Parece que esta página se ha traspapelado
            </h1>
            <p class="font-mono text-[#A4D8FF]/80 text-sm leading-relaxed">
                La ruta o documento al que intentas acceder no existe, ha sido trasladado o no dispones de los permisos necesarios dentro del tenant actual.
            </p>
        </div>

        {{-- Quick Links / Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full sm:w-auto">
            <a href="{{ route('landing') }}" class="w-full sm:w-auto px-6 py-3.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-xs sm:text-sm font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all flex items-center justify-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Volver al Inicio
            </a>
            
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3.5 bg-[#15181B] border-2 border-[#35393C] hover:bg-[#162A45] text-xs sm:text-sm font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all flex items-center justify-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Ir al Panel de Control
            </a>

            <a href="{{ route('terms') }}" class="w-full sm:w-auto px-5 py-3.5 font-mono text-xs sm:text-sm font-bold text-[#A4D8FF]/80 hover:text-[#A4D8FF] hover:underline transition-all flex items-center justify-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Términos y Condiciones
            </a>
        </div>

        {{-- Assistance Help Box --}}
        <div class="mt-4 p-4 bg-[#222629] border-2 border-[#35393C] max-w-md text-xs font-mono text-[#A4D8FF]/80 flex items-center gap-3 shadow-brutal-sm">
            <svg class="w-5 h-5 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Si crees que esto es un error de la plataforma Auditor-IA, puedes volver al panel principal para revisar tus análisis activos.</span>
        </div>

    </div>
</div>
@endsection


