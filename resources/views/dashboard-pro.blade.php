@extends('layouts.app')

@section('title', 'Dashboard Pro - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    <!-- Pro Header -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b-2 border-[#35393C]">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight">Dashboard Pro</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-mono font-bold uppercase tracking-wide bg-[#0F1E36] text-[#A4D8FF] border border-[#A4D8FF]/40 shadow-brutal-sm">
                    <svg class="w-3.5 h-3.5 text-[#A4D8FF]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Pro
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-mono font-bold bg-[#1B4332] text-[#A4D8FF] border border-[#15181B] shadow-brutal-sm uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#A4D8FF] animate-pulse"></span>
                    Procesamiento integrado activo
                </span>
            </div>
            <p class="text-[13px] font-mono text-[#A4D8FF]/70">{{ $analyzedCount }} análisis completados · acceso a todos los tipos de informe</p>
        </div>
        <button onclick="document.getElementById('file-input').click()" class="flex items-center gap-2 px-5 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer shrink-0 uppercase tracking-wider">
            <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Subir documento
        </button>
    </header>

    @if(session('success'))
        <div class="bg-[#1B4332] border-2 border-[#15181B] text-[#A4D8FF] px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-[#0F1E36] border-2 border-[#15181B] text-[#A4D8FF] px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium">{{ session('error') }}</div>
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
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-[#A4D8FF] uppercase tracking-wide">Documentos analizados</span>
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF]">{{ $analyzedCount }}</span>
            <span class="text-[11px] font-mono text-[#A4D8FF]/60">{{ $documents->count() }} subidos en total</span>
        </div>
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wide">Riesgos detectados</span>
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF]">{{ $riskCount }}</span>
            <span class="text-[11px] font-mono text-[#A4D8FF]/60">En todos tus documentos</span>
        </div>
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wide">Tiempo prom. análisis</span>
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF]">{{ $avgTime }}{{ $avgTime !== '--' ? 's' : '' }}</span>
            <span class="text-[11px] font-mono text-[#A4D8FF]/60">Procesamiento prioritario</span>
        </div>
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wide">Tokens este mes</span>
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF]">{{ number_format($tokensThisMonth, 0, ',', '.') }}</span>
            <span class="text-[11px] font-mono text-[#A4D8FF]/60">Límite: {{ isset($planConfig['max_tokens_per_month']) && $planConfig['max_tokens_per_month'] ? number_format($planConfig['max_tokens_per_month'], 0, ',', '.') : 'Ilimitado' }}</span>
        </div>
    </div>

    <!-- Analysis types panel (Pro exclusive) -->
    <section id="analysis-types-panel" class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-serif font-bold text-[#A4D8FF]">Tipos de análisis disponibles</h2>
            <p class="text-[13px] font-mono text-[#A4D8FF]/70">Como usuario Pro tienes acceso a todos los informes especializados.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach(\App\Enums\AnalysisType::cases() as $type)
                <div class="flex items-start gap-3 p-4 bg-[#15181B] border-2 border-[#35393C] shadow-brutal-sm">
                    <div class="w-8 h-8 bg-[#0F1E36] border border-[#A4D8FF]/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-[13.5px] font-bold text-[#A4D8FF]">{{ $type->label() }}</span>
                        <span class="text-[11px] text-[#A4D8FF]/70 leading-relaxed">{{ $type->description() }}</span>
                        <span class="text-[11px] font-mono font-bold text-[#A4D8FF] mt-1">{{ $analysisTypeCounts[$type->value] ?? 0 }} este mes</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <x-provider-selector :current-provider="$currentProvider" :is-pro="true" />

    <x-dashboard-upload-zone :is-pro="true" :current-provider="$currentProvider" />

    <!-- Documents table -->
    <section id="recent-analyses-container" class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-serif font-bold text-[#A4D8FF] tracking-tight">Análisis recientes</h2>
            @if(count($pendingDocumentIds) > 0)
                <span class="text-[12px] font-mono text-[#A4D8FF] bg-[#0F1E36] px-2.5 py-1 border border-[#A4D8FF]/40 shadow-brutal-sm font-medium animate-pulse">{{ count($pendingDocumentIds) }} en procesamiento...</span>
            @endif
        </div>

        @if($documents->isEmpty())
            <div class="flex flex-col items-center justify-center p-10 bg-[#222629]/50 border-2 border-dashed border-[#35393C] text-center gap-4">
                <h3 class="text-[14px] font-serif font-bold text-[#A4D8FF]">No hay análisis disponibles</h3>
                <p class="text-[12px] font-mono text-[#A4D8FF]/60">Sube tu primer contrato para comenzar.</p>
            </div>
        @else
            <x-documents-table :documents="$documents" />
        @endif
    </section>

    <x-document-status-poller :pending-document-ids="$pendingDocumentIds" />

</div>
@endsection


