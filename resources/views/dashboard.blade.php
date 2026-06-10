@extends('layouts.app')

@section('title', 'Dashboard - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-6xl mx-auto w-full">

    <!-- Header Section -->
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Dashboard</h1>
            <p class="text-[13px] text-zinc-400">{{ $analyzedCount }} {{ $analyzedCount === 1 ? 'análisis disponible' : 'análisis disponibles' }} en tu organización</p>
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
            <button onclick="document.getElementById('file-input').click()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[13px] font-bold text-white shadow-lg transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Subir documento
            </button>
        </div>
    </header>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3.5 rounded-xl text-[13px] font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3.5 rounded-xl text-[13px] font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Metric 1 -->
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3 hover:border-zinc-750 transition-colors duration-255 group">
            <span class="text-4xl font-extrabold text-white tracking-tight group-hover:scale-[1.02] origin-left transition-transform duration-200">{{ $analyzedCount }}</span>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-zinc-400">Documentos analizados</span>
                <span class="text-[11px] text-zinc-500 font-semibold flex items-center gap-1 mt-0.5">
                    {{ $documents->count() }} subidos en total
                </span>
            </div>
        </div>

        <!-- Metric 2 -->
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3 hover:border-zinc-750 transition-colors duration-255 group">
            <span class="text-4xl font-extrabold text-white tracking-tight group-hover:scale-[1.02] origin-left transition-transform duration-200">{{ $riskCount }}</span>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-zinc-400">Cláusulas de riesgo</span>
                <span class="text-[11px] text-zinc-500 font-semibold flex items-center gap-1.5 mt-0.5">
                    {{ $riskCount === 0 ? 'Ninguna detectada' : 'Requieren atención inmediata' }}
                </span>
            </div>
        </div>

        <!-- Metric 3 -->
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3 hover:border-zinc-750 transition-colors duration-255 group">
            <span class="text-4xl font-extrabold text-white tracking-tight group-hover:scale-[1.02] origin-left transition-transform duration-200">{{ $avgTime }}{{ $avgTime !== '--' ? ' s' : '' }}</span>
            <div class="flex flex-col gap-1">
                <span class="text-[13px] font-medium text-zinc-400">Tiempo prom. de análisis</span>
                <span class="text-[11px] text-zinc-500 font-semibold flex items-center gap-1 mt-0.5">
                    {{ $avgTime !== '--' ? 'Basado en análisis reales' : 'Sin datos suficientes' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Drag & Drop Upload Zone -->
    <form action="{{ route('dashboard.upload') }}" method="POST" enctype="multipart/form-data" class="bg-[#161616]/40 border-2 border-dashed border-zinc-850 hover:border-zinc-750 rounded-2xl p-8 md:p-12 flex flex-col items-center justify-center gap-6 transition-colors duration-300">
        @csrf
        <!-- Hidden File Input -->
        <input type="file" name="file" id="file-input" class="hidden" accept=".pdf,.docx" onchange="this.form.submit()">

        <!-- Cloud Upload Icon Container -->
        <div onclick="document.getElementById('file-input').click()" class="w-14 h-14 rounded-2xl bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-violet-400 shadow-inner cursor-pointer hover:bg-violet-600/20 transition-colors">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
        </div>

        <div class="flex flex-col gap-2 text-center">
            <h2 class="text-lg font-bold text-white tracking-tight">Arrastra tu documento aquí</h2>
            <p class="text-[13px] text-zinc-400 leading-relaxed">
                PDF o DOCX · hasta 50MB · en español <br>
                <span class="text-zinc-500">El análisis tarda entre 30 y 60 segundos</span>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Explore Files Button -->
            <button type="button" onclick="document.getElementById('file-input').click()" class="px-5 py-2.5 rounded-xl border border-zinc-700/60 hover:bg-zinc-800/30 text-[13px] font-semibold text-zinc-300 flex items-center gap-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                Explorar archivos
            </button>
            <!-- Upload & Analyze Button -->
            <button type="button" onclick="document.getElementById('file-input').click()" class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white flex items-center gap-2 shadow-lg shadow-violet-500/10 hover:shadow-violet-500/20 transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Subir y analizar
            </button>
        </div>
    </form>

    <!-- Recent Analyses Section -->
    <section class="flex flex-col gap-4">
        <h2 class="text-lg font-bold text-white tracking-tight">Análisis recientes</h2>
        
        @if($documents->isEmpty())
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
        @else
            <div class="overflow-x-auto bg-[#161616]/30 border border-zinc-800/40 rounded-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-850/60 text-[12px] font-bold text-zinc-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Documento</th>
                            <th class="px-6 py-4">Fecha</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-850/40 text-[13px] text-zinc-300">
                        @foreach($documents as $doc)
                            <tr class="hover:bg-zinc-900/20 transition-colors">
                                <td class="px-6 py-4.5 font-medium text-white max-w-xs truncate">
                                    {{ $doc->original_filename }}
                                </td>
                                <td class="px-6 py-4.5 text-zinc-500">
                                    {{ $doc->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4.5">
                                    @if($doc->status->value === 'processed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Listo
                                        </span>
                                    @elseif($doc->status->value === 'processing')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Analizando...
                                        </span>
                                    @elseif($doc->status->value === 'failed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            Falló
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-zinc-500/10 text-zinc-400 border border-zinc-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4.5 text-right">
                                    @if($doc->status->value === 'processed')
                                        <a href="{{ route('analysis.show', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-[12px] font-bold text-white transition-colors">
                                            Ver reporte
                                        </a>
                                    @else
                                        <span class="text-zinc-600 font-semibold text-[12px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

</div>
@endsection
