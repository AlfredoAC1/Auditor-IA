@extends('layouts.app')

@section('title', 'Auditoría Contextual Multidocumental - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    @php
        $content = $analysis->content ?? [];
        $supportingDocs = $analysis->supportingDocuments()->with('document')->get();
        $primaryAncillaryDoc = $supportingDocs->first()?->document;
        $pivot = $supportingDocs->first();
        $bonus = $content['bonus_insights'] ?? [];
        $complementaryData = $content['datos_complementarios'] ?? [];
        $contradictions = $content['contradicciones'] ?? [];
        $risks = $content['riesgos_identificados'] ?? [];
        $recommendations = $content['recomendaciones_finales'] ?? [];
    @endphp

    <!-- Top Navigation & Return -->
    <div class="flex items-center justify-between gap-4 pb-4 border-b border-gunmetal">
        <div class="flex items-center gap-3">
            <a href="{{ route('analysis.show', $document) }}" class="text-icy/70 hover:text-icy transition-colors text-[13px] font-mono font-bold flex items-center gap-1.5 uppercase">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Análisis Individual
            </a>
            <span class="text-zinc-600 font-mono">/</span>
            <a href="{{ route('dashboard') }}" class="text-icy/50 hover:text-icy transition-colors text-[13px] font-mono font-bold uppercase">
                Dashboard
            </a>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 text-[11px] font-bold bg-violet-600/20 text-violet-300 border border-violet-500/30 uppercase tracking-wider rounded-lg flex items-center gap-1.5 shadow-brutal-sm">
                <svg class="w-3.5 h-3.5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Auditoría Contextual PRO
            </span>
        </div>
    </div>

    <!-- Header Banner: Primary Document vs Ancillary Document -->
    <header class="bg-gunmetal-card border-2 border-violet-500/30 p-6 md:p-8 flex flex-col gap-6 shadow-brutal rounded-2xl relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-violet-400">Auditoría Cruzada Multidocumental</span>
                <span class="text-zinc-600">•</span>
                <span class="text-[11px] font-mono text-zinc-400">Versión {{ $analysis->version }}</span>
                <span class="text-zinc-600">•</span>
                <span class="text-[11px] font-mono text-zinc-400">{{ $analysis->created_at->diffForHumans() }}</span>
            </div>
            
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-white tracking-tight leading-snug">
                Análisis Conjunto: {{ $document->original_filename }}
            </h1>
        </div>

        <!-- Comparative Document Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <!-- Primary Document Card -->
            <div class="p-4 bg-gunmetal-dark/80 border border-gunmetal rounded-xl flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-navy-dark border border-icy/30 flex items-center justify-center text-icy shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-icy/60">Documento Principal</span>
                    <span class="text-[13px] font-bold text-white truncate" title="{{ $document->original_filename }}">{{ $document->original_filename }}</span>
                    <span class="text-[11px] font-mono text-zinc-400 mt-0.5">{{ $document->document_type?->label() ?? 'Contrato Base' }}</span>
                </div>
            </div>

            <!-- Ancillary Document Card -->
            <div class="p-4 bg-violet-950/20 border border-violet-500/30 rounded-xl flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-300 shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-violet-400">Documento Anexo / Relacionado</span>
                    <span class="text-[13px] font-bold text-white truncate" title="{{ $primaryAncillaryDoc?->original_filename ?? 'Anexo Adjunto' }}">
                        {{ $primaryAncillaryDoc?->original_filename ?? 'Anexo Adjunto' }}
                    </span>
                    <span class="text-[11px] font-mono text-violet-300 mt-0.5">
                        {{ $pivot?->relationshipLabel() ?? 'Adenda / Modificación' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Integrated Summary -->
        @if(!empty($content['resumen_integrado']))
            <div class="flex flex-col gap-2 pt-2 border-t border-gunmetal">
                <h3 class="text-[12px] font-mono font-bold uppercase tracking-wider text-icy/70">Resumen Ejecutivo Integrado</h3>
                <p class="text-[13px] font-sans text-icy/90 leading-relaxed">
                    {{ $content['resumen_integrado'] }}
                </p>
            </div>
        @endif

        <!-- Badges footer -->
        <div class="flex flex-wrap items-center gap-3 pt-2 font-mono text-[11px]">
            <span class="px-2.5 py-0.5 bg-gunmetal-dark border border-gunmetal text-icy rounded">
                Motor: {{ $analysis->llm_model ?? $analysis->llm_provider }}
            </span>
            <span class="px-2.5 py-0.5 bg-gunmetal-dark border border-gunmetal text-icy rounded">
                Tokens consumidos: {{ number_format($analysis->totalTokens()) }}
            </span>
            <span class="px-2.5 py-0.5 bg-gunmetal-dark border border-gunmetal text-icy rounded">
                Tiempo de procesamiento: {{ number_format($analysis->processing_time_ms / 1000, 2) }}s
            </span>
            @if(!empty($content['nivel_confianza']))
                <span class="px-2.5 py-0.5 bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 rounded font-bold">
                    Confianza: {{ strtoupper($content['nivel_confianza']) }}
                </span>
            @endif
        </div>
    </header>

    <!-- ================================================================ -->
    <!-- BONUS INFORMATION & JURIDICAL INSIGHTS (EXCLUSIVE PRO HIGHLIGHT) -->
    <!-- ================================================================ -->
    @if(!empty($bonus))
        <section class="bg-linear-to-b from-[#181226] to-[#12101a] border-2 border-violet-500/40 p-6 md:p-8 flex flex-col gap-5 shadow-brutal rounded-2xl relative">
            <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-violet-500/20">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-violet-600/30 border border-violet-400/40 flex items-center justify-center text-violet-300">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-serif font-bold text-white tracking-tight">Bonus Insights Jurídicos Avanzados</h2>
                        <span class="text-[11px] font-mono text-violet-400">Interpretación cruzada exclusiva para usuarios PRO</span>
                    </div>
                </div>

                @php
                    $riskDelta = strtolower($bonus['variacion_del_riesgo'] ?? 'se_mantiene');
                    [$riskBadgeBg, $riskBadgeText] = match($riskDelta) {
                        'aumenta' => ['bg-rose-950/60 border-rose-500/40', 'text-rose-300'],
                        'disminuye' => ['bg-emerald-950/60 border-emerald-500/40', 'text-emerald-300'],
                        default => ['bg-zinc-800 border-zinc-700', 'text-zinc-300'],
                    };
                @endphp
                <div class="px-3 py-1 rounded-lg border {{ $riskBadgeBg }} {{ $riskBadgeText }} text-[12px] font-mono font-bold uppercase flex items-center gap-1.5">
                    <span>Delta de Riesgo:</span>
                    <span>{{ strtoupper(str_replace('_', ' ', $riskDelta)) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Prevalencia Jurídica -->
                <div class="p-4 bg-[#1b152b] border border-violet-500/20 rounded-xl flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-violet-300 text-[12px] font-mono font-bold uppercase">
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        Prevalencia Jurídica
                    </div>
                    <p class="text-[12px] font-sans text-icy/85 leading-relaxed">
                        {{ $bonus['prevalencia_juridica'] ?? 'No se detectaron conflictos de jerarquía explícitos.' }}
                    </p>
                </div>

                <!-- Vigencia y Personerías -->
                <div class="p-4 bg-[#1b152b] border border-violet-500/20 rounded-xl flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-violet-300 text-[12px] font-mono font-bold uppercase">
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Vigencia y Representación
                    </div>
                    <p class="text-[12px] font-sans text-icy/85 leading-relaxed">
                        {{ $bonus['vigencia_y_personerias'] ?? 'Sin observaciones sobre poderes o vigencia.' }}
                    </p>
                </div>

                <!-- Justificación del Perfil de Riesgo -->
                <div class="p-4 bg-[#1b152b] border border-violet-500/20 rounded-xl flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-violet-300 text-[12px] font-mono font-bold uppercase">
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Impacto en Riesgo Global
                    </div>
                    <p class="text-[12px] font-sans text-icy/85 leading-relaxed">
                        {{ $bonus['justificacion_riesgo'] ?? 'El perfil de riesgo se mantiene dentro de los parámetros del contrato original.' }}
                    </p>
                </div>
            </div>
        </section>
    @endif

    <!-- ================================================================ -->
    <!-- DATOS COMPLEMENTARIOS Y MODIFICACIONES CONTRACTUALES -->
    <!-- ================================================================ -->
    <section class="bg-gunmetal-card border-2 border-gunmetal p-6 md:p-8 flex flex-col gap-5 shadow-brutal rounded-2xl">
        <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-gunmetal">
            <h2 class="text-[16px] font-serif font-bold text-white tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Datos Complementarios y Modificaciones Detectadas
            </h2>
            <span class="text-[11px] font-mono text-zinc-400">Total: {{ count($complementaryData) }}</span>
        </div>

        @if(empty($complementaryData))
            <p class="text-[13px] font-mono text-zinc-500 italic py-4 text-center">
                No se registraron adendas ni modificaciones sustanciales en las cláusulas operativas.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($complementaryData as $item)
                    @php
                        $impact = strtolower($item['impacto'] ?? 'neutro');
                        $impactClass = match($impact) {
                            'critico'  => 'bg-rose-950/40 text-rose-300 border-rose-500/30',
                            'positivo' => 'bg-emerald-950/40 text-emerald-300 border-emerald-500/30',
                            default    => 'bg-zinc-800 text-zinc-300 border-zinc-700',
                        };
                    @endphp
                    <div class="p-4 bg-gunmetal-dark border border-gunmetal rounded-xl flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-[13px] font-bold text-white">{{ $item['elemento'] ?? 'Cláusula / Modificación' }}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase border {{ $impactClass }}">
                                {{ $impact }}
                            </span>
                        </div>
                        <p class="text-[12px] font-sans text-icy/80 leading-relaxed">
                            {{ $item['detalle'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- ================================================================ -->
    <!-- CONTRADICCIONES Y HALLAZGOS CRUZADOS -->
    <!-- ================================================================ -->
    <section class="bg-gunmetal-card border-2 border-gunmetal p-6 md:p-8 flex flex-col gap-5 shadow-brutal rounded-2xl">
        <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-gunmetal">
            <h2 class="text-[16px] font-serif font-bold text-white tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Discrepancias e Inconsistencias entre Documentos
            </h2>
            <span class="text-[11px] font-mono text-zinc-400">Detectadas: {{ count($contradictions) }}</span>
        </div>

        @if(empty($contradictions))
            <div class="p-5 bg-emerald-950/20 border border-emerald-500/20 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <p class="text-[12.5px] font-sans text-emerald-200">
                    No se detectaron contradicciones ni incompatibilidades normativas entre el documento principal y el anexo adjunto. Coherencia jurídica verificada.
                </p>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach($contradictions as $con)
                    <div class="p-4 bg-rose-950/20 border border-rose-500/30 rounded-xl flex flex-col gap-2.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                Conflicto Detectado
                            </span>
                            @if(!empty($con['documentos_involucrados']))
                                <span class="text-[11px] font-mono text-zinc-400 truncate">
                                    Entre: {{ implode(' ↔ ', (array) $con['documentos_involucrados']) }}
                                </span>
                            @endif
                        </div>
                        <p class="text-[12.5px] font-sans text-rose-100 font-medium leading-relaxed">
                            {{ $con['descripcion'] ?? '' }}
                        </p>
                        @if(!empty($con['recomendacion']))
                            <div class="p-2.5 bg-black/40 rounded-lg border border-rose-500/20 text-[11.5px] font-sans text-zinc-300 flex items-start gap-2">
                                <span class="font-bold text-rose-300 shrink-0">Acción sugerida:</span>
                                <span>{{ $con['recomendacion'] }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- ================================================================ -->
    <!-- RIESGOS INTEGRADOS Y RECOMENDACIONES -->
    <!-- ================================================================ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Riesgos Conjuntos -->
        <section class="bg-gunmetal-card border-2 border-gunmetal p-6 flex flex-col gap-4 shadow-brutal rounded-2xl">
            <h3 class="text-[15px] font-serif font-bold text-white tracking-tight flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Riesgos Jurídicos Identificados
            </h3>

            @if(empty($risks))
                <p class="text-[12.5px] font-mono text-zinc-500 italic">No se identificaron riesgos adicionales de severidad relevante.</p>
            @else
                <div class="flex flex-col gap-3">
                    @foreach($risks as $r)
                        @php
                            $sev = strtolower($r['severidad'] ?? 'media');
                            $sevClass = match($sev) {
                                'alta' => 'text-rose-400 border-rose-500/30 bg-rose-500/10',
                                'media' => 'text-amber-400 border-amber-500/30 bg-amber-500/10',
                                default => 'text-zinc-400 border-zinc-700 bg-zinc-800',
                            };
                        @endphp
                        <div class="p-3 bg-gunmetal-dark border border-gunmetal rounded-xl flex flex-col gap-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase border {{ $sevClass }}">
                                    Riesgo {{ $sev }}
                                </span>
                                @if(!empty($r['referencia_legal']))
                                    <span class="text-[10.5px] font-mono text-zinc-400 truncate">{{ $r['referencia_legal'] }}</span>
                                @endif
                            </div>
                            <p class="text-[12px] font-sans text-icy/85 leading-relaxed">{{ $r['riesgo'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Recomendaciones Finales -->
        <section class="bg-gunmetal-card border-2 border-gunmetal p-6 flex flex-col gap-4 shadow-brutal rounded-2xl">
            <h3 class="text-[15px] font-serif font-bold text-white tracking-tight flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Recomendaciones Integradas
            </h3>

            @if(empty($recommendations))
                <p class="text-[12.5px] font-mono text-zinc-500 italic">No hay recomendaciones adicionales registradas.</p>
            @else
                <div class="flex flex-col gap-3">
                    @foreach($recommendations as $rec)
                        <div class="p-3 bg-gunmetal-dark border border-gunmetal rounded-xl flex flex-col gap-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[12.5px] font-bold text-white">{{ $rec['recomendacion'] ?? '' }}</span>
                                <span class="text-[10px] font-mono font-bold uppercase text-violet-400">
                                    {{ $rec['prioridad'] ?? 'Media' }}
                                </span>
                            </div>
                            @if(!empty($rec['justificacion']))
                                <p class="text-[11.5px] font-sans text-zinc-400 leading-relaxed">{{ $rec['justificacion'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

</div>
@endsection
