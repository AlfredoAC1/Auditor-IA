@extends('layouts.app')

@section('title', 'Dashboard - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-6xl mx-auto w-full">

    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Dashboard</h1>
            <p class="text-[13px] text-zinc-400">{{ $analyzedCount }} {{ $analyzedCount === 1 ? 'análisis disponible' : 'análisis disponibles' }} en tu organización</p>
        </div>
        <button onclick="document.getElementById('file-input').click()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[13px] font-bold text-white shadow-lg transition-all duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Subir documento
        </button>
    </header>

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
            {{ session('error') }}
        </div>
    @endif

    <x-plan-usage-card
        :plan-name="$planConfig['name'] ?? 'Free'"
        :plan-slug="$planSlug"
        :documents-used="$documentsThisMonth"
        :documents-limit="$planConfig['max_documents_per_month'] ?? null"
        :analyses-used="$analysesThisMonth"
        :analyses-limit="$planConfig['max_analyses_per_month'] ?? null"
        :storage-used-bytes="$storageUsedBytes"
        :storage-limit-bytes="$planConfig['max_storage_bytes'] ?? null"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3">
            <span class="text-4xl font-extrabold text-white tracking-tight">{{ $analyzedCount }}</span>
            <span class="text-[13px] font-medium text-zinc-400">Documentos analizados</span>
        </div>
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3">
            <span class="text-4xl font-extrabold text-white tracking-tight">{{ $riskCount }}</span>
            <span class="text-[13px] font-medium text-zinc-400">Cláusulas de riesgo</span>
        </div>
        <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3">
            <span class="text-4xl font-extrabold text-white tracking-tight">{{ $avgTime }}{{ $avgTime !== '--' ? ' s' : '' }}</span>
            <span class="text-[13px] font-medium text-zinc-400">Tiempo prom. de análisis</span>
        </div>
    </div>

    <x-dashboard-upload-zone :is-pro="false" />

    <section id="recent-analyses-container" class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-white tracking-tight">Análisis recientes</h2>
            @if(count($pendingDocumentIds) > 0)
                <span class="text-[12px] text-amber-400 font-medium">Procesando...</span>
            @endif
        </div>

        @if($documents->isEmpty())
            <div class="flex flex-col items-center justify-center p-10 rounded-2xl bg-[#161616]/40 border border-zinc-800/40 text-center gap-4">
                <h3 class="text-[14px] font-bold text-zinc-200">No hay análisis disponibles</h3>
                <p class="text-[12px] text-zinc-500">Sube tu primer archivo PDF o DOCX para comenzar.</p>
            </div>
        @else
            <x-documents-table :documents="$documents" />
        @endif
    </section>

    <x-document-status-poller :pending-document-ids="$pendingDocumentIds" />

</div>
@endsection
