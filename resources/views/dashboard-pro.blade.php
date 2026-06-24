@extends('layouts.app')

@section('title', 'Dashboard Pro - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    <!-- Pro Header -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Dashboard Pro</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide bg-gradient-to-r from-violet-600/20 to-fuchsia-600/20 text-violet-200 border border-violet-500/30">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Pro
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Procesamiento integrado activo
                </span>
            </div>
            <p class="text-[13px] text-zinc-400">{{ $analyzedCount }} análisis completados · acceso a todos los tipos de informe</p>
        </div>
        <button onclick="document.getElementById('file-input').click()" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-[13px] font-bold text-white shadow-lg shadow-violet-500/20 transition-all cursor-pointer shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Subir documento
        </button>
    </header>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3.5 rounded-xl text-[13px] font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3.5 rounded-xl text-[13px] font-medium">{{ session('error') }}</div>
    @endif

    <x-plan-usage-card
        :plan-name="$planConfig['name'] ?? 'Pro'"
        :plan-slug="$planSlug"
        :documents-used="$documentsThisMonth"
        :documents-limit="$planConfig['max_documents_per_month'] ?? null"
        :analyses-used="$analysesThisMonth"
        :analyses-limit="$planConfig['max_analyses_per_month'] ?? null"
        :storage-used-bytes="$storageUsedBytes"
        :storage-limit-bytes="$planConfig['max_storage_bytes'] ?? null"
    />

    <!-- Pro metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-[#161616] border border-violet-500/20 rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-[12px] font-semibold text-violet-300 uppercase tracking-wide">Documentos analizados</span>
            <span class="text-4xl font-extrabold text-white">{{ $analyzedCount }}</span>
            <span class="text-[11px] text-zinc-500">{{ $documents->count() }} subidos en total</span>
        </div>
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-[12px] font-semibold text-zinc-400 uppercase tracking-wide">Riesgos detectados</span>
            <span class="text-4xl font-extrabold text-white">{{ $riskCount }}</span>
            <span class="text-[11px] text-zinc-500">En todos tus documentos</span>
        </div>
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-[12px] font-semibold text-zinc-400 uppercase tracking-wide">Tiempo prom. análisis</span>
            <span class="text-4xl font-extrabold text-white">{{ $avgTime }}{{ $avgTime !== '--' ? 's' : '' }}</span>
            <span class="text-[11px] text-zinc-500">Procesamiento prioritario</span>
        </div>
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-2">
            <span class="text-[12px] font-semibold text-zinc-400 uppercase tracking-wide">Tokens este mes</span>
            <span class="text-4xl font-extrabold text-white">{{ number_format($tokensThisMonth, 0, ',', '.') }}</span>
            <span class="text-[11px] text-zinc-500">Límite: {{ isset($planConfig['max_tokens_per_month']) && $planConfig['max_tokens_per_month'] ? number_format($planConfig['max_tokens_per_month'], 0, ',', '.') : 'Ilimitado' }}</span>
        </div>
    </div>

    <!-- Analysis types panel (Pro exclusive) -->
    <section class="bg-[#161616] border border-violet-500/20 rounded-2xl p-6 flex flex-col gap-5">
        <div class="flex flex-col gap-1">
            <h2 class="text-lg font-bold text-white">Tipos de análisis disponibles</h2>
            <p class="text-[13px] text-zinc-500">Como usuario Pro tienes acceso a todos los informes especializados.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach(\App\Enums\AnalysisType::cases() as $type)
                <div class="flex items-start gap-3 p-4 rounded-xl bg-zinc-900/40 border border-zinc-800/60">
                    <div class="w-8 h-8 rounded-lg bg-violet-600/10 border border-violet-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-[13px] font-bold text-white">{{ $type->label() }}</span>
                        <span class="text-[11px] text-zinc-500 leading-relaxed">{{ $type->description() }}</span>
                        <span class="text-[11px] text-violet-300 font-semibold mt-1">{{ $analysisTypeCounts[$type->value] ?? 0 }} este mes</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <x-dashboard-upload-zone :is-pro="true" />

    <!-- Documents table -->
    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-white tracking-tight">Análisis recientes</h2>
            @if(count($pendingDocumentIds) > 0)
                <span class="text-[12px] text-amber-400 font-medium animate-pulse">{{ count($pendingDocumentIds) }} en procesamiento...</span>
            @endif
        </div>

        @if($documents->isEmpty())
            <div class="flex flex-col items-center justify-center p-10 rounded-2xl bg-[#161616]/40 border border-zinc-800/40 text-center gap-4">
                <h3 class="text-[14px] font-bold text-zinc-200">No hay análisis disponibles</h3>
                <p class="text-[12px] text-zinc-500">Sube tu primer contrato para comenzar.</p>
            </div>
        @else
            <x-documents-table :documents="$documents" />
        @endif
    </section>

    <x-document-status-poller :pending-document-ids="$pendingDocumentIds" />

</div>
@endsection
