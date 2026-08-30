@extends('layouts.app')

@section('title', 'Detalle de Auditoría - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    @if(!$analysis)
        <!-- Empty / Pending State -->
        <div class="flex-1 flex flex-col items-center justify-center text-center gap-5 max-w-lg mx-auto w-full min-h-[75vh]">
            <!-- Icon -->
            <div class="w-16 h-16 bg-navy-dark border-2 border-gunmetal-dark flex items-center justify-center text-icy shadow-brutal">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h1 class="text-xl font-serif font-bold text-icy tracking-tight">Análisis no disponible</h1>
                @if($document->status->value === 'processing')
                    <p class="text-[13px] font-mono text-icy/70 leading-relaxed">
                        Este documento se está analizando actualmente. Por favor, recarga la página en unos segundos.
                    </p>
                @elseif($document->status->value === 'failed')
                    <p class="text-[13px] font-mono text-icy leading-relaxed">
                        El procesamiento de este documento falló: {{ $document->metadata['last_error'] ?? 'Error desconocido' }}
                    </p>
                @else
                    <p class="text-[13px] font-mono text-icy/60 leading-relaxed">
                        No hay análisis completados para este documento. Sube un archivo en tu Dashboard para generar el reporte.
                    </p>
                @endif
            </div>
            <a href="{{ route('dashboard') }}" class="mt-2 px-5 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                Ir al Dashboard
            </a>
        </div>
    @else
        @php
            $content = $analysis->content;
        @endphp

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b-2 border-gunmetal">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="text-icy/70 hover:text-icy transition-colors text-[13px] font-mono font-bold flex items-center gap-1.5 uppercase">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">{{ $document->original_filename }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 font-mono">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold bg-navy-dark text-icy border border-icy/40 shadow-brutal-sm uppercase">
                        {{ $content['tipo_documento'] ?? 'Documento Legal' }}
                    </span>
                    <span class="text-[12px] text-icy/70">
                        Auditado {{ $analysis->created_at->diffForHumans() }}
                    </span>
                    <span class="px-2.5 py-0.5 text-[11px] font-bold {{ str_contains(strtolower($analysis->llm_provider), 'nvidia') ? 'bg-[#2D4F3B] text-[#D5E8D4] border border-gunmetal-dark' : 'bg-navy-dark text-icy border border-icy/40' }} shadow-brutal-sm uppercase">
                        Motor IA: {{ str_contains(strtolower($analysis->llm_provider), 'nvidia') ? 'NVIDIA NIM' : 'Groq Cloud' }}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('analysis.clauses', $document->id) }}" class="flex items-center gap-2 px-4 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                    <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Ver Cláusulas
                </a>

                <form action="{{ route('dashboard.documents.destroy', $document->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este documento y su análisis en cascada? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-navy-dark hover:bg-navy-light border border-gunmetal-dark text-[13px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar Documento
                    </button>
                </form>
            </div>
        </header>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Columns: Summary, Risks, Clauses -->
            <div class="lg:col-span-2 flex flex-col gap-8">
                
                <!-- Resumen General -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-6 md:p-7 flex flex-col gap-4 shadow-brutal">
                    <h2 class="text-xl font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Resumen General
                    </h2>
                    <p class="text-[13.5px] font-sans text-icy/90 leading-relaxed font-normal">
                        {{ $content['resumen_general'] ?? 'No se generó resumen.' }}
                    </p>
                </section>

                <!-- Riesgos Identificados -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-6 md:p-7 flex flex-col gap-5 shadow-brutal">
                    <h2 class="text-xl font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Riesgos Detectados ({{ is_array($content['riesgos_identificados'] ?? null) ? count($content['riesgos_identificados']) : 0 }})
                    </h2>

                    @if(empty($content['riesgos_identificados']))
                        <div class="text-center py-6 text-icy/60 font-mono text-[13px] italic">
                            No se detectaron riesgos de severidad en el documento.
                        </div>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($content['riesgos_identificados'] as $r)
                                <div class="p-4 bg-gunmetal-dark border-2 border-gunmetal flex flex-col gap-2 shadow-brutal-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <h3 class="text-[14px] font-serif font-bold text-icy">{{ $r['riesgo'] ?? 'Riesgo indefinido' }}</h3>
                                        @php
                                            $severity = strtolower($r['severidad'] ?? 'baja');
                                            $badgeClass = match($severity) {
                                                'alta' => 'bg-navy-dark text-icy border-gunmetal',
                                                'media' => 'bg-gunmetal text-icy border-gunmetal',
                                                default => 'bg-gunmetal-dark text-icy/70 border-gunmetal'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider border shadow-brutal-sm {{ $badgeClass }}">
                                             Severidad {{ $severity }}
                                        </span>
                                    </div>
                                    <p class="text-[12.5px] font-sans text-icy/80 leading-relaxed">{{ $r['explicacion'] ?? '' }}</p>
                                    @if(!empty($r['referencia_legal']))
                                        <div class="mt-1 flex items-center gap-1.5 text-[11px] font-mono text-icy/70 bg-gunmetal-card p-2 border border-gunmetal">
                                            <svg class="w-3.5 h-3.5 text-icy shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                                            </svg>
                                            Normativa Chilena: {{ $r['referencia_legal'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Cláusulas Principales -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-6 md:p-7 flex flex-col gap-5 shadow-brutal">
                    <h2 class="text-xl font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        Cláusulas Clave Analizadas
                    </h2>

                    @if(empty($content['clausulas_principales']))
                        <div class="text-center py-6 text-icy/60 font-mono text-[13px] italic">
                            No se desglosaron cláusulas.
                        </div>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($content['clausulas_principales'] as $c)
                                <div class="p-4 bg-gunmetal-dark border-2 border-gunmetal flex flex-col gap-2 shadow-brutal-sm">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($c['numero']))
                                            <span class="w-6 h-6 bg-navy-dark border border-icy/40 flex items-center justify-center text-[11px] font-mono font-bold text-icy">
                                                {{ $c['numero'] }}
                                            </span>
                                        @endif
                                        <h3 class="text-[13.5px] font-serif font-bold text-icy">{{ $c['titulo'] ?? 'Cláusula' }}</h3>
                                    </div>
                                    <p class="text-[12.5px] font-sans text-icy/85 leading-relaxed">{{ $c['explicacion'] ?? '' }}</p>
                                    @if(!empty($c['implicacion_legal']))
                                        <p class="text-[11.5px] font-mono text-icy/70 italic mt-0.5"><strong class="text-icy not-italic font-bold">Impacto Legal:</strong> {{ $c['implicacion_legal'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <!-- Right Column: Metadata, Provider, Parties, Recommendations -->
            <div class="flex flex-col gap-8">
                
                <!-- Motor de Inteligencia Artificial -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-3.5 shadow-brutal">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                            <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Inferencia y Motor IA
                        </h3>
                        @if(str_contains(strtolower($analysis->llm_provider), 'nvidia'))
                            <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold bg-[#2D4F3B] text-[#D5E8D4] border border-gunmetal-dark shadow-brutal-sm uppercase tracking-wider">
                                NVIDIA NIM
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold bg-navy-dark text-icy border border-icy/40 shadow-brutal-sm uppercase tracking-wider">
                                Groq Cloud
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-[12px] font-mono pt-1 border-t border-gunmetal">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-icy/60 uppercase">Proveedor</span>
                            <span class="text-icy font-bold uppercase">{{ $analysis->llm_provider }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-icy/60 uppercase">Modelo</span>
                            <span class="text-icy font-bold truncate" title="{{ $analysis->llm_model }}">{{ $analysis->llm_model }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-icy/60 uppercase">Respuesta</span>
                            <span class="text-icy font-bold">{{ number_format($analysis->processing_time_ms / 1000, 2) }} s</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-icy/60 uppercase">Tokens Consumidos</span>
                            <span class="text-icy font-bold">{{ number_format($analysis->totalTokens(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </section>
                
                <!-- Diagnóstico & Abogado -->
                @if(isset($content['requiere_abogado']) && $content['requiere_abogado'])
                    <div class="bg-navy-dark border-2 border-gunmetal-dark p-5 flex flex-col gap-3 shadow-brutal">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gunmetal-dark border border-icy/40 flex items-center justify-center text-icy">
                                <svg class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-[14px] font-serif font-bold text-icy">Recomendación Legal</h3>
                        </div>
                        <p class="text-[12px] font-mono text-icy/90 leading-relaxed">
                            Este documento contiene cláusulas de riesgo o compromisos significativos según la legislación chilena. Se recomienda encarecidamente consultar a un abogado calificado antes de firmar.
                        </p>
                    </div>
                @endif

                <!-- Partes Involucradas -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-3.5 shadow-brutal">
                    <h3 class="text-[14px] font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Partes Identificadas
                    </h3>
                    @if(empty($content['partes_involucradas']))
                        <span class="text-[12px] font-mono text-icy/60 italic">No identificadas</span>
                    @else
                        <ul class="flex flex-col gap-2 font-mono">
                            @foreach($content['partes_involucradas'] as $p)
                                <li class="text-[12.5px] text-icy flex items-center gap-2 px-3 py-2 bg-gunmetal-dark border border-gunmetal">
                                    <span class="w-1.5 h-1.5 bg-icy"></span>
                                    {{ $p }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <!-- Recomendaciones Proactivas -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-4 shadow-brutal">
                    <h3 class="text-[14px] font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Acciones Sugeridas
                    </h3>
                    @if(empty($content['recomendaciones']))
                        <span class="text-[12px] font-mono text-icy/60 italic">Sin recomendaciones</span>
                    @else
                        <div class="flex flex-col gap-3.5">
                            @foreach($content['recomendaciones'] as $rec)
                                <div class="flex flex-col gap-1.5 p-3 bg-gunmetal-dark border border-gunmetal">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="text-[12.5px] font-serif font-bold text-icy">{{ $rec['recomendacion'] ?? 'Recomendación' }}</h4>
                                        @php
                                            $priority = strtolower($rec['prioridad'] ?? 'baja');
                                            $prioClass = match($priority) {
                                                'alta' => 'text-icy',
                                                'media' => 'text-icy/80',
                                                default => 'text-icy/60'
                                            };
                                        @endphp
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $prioClass }}">
                                            {{ $priority }}
                                        </span>
                                    </div>
                                    @if(!empty($rec['justificacion']))
                                        <p class="text-[11.5px] font-sans text-icy/80 leading-relaxed">{{ $rec['justificacion'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Marco Legal Chileno Aplicable -->
                <section class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-3.5 shadow-brutal">
                    <h3 class="text-[14px] font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                        </svg>
                        Marco Legal Aplicable
                    </h3>
                    @if(empty($content['leyes_aplicables']))
                        <span class="text-[12px] font-mono text-icy/60 italic">No declaradas</span>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($content['leyes_aplicables'] as $ley)
                                <span class="px-2 py-1 bg-gunmetal-dark border border-gunmetal text-[11px] font-mono text-icy">
                                    {{ $ley }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- SUPPORTING DOCUMENTS SECTION (full width, below main grid) --}}
        {{-- ================================================================ --}}
        <section id="supporting-docs-section" class="bg-[#161616]/40 border border-violet-500/20 rounded-2xl p-6 flex flex-col gap-5">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-[15px] font-bold text-white tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Documentos de Apoyo
                    <span id="supporting-docs-count" class="ml-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-violet-600/10 text-violet-400 border border-violet-500/20">
                        {{ $analysis ? $analysis->supportingDocuments()->count() : 0 }}
                    </span>
                </h2>
                <div class="flex items-center gap-3">
                    <button
                        id="btn-analyze-context"
                        type="button"
                        class="hidden flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white transition-all shadow-lg shadow-violet-500/20 cursor-pointer"
                        onclick="triggerContextualAnalysis()"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z" />
                        </svg>
                        Analizar con Contexto
                    </button>
                    <button
                        id="btn-attach-doc"
                        type="button"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-750 border border-zinc-700 text-[13px] font-bold text-white transition-all cursor-pointer"
                        onclick="openAttachModal()"
                    >
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Adjuntar Documento
                    </button>
                </div>
            </div>

            <p class="text-[12px] text-zinc-500 leading-relaxed">
                Adjunta documentos relacionados, referenciados o complementarios a este análisis. La IA podrá acceder a todos ellos para generar un análisis contextual enriquecido.
            </p>

            {{-- Supporting Documents List --}}
            <div id="supporting-docs-list" class="flex flex-col gap-3">
                @php $supportingDocs = $analysis ? $analysis->supportingDocuments()->with('document')->get() : collect(); @endphp
                @if($supportingDocs->isEmpty())
                    <div id="empty-state" class="text-center py-8 text-zinc-600 text-[13px] italic flex flex-col items-center gap-2">
                        <svg class="w-8 h-8 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Sin documentos de apoyo adjuntos aún.
                    </div>
                @else
                    @foreach($supportingDocs as $pivot)
                        <div class="supporting-doc-item flex items-center justify-between gap-3 p-4 rounded-xl bg-zinc-900/30 border border-zinc-800/60" data-pivot-id="{{ $pivot->id }}" data-document-id="{{ $pivot->document_id }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-violet-600/10 border border-violet-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <span class="text-[13px] font-semibold text-white truncate">{{ $pivot->document?->original_filename ?? 'Documento' }}</span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($pivot->relationship_type)
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-violet-400 bg-violet-600/10 border border-violet-500/10 px-1.5 py-0.5 rounded">
                                                {{ $pivot->relationshipLabel() }}
                                            </span>
                                        @endif
                                        @if(isset($pivot->document?->metadata['parsed_compact_content']))
                                            <span class="text-[10px] font-bold text-emerald-400">✓ Procesado</span>
                                        @else
                                            <span class="text-[10px] font-bold text-amber-400">⚠ Sin procesar</span>
                                        @endif
                                    </div>
                                    @if($pivot->notes)
                                        <span class="text-[11px] text-zinc-500 italic truncate">{{ $pivot->notes }}</span>
                                    @endif
                                </div>
                            </div>
                            <button
                                type="button"
                                class="flex-shrink-0 p-2 rounded-lg hover:bg-rose-500/10 text-zinc-600 hover:text-rose-400 transition-colors cursor-pointer"
                                title="Desvincular documento"
                                onclick="detachDocument('{{ $analysis->id }}', '{{ $pivot->document_id }}', this)"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Contextual Analysis Result --}}
            <div id="contextual-result" class="hidden flex flex-col gap-4 pt-4 border-t border-zinc-800/60">
                <h3 class="text-[14px] font-bold text-white">Resultado del Análisis Contextual</h3>
                <div id="contextual-result-content" class="text-[12.5px] text-zinc-300 leading-relaxed bg-zinc-900/40 rounded-xl p-4 border border-zinc-800"></div>
            </div>
        </section>

        {{-- ================================================================ --}}
        {{-- ATTACH DOCUMENT MODAL --}}
        {{-- ================================================================ --}}
        <div id="attach-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg bg-[#111] border border-zinc-800 rounded-2xl p-6 flex flex-col gap-5 shadow-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-[16px] font-bold text-white">Adjuntar Documento de Apoyo</h3>
                    <button type="button" onclick="closeAttachModal()" class="text-zinc-500 hover:text-white transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="attach-form" onsubmit="submitAttachForm(event)">
                    @csrf
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="attach-document-select" class="text-[13px] font-semibold text-zinc-300">Documento de apoyo <span class="text-rose-400">*</span></label>
                            <select
                                id="attach-document-select"
                                name="document_id"
                                required
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] text-white focus:border-violet-500 focus:outline-none transition-colors"
                            >
                                <option value="">Seleccionar documento...</option>
                                @foreach(\App\Models\Document::query()->forOrganization(auth()->user()->organization_id)->where('id', '!=', $document->id)->orderByDesc('created_at')->limit(50)->get() as $doc)
                                    <option value="{{ $doc->id }}" {{ !isset($doc->metadata['parsed_compact_content']) ? 'data-unparsed=true' : '' }}>
                                        {{ $doc->original_filename }}{{ !isset($doc->metadata['parsed_compact_content']) ? ' ⚠ (sin procesar)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="attach-relationship" class="text-[13px] font-semibold text-zinc-300">Tipo de relación</label>
                            <select id="attach-relationship" name="relationship_type" class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] text-white focus:border-violet-500 focus:outline-none transition-colors">
                                <option value="">Sin especificar</option>
                                <option value="referenced">Referenciado — el documento principal lo menciona</option>
                                <option value="related">Relacionado — misma operación o partes</option>
                                <option value="clarification">Aclaración — explica o amplía el principal</option>
                                <option value="complementary">Complementario — aporta contexto adicional</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="attach-notes" class="text-[13px] font-semibold text-zinc-300">Notas <span class="text-zinc-600 font-normal">(opcional)</span></label>
                            <textarea
                                id="attach-notes"
                                name="notes"
                                rows="2"
                                maxlength="1000"
                                placeholder="¿Por qué este documento es relevante para el análisis?"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] text-white placeholder-zinc-600 focus:border-violet-500 focus:outline-none transition-colors resize-none"
                            ></textarea>
                        </div>

                        <div id="attach-error" class="hidden text-[12px] text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-xl px-3 py-2"></div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" onclick="closeAttachModal()" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-[13px] font-semibold text-zinc-300 transition-colors cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit" id="attach-submit-btn" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Adjuntar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    @endif

</div>

@push('scripts')
<script>
    const ANALYSIS_ID = '{{ $analysis?->id }}';
    const API_BASE    = '/api/v1';

    // ---------------------------------------------------------------
    // Modal helpers
    // ---------------------------------------------------------------
    function openAttachModal() {
        document.getElementById('attach-modal').classList.remove('hidden');
        document.getElementById('attach-modal').classList.add('flex');
        document.getElementById('attach-error').classList.add('hidden');
    }

    function closeAttachModal() {
        document.getElementById('attach-modal').classList.add('hidden');
        document.getElementById('attach-modal').classList.remove('flex');
    }

    // ---------------------------------------------------------------
    // Attach document
    // ---------------------------------------------------------------
    async function submitAttachForm(event) {
        event.preventDefault();
        const btn  = document.getElementById('attach-submit-btn');
        const errEl = document.getElementById('attach-error');
        const form = document.getElementById('attach-form');

        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Adjuntando...';
        errEl.classList.add('hidden');

        const formData = new FormData(form);
        const body = {
            document_id:       formData.get('document_id'),
            relationship_type: formData.get('relationship_type') || null,
            notes:             formData.get('notes') || null,
        };

        try {
            const res = await fetch(`${API_BASE}/analyses/${ANALYSIS_ID}/supporting-documents`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });

            const data = await res.json();

            if (!res.ok) {
                errEl.textContent = data.message ?? 'Error al adjuntar el documento.';
                errEl.classList.remove('hidden');
                return;
            }

            // Append new item to list UI
            addDocumentToList(data.supporting_document);
            updateCount(1);
            closeAttachModal();
            form.reset();
        } catch (e) {
            errEl.textContent = 'Error de conexión. Intente nuevamente.';
            errEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Adjuntar';
        }
    }

    function addDocumentToList(doc) {
        const list = document.getElementById('supporting-docs-list');
        const empty = document.getElementById('empty-state');
        if (empty) empty.remove();

        const isParsed = doc.is_parsed;
        const relLabel = doc.relationship_label || 'Adjunto';

        const div = document.createElement('div');
        div.className = 'supporting-doc-item flex items-center justify-between gap-3 p-4 rounded-xl bg-zinc-900/30 border border-zinc-800/60';
        div.setAttribute('data-pivot-id', doc.pivot_id ?? '');
        div.setAttribute('data-document-id', doc.id ?? '');
        div.innerHTML = `
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-violet-600/10 border border-violet-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex flex-col gap-0.5 min-w-0">
                    <span class="text-[13px] font-semibold text-white truncate">${escapeHtml(doc.original_filename ?? 'Documento')}</span>
                    <div class="flex items-center gap-2 flex-wrap">
                        ${doc.relationship_type ? `<span class="text-[10px] font-bold uppercase tracking-wider text-violet-400 bg-violet-600/10 border border-violet-500/10 px-1.5 py-0.5 rounded">${escapeHtml(relLabel)}</span>` : ''}
                        <span class="text-[10px] font-bold ${isParsed ? 'text-emerald-400' : 'text-amber-400'}">${isParsed ? '✓ Procesado' : '⚠ Sin procesar'}</span>
                    </div>
                    ${doc.notes ? `<span class="text-[11px] text-zinc-500 italic truncate">${escapeHtml(doc.notes)}</span>` : ''}
                </div>
            </div>
            <button type="button" class="flex-shrink-0 p-2 rounded-lg hover:bg-rose-500/10 text-zinc-600 hover:text-rose-400 transition-colors cursor-pointer" title="Desvincular" onclick="detachDocument('${ANALYSIS_ID}', '${doc.id}', this)">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;
        list.appendChild(div);
        updateAnalyzeButton();
    }

    // ---------------------------------------------------------------
    // Detach document
    // ---------------------------------------------------------------
    async function detachDocument(analysisId, documentId, btn) {
        if (!confirm('¿Desvincular este documento de apoyo del análisis?')) return;

        const row = btn.closest('.supporting-doc-item');
        row.style.opacity = '0.4';

        try {
            const res = await fetch(`${API_BASE}/analyses/${analysisId}/supporting-documents/${documentId}`, {
                method: 'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
            });

            if (!res.ok) {
                row.style.opacity = '1';
                alert('Error al desvincular. Intente nuevamente.');
                return;
            }

            row.remove();
            updateCount(-1);
            updateAnalyzeButton();

            const list = document.getElementById('supporting-docs-list');
            if (list.querySelectorAll('.supporting-doc-item').length === 0) {
                list.innerHTML = `<div id="empty-state" class="text-center py-8 text-zinc-600 text-[13px] italic flex flex-col items-center gap-2">
                    <svg class="w-8 h-8 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Sin documentos de apoyo adjuntos aún.
                </div>`;
            }
        } catch (e) {
            row.style.opacity = '1';
            alert('Error de conexión.');
        }
    }

    // ---------------------------------------------------------------
    // Contextual analysis
    // ---------------------------------------------------------------
    async function triggerContextualAnalysis() {
        const btn = document.getElementById('btn-analyze-context');
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Analizando...';

        try {
            const res = await fetch(`${API_BASE}/analyses/${ANALYSIS_ID}/analyze-with-context`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ analysis_type: 'contextual_summary' }),
            });

            const data = await res.json();

            const resultSection = document.getElementById('contextual-result');
            const resultContent = document.getElementById('contextual-result-content');
            resultSection.classList.remove('hidden');

            if (!res.ok) {
                resultContent.innerHTML = `<span class="text-rose-400">${escapeHtml(data.message ?? 'Error en el análisis.')}</span>`;
            } else {
                const c = data.content ?? {};
                let html = '';
                if (c.resumen_integrado) html += `<p class="font-semibold text-white mb-2">Resumen Integrado</p><p class="mb-4">${escapeHtml(c.resumen_integrado)}</p>`;
                if (c.nivel_confianza)  html += `<p class="text-[11px] text-zinc-500">Nivel de confianza: <strong class="text-zinc-300">${escapeHtml(c.nivel_confianza)}</strong></p>`;
                if (data.skipped_documents?.length) html += `<p class="text-[11px] text-amber-400 mt-2">⚠ Documentos omitidos (sin procesar): ${data.skipped_documents.map(escapeHtml).join(', ')}</p>`;
                if (!html) html = `<pre class="text-[11px] overflow-auto max-h-64">${escapeHtml(JSON.stringify(c, null, 2))}</pre>`;
                resultContent.innerHTML = html;
            }
        } catch (e) {
            const resultSection = document.getElementById('contextual-result');
            const resultContent = document.getElementById('contextual-result-content');
            resultSection.classList.remove('hidden');
            resultContent.innerHTML = '<span class="text-rose-400">Error de conexión. Intente nuevamente.</span>';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/></svg> Analizar con Contexto';
        }
    }

    // ---------------------------------------------------------------
    // UI helpers
    // ---------------------------------------------------------------
    function updateCount(delta) {
        const el = document.getElementById('supporting-docs-count');
        if (!el) return;
        el.textContent = Math.max(0, parseInt(el.textContent) + delta);
    }

    function updateAnalyzeButton() {
        const btn = document.getElementById('btn-analyze-context');
        const count = document.querySelectorAll('.supporting-doc-item').length;
        if (count > 0) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    // Init
    document.addEventListener('DOMContentLoaded', updateAnalyzeButton);
</script>
@endpush

@endsection
