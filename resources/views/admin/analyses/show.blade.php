@extends('layouts.app')

@section('title', 'Detalle de Análisis #' . substr($analysis->id, 0, 8) . ' - Admin Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col gap-1 pb-4 border-b-2 border-gunmetal">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.analyses.index') }}" class="text-icy/60 hover:text-icy text-[13px] font-mono">← Auditoría de Análisis</a>
            <span class="text-icy/40">/</span>
            <h1 class="text-xl md:text-2xl font-serif font-bold text-icy tracking-tight">Inspección de Análisis</h1>
        </div>
        <p class="text-[12px] font-mono text-icy/70">ID: {{ $analysis->id }} · Versión: {{ $analysis->version }}</p>
    </header>

    {{-- Metadata Summary Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col">
            <span class="text-[11px] font-mono text-icy/60 uppercase">Documento</span>
            <span class="font-bold text-icy text-[13px] truncate" title="{{ $analysis->document?->original_filename }}">
                {{ $analysis->document?->original_filename ?? '—' }}
            </span>
        </div>

        <div class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col">
            <span class="text-[11px] font-mono text-icy/60 uppercase">Tipo de Análisis</span>
            <span class="font-bold text-icy text-[13px]">
                {{ $analysis->analysis_type->label() }}
            </span>
        </div>

        <div class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col">
            <span class="text-[11px] font-mono text-icy/60 uppercase">Modelo LLM</span>
            <span class="font-bold text-icy text-[13px] truncate">
                {{ $analysis->llm_model }} ({{ $analysis->llm_provider }})
            </span>
        </div>

        <div class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col">
            <span class="text-[11px] font-mono text-icy/60 uppercase">Tokens Consumidos</span>
            <span class="font-bold text-icy text-[13px]">
                {{ number_format($analysis->totalTokens()) }} (In: {{ number_format($analysis->input_tokens) }} / Out: {{ number_format($analysis->output_tokens) }})
            </span>
        </div>
    </div>

    {{-- Performance & Traceability Details --}}
    <div class="bg-gunmetal-card border-2 border-gunmetal p-5 shadow-brutal flex flex-col gap-3">
        <h2 class="text-sm font-serif font-bold text-icy uppercase tracking-wider border-b border-gunmetal pb-2">Trazabilidad Técnica</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-[12.5px] font-mono">
            <div>
                <span class="text-icy/60 block">Organización:</span>
                <span class="text-icy font-bold">{{ $analysis->organization?->name }} (ID: {{ $analysis->organization_id }})</span>
            </div>
            <div>
                <span class="text-icy/60 block">Solicitado por:</span>
                <span class="text-icy font-bold">{{ $analysis->requester?->name ?? 'Sistema' }} ({{ $analysis->requester?->email }})</span>
            </div>
            <div>
                <span class="text-icy/60 block">Tiempo de Ejecución:</span>
                <span class="text-icy font-bold">{{ $analysis->processing_time_ms ? number_format($analysis->processing_time_ms / 1000, 2) . ' segundos' : '—' }}</span>
            </div>
            <div>
                <span class="text-icy/60 block">Puntaje de Confianza:</span>
                <span class="text-icy font-bold">{{ $analysis->confidence_score !== null ? number_format((float) $analysis->confidence_score * 100, 0) . '%' : 'N/A' }}</span>
            </div>
            <div>
                <span class="text-icy/60 block">Fecha y Hora:</span>
                <span class="text-icy font-bold">{{ $analysis->created_at?->toIso8601String() }}</span>
            </div>
            <div>
                <span class="text-icy/60 block">Documentos de Apoyo:</span>
                <span class="text-icy font-bold">{{ $analysis->supportingDocuments->count() }} adjuntos</span>
            </div>
        </div>
    </div>

    {{-- Structured JSON Content --}}
    <div class="bg-gunmetal-card border-2 border-gunmetal p-5 shadow-brutal flex flex-col gap-3">
        <div class="flex items-center justify-between border-b border-gunmetal pb-2">
            <h2 class="text-sm font-serif font-bold text-icy uppercase tracking-wider">Contenido Estructurado Generado (JSON)</h2>
            <button 
                type="button" 
                onclick="navigator.clipboard.writeText(document.getElementById('analysis-json-content').innerText); alert('JSON copiado al portapapeles');" 
                class="text-[11px] font-mono text-icy/70 hover:text-icy underline"
            >
                Copiar JSON
            </button>
        </div>
        <pre id="analysis-json-content" class="bg-navy-dark p-4 border border-gunmetal text-icy text-[12px] font-mono overflow-x-auto max-h-96 whitespace-pre-wrap selection:bg-icy selection:text-black">@json($analysis->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
    </div>

    {{-- Prompt Metadata (if any) --}}
    @if(!empty($analysis->prompt_metadata))
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 shadow-brutal flex flex-col gap-3">
            <h2 class="text-sm font-serif font-bold text-icy uppercase tracking-wider border-b border-gunmetal pb-2">Metadata de Prompt y RAG</h2>
            <pre class="bg-navy-dark p-4 border border-gunmetal text-icy text-[12px] font-mono overflow-x-auto max-h-64 whitespace-pre-wrap">@json($analysis->prompt_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
        </div>
    @endif

</div>
@endsection
