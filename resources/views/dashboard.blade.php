@extends('layouts.app')

@section('title', 'Dashboard - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-6xl mx-auto w-full">

    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Dashboard</h1>
            <p class="text-[13px] font-mono text-icy/70">Bienvenido, {{ auth()->user()->name }}</p>
        </div>
        <button onclick="document.getElementById('file-input').click()" class="flex items-center gap-2 px-4 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
            <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Subir documento
        </button>
    </header>

    @if(session('success'))
        <div class="bg-[#1B4332] border-2 border-gunmetal text-icy px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-icy shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-navy-dark border-2 border-gunmetal text-icy px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
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
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-4xl font-serif font-extrabold text-icy tracking-tight">{{ $analyzedCount }}</span>
            <span class="text-[12px] font-mono uppercase tracking-wider text-icy/70">Documentos analizados</span>
        </div>
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-4xl font-serif font-extrabold text-icy tracking-tight">{{ $riskCount }}</span>
            <span class="text-[12px] font-mono uppercase tracking-wider text-icy/70">Cláusulas de riesgo</span>
        </div>
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-4xl font-serif font-extrabold text-icy tracking-tight">{{ $avgTime }}{{ $avgTime !== '--' ? ' s' : '' }}</span>
            <span class="text-[12px] font-mono uppercase tracking-wider text-icy/70">Tiempo prom. de análisis</span>
        </div>
    </div>

    {{-- AI Engine Information Banner --}}
    <div class="bg-gunmetal-dark border-2 border-gunmetal p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-brutal">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-navy-dark border border-icy/40 flex items-center justify-center text-icy shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-[13px] font-mono font-bold text-icy">Motor IA: Inferencia Legal Ultrarrápida</span>
                <span class="text-[11px] font-mono text-icy/60">Análisis automático con modelos optimizados para legislación chilena.</span>
            </div>
        </div>
        <a href="{{ route('landing') }}#planes" class="inline-flex items-center gap-1.5 text-[11px] font-mono font-bold text-icy hover:underline uppercase tracking-wider self-start sm:self-auto">
            Desbloquear Gemma 4 (31B) & Modelos Pro &rarr;
        </a>
    </div>

    <x-dashboard-upload-zone :is-pro="false" :selected-model="$selectedModel" :selected-model-slug="$selectedModelSlug" />

    <section id="recent-analyses-container" class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-serif font-bold text-icy tracking-tight">Análisis recientes</h2>
            @if(count($pendingDocumentIds) > 0)
                <span class="text-[12px] font-mono text-icy bg-navy-dark px-2.5 py-1 border border-icy/40 shadow-brutal-sm font-medium">Procesando...</span>
            @endif
        </div>

        @if($documents->isEmpty())
            <div class="flex flex-col items-center justify-center p-10 bg-gunmetal-card/50 border-2 border-dashed border-gunmetal text-center gap-4">
                <h3 class="text-[14px] font-serif font-bold text-icy">No hay análisis disponibles</h3>
                <p class="text-[12px] font-mono text-icy/60">Sube tu primer archivo PDF o DOCX para comenzar.</p>
            </div>
        @else
            <x-documents-table :documents="$documents" />
        @endif
    </section>

    <x-document-status-poller :pending-document-ids="$pendingDocumentIds" />

</div>
@endsection
