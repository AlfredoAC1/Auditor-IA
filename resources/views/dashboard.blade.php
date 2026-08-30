@extends('layouts.app')

@section('title', 'Dashboard - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-6xl mx-auto w-full">

    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b-2 border-[#35393C]">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight">Dashboard</h1>
            <p class="text-[13px] font-mono text-[#A4D8FF]/70">Bienvenido, {{ auth()->user()->name }}</p>
        </div>
        <button onclick="document.getElementById('file-input').click()" class="flex items-center gap-2 px-4 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
            <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Subir documento
        </button>
    </header>

    @if(session('success'))
        <div class="bg-[#1B4332] border-2 border-[#35393C] text-[#A4D8FF] px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-[#0F1E36] border-2 border-[#35393C] text-[#A4D8FF] px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            {{ session('error') }}
        </div>
    @endif

    {{-- AI Credits card --}}
    <x-ai-credits-card
        :credits-remaining="$creditsRemaining"
        :credits-quota="$creditsQuota"
        :credit-refresh-interval="$creditRefreshInterval"
        :period-expires-at="$periodExpiresAt"
        :plan-slug="$planSlug"
        :plan-name="$planConfig['name'] ?? 'Free'"
        :allows-model-selection="$allowsModelSelection"
    />

    {{-- Quick stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF] tracking-tight">{{ $analyzedCount }}</span>
            <span class="text-[12px] font-mono uppercase tracking-wider text-[#A4D8FF]/70">Documentos analizados</span>
        </div>
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF] tracking-tight">{{ $riskCount }}</span>
            <span class="text-[12px] font-mono uppercase tracking-wider text-[#A4D8FF]/70">Cláusulas de riesgo</span>
        </div>
        <div class="bg-[#222629] border-2 border-[#35393C] p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF] tracking-tight">{{ $avgTime }}{{ $avgTime !== '--' ? ' s' : '' }}</span>
            <span class="text-[12px] font-mono uppercase tracking-wider text-[#A4D8FF]/70">Tiempo prom. de análisis</span>
        </div>
    </div>

    <x-provider-selector :current-provider="$currentProvider" :is-pro="false" />

    <x-dashboard-upload-zone :is-pro="false" :current-provider="$currentProvider" />

    <section id="recent-analyses-container" class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-serif font-bold text-[#A4D8FF] tracking-tight">Análisis recientes</h2>
            @if(count($pendingDocumentIds) > 0)
                <span class="text-[12px] font-mono text-[#A4D8FF] bg-[#0F1E36] px-2.5 py-1 border border-[#A4D8FF]/40 shadow-brutal-sm font-medium">Procesando...</span>
            @endif
        </div>

        @if($documents->isEmpty())
            <div class="flex flex-col items-center justify-center p-10 bg-[#222629]/50 border-2 border-dashed border-[#35393C] text-center gap-4">
                <h3 class="text-[14px] font-serif font-bold text-[#A4D8FF]">No hay análisis disponibles</h3>
                <p class="text-[12px] font-mono text-[#A4D8FF]/60">Sube tu primer archivo PDF o DOCX para comenzar.</p>
            </div>
        @else
            <x-documents-table :documents="$documents" />
        @endif
    </section>

    <x-document-status-poller :pending-document-ids="$pendingDocumentIds" />

</div>
@endsection
