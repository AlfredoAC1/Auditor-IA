@extends('layouts.app')

@section('title', 'Dashboard - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-6xl mx-auto w-full">

    <!-- Header Section -->
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Dashboard</h1>
            <p class="text-[13px] text-zinc-400">1 análisis disponible este mes</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Filter Button -->
            <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800/80 border border-zinc-800/80 text-[13px] font-semibold text-zinc-350 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                </svg>
                Filtrar
            </button>
            <!-- Upload Document Button -->
            <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[13px] font-bold text-white shadow-lg transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Subir documento
            </button>
        </div>
    </header>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Metric 1 -->
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3 hover:border-zinc-750 transition-colors duration-255 group">
            <span class="text-4xl font-extrabold text-white tracking-tight group-hover:scale-[1.02] origin-left transition-transform duration-200">0</span>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-zinc-400">Documentos analizados</span>
                <span class="text-[11px] text-zinc-500 font-semibold flex items-center gap-1 mt-0.5">
                    Sin actividad este mes
                </span>
            </div>
        </div>

        <!-- Metric 2 -->
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3 hover:border-zinc-750 transition-colors duration-255 group">
            <span class="text-4xl font-extrabold text-white tracking-tight group-hover:scale-[1.02] origin-left transition-transform duration-200">0</span>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-zinc-400">Cláusulas de riesgo</span>
                <span class="text-[11px] text-zinc-500 font-semibold flex items-center gap-1.5 mt-0.5">
                    Ninguna detectada
                </span>
            </div>
        </div>

        <!-- Metric 3 -->
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3 hover:border-zinc-750 transition-colors duration-255 group">
            <span class="text-4xl font-extrabold text-white tracking-tight group-hover:scale-[1.02] origin-left transition-transform duration-200">-- s</span>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-zinc-400">Tiempo prom. de análisis</span>
                <span class="text-[11px] text-zinc-500 font-semibold flex items-center gap-1 mt-0.5">
                    Sin datos suficientes
                </span>
            </div>
        </div>
    </div>

    <!-- Drag & Drop Upload Zone -->
    <div class="bg-[#161616]/40 border-2 border-dashed border-zinc-850 hover:border-zinc-750 rounded-2xl p-8 md:p-12 flex flex-col items-center justify-center gap-6 transition-colors duration-300">
        <!-- Cloud Upload Icon Container -->
        <div class="w-14 h-14 rounded-2xl bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-violet-400 shadow-inner">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
        </div>

        <div class="flex flex-col gap-2 text-center">
            <h2 class="text-lg font-bold text-white tracking-tight">Arrastra tu documento aquí</h2>
            <p class="text-[13px] text-zinc-400 leading-relaxed">
                PDF o DOCX · hasta 20 páginas · en español <br>
                <span class="text-zinc-500">El análisis tarda entre 30 y 60 segundos</span>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Explore Files Button -->
            <button class="px-5 py-2.5 rounded-xl border border-zinc-700/60 hover:bg-zinc-800/30 text-[13px] font-semibold text-zinc-300 flex items-center gap-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                Explorar archivos
            </button>
            <!-- Upload & Analyze Button -->
            <button class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white flex items-center gap-2 shadow-lg shadow-violet-500/10 hover:shadow-violet-500/20 transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Subir y analizar
            </button>
        </div>
    </div>

    <!-- Recent Analyses Section -->
    <section class="flex flex-col gap-4">
        <h2 class="text-lg font-bold text-white tracking-tight">Análisis recientes</h2>
        
        <div class="flex flex-col items-center justify-center p-10 rounded-2xl bg-[#161616]/40 border border-zinc-800/40 text-center gap-4 transition-all duration-300 hover:border-zinc-750">
            <!-- Sleek Empty State Icon -->
            <div class="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-550 shadow-inner">
                <svg class="w-6.5 h-6.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4M4 18h16" />
                </svg>
            </div>
            <div class="flex flex-col gap-1.5 max-w-sm">
                <h3 class="text-[14px] font-bold text-zinc-200">No hay análisis disponibles</h3>
                <p class="text-[12px] text-zinc-500 leading-relaxed">
                    Sube tu primer archivo en la zona de arriba (`PDF` o `DOCX`) para comenzar el análisis legal con inteligencia artificial.
                </p>
            </div>
        </div>
    </section>

</div>
@endsection
