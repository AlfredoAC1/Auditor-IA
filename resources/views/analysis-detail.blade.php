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
                    @php
                        $providerSlug = strtolower($analysis->llm_provider ?? '');
                        [$providerLabel, $providerBadgeClass] = match(true) {
                            str_contains($providerSlug, 'cerebras')
                                => ['Cerebras AI', 'bg-[#3B2D1A] text-[#F5C87A] border border-[#7A5C2E]'],
                            str_contains($providerSlug, 'nvidia')
                                => ['NVIDIA NIM', 'bg-[#2D4F3B] text-[#D5E8D4] border border-gunmetal-dark'],
                            str_contains($providerSlug, 'qwen')
                                => ['Qwen Cloud', 'bg-[#2D2050] text-[#C4B5F4] border border-[#5B4A9E]'],
                            str_contains($providerSlug, 'deepseek')
                                => ['DeepSeek', 'bg-[#1A2D4F] text-[#93C5FD] border border-[#2E4A7A]'],
                            str_contains($providerSlug, 'azure')
                                => ['Azure OpenAI', 'bg-[#1B3A4B] text-[#7DD3FC] border border-[#2C5F7A]'],
                            default
                                => ['Groq Cloud', 'bg-navy-dark text-icy border border-icy/40'],
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 text-[11px] font-bold {{ $providerBadgeClass }} shadow-brutal-sm uppercase">
                        Motor IA: {{ $providerLabel }}
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
        {{-- ANCILLARY / SUPPORTING DOCUMENTS SECTION --}}
        {{-- ================================================================ --}}
        @php
            $isPro = auth()->user()?->hasProAccess() ?? false;
            $contextualAnalysis = \App\Models\DocumentAnalysis::query()
                ->where('document_id', $document->id)
                ->where('analysis_type', \App\Enums\AnalysisType::ContextualSummary)
                ->where('status', \App\Enums\DocumentStatus::Processed)
                ->orderByDesc('created_at')
                ->first();
            $supportingDocs = $analysis ? $analysis->supportingDocuments()->with('document')->get() : collect();
        @endphp

        <section id="supporting-docs-section" class="bg-[#161616]/60 border border-violet-500/30 rounded-2xl p-6 flex flex-col gap-6 shadow-xl">
            <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-zinc-800/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-violet-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h2 class="text-[16px] font-bold text-white tracking-tight">Documentos Anexos y Auditoría Contextual</h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-violet-600/20 text-violet-300 border border-violet-500/30 uppercase tracking-wider">PRO</span>
                        </div>
                        <p class="text-[12px] text-zinc-400">
                            Audita adendas, contratos marco y anexos junto al documento principal sin volver a procesar el archivo original.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($contextualAnalysis)
                        <a
                            href="{{ route('analysis.contextual.show', ['document' => $document->id, 'analysis' => $contextualAnalysis->id]) }}"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-linear-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-[13px] font-bold text-white shadow-lg shadow-violet-500/20 transition-all cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ver Análisis Contextual con Bonus Insights
                        </a>
                    @endif

                    @if($isPro)
                        <button
                            id="btn-attach-doc"
                            type="button"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 border border-violet-500/30 text-[13px] font-bold text-white transition-all cursor-pointer shadow-md hover:border-violet-500/50"
                            onclick="openAttachModal()"
                        >
                            <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Adjuntar Documento Anexo
                        </button>
                    @endif
                </div>
            </div>

            @if(! $isPro)
                {{-- Locked State for Non-PRO Users --}}
                <div class="p-6 rounded-2xl bg-zinc-900/40 border border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-violet-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-[14px] font-bold text-white">Función Exclusiva del Plan PRO</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-violet-600/20 text-violet-300 border border-violet-500/30 uppercase tracking-wider">Bloqueado</span>
                            </div>
                            <p class="text-[12px] text-zinc-400 leading-relaxed max-w-xl">
                                Agrega adendas, contratos marco, garantías o poderes notariales para realizar una auditoría jurídica cruzada con detección de discrepancias, cláusulas modificadas y bonus insights legales.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('landing') }}#precios" class="px-5 py-2.5 rounded-xl bg-linear-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-[13px] font-bold text-white shadow-lg shadow-violet-500/20 transition-all flex items-center gap-2 shrink-0">
                        Actualizar a PRO
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            @else
                {{-- Supporting Documents List --}}
                <div id="supporting-docs-list" class="flex flex-col gap-3">
                    @if($supportingDocs->isEmpty())
                        <div id="empty-state" class="text-center py-8 text-zinc-500 text-[13px] italic flex flex-col items-center gap-2 bg-zinc-900/20 rounded-xl border border-zinc-800/50">
                            <svg class="w-8 h-8 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Sin documentos anexos adjuntos aún. Haz clic en "Adjuntar Documento Anexo" para cargar una adenda o contrato relacionado.
                        </div>
                    @else
                        @foreach($supportingDocs as $pivot)
                            <div class="supporting-doc-item flex items-center justify-between gap-3 p-4 rounded-xl bg-zinc-900/40 border border-zinc-800/80" data-pivot-id="{{ $pivot->id }}" data-document-id="{{ $pivot->document_id }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-violet-600/10 border border-violet-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col gap-0.5 min-w-0">
                                        <span class="text-[13px] font-semibold text-white truncate">{{ $pivot->document?->original_filename ?? 'Documento Anexo' }}</span>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($pivot->relationship_type)
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-violet-400 bg-violet-600/10 border border-violet-500/10 px-1.5 py-0.5 rounded">
                                                    {{ $pivot->relationshipLabel() }}
                                                </span>
                                            @endif
                                            @if(isset($pivot->document?->metadata['parsed_compact_content']))
                                                <span class="text-[10px] font-bold text-emerald-400">✓ OCR Procesado</span>
                                            @else
                                                <span class="text-[10px] font-bold text-amber-400">⏳ En procesamiento</span>
                                            @endif
                                        </div>
                                        @if($pivot->notes)
                                            <span class="text-[11px] text-zinc-500 italic truncate">{{ $pivot->notes }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if($contextualAnalysis)
                                        <a href="{{ route('analysis.contextual.show', ['document' => $document->id, 'analysis' => $contextualAnalysis->id]) }}" class="px-3 py-1.5 rounded-lg bg-violet-600/10 hover:bg-violet-600/20 text-violet-300 text-[11px] font-bold border border-violet-500/20 transition-colors">
                                            Ver en Análisis Contextual
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif
        </section>

        {{-- ================================================================ --}}
        {{-- ATTACH ANCILLARY DOCUMENT MODAL (Drag & Drop) --}}
        {{-- ================================================================ --}}
        @if($isPro)
            <div id="attach-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75 backdrop-blur-sm p-4">
                <div class="w-full max-w-xl bg-[#121212] border border-zinc-800 rounded-2xl p-6 flex flex-col gap-5 shadow-2xl">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h3 class="text-[16px] font-bold text-white">Adjuntar Documento Anexo</h3>
                        </div>
                        <button type="button" onclick="closeAttachModal()" class="text-zinc-500 hover:text-white transition-colors cursor-pointer p-1">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form id="attach-form" onsubmit="submitAttachForm(event)" enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-col gap-4">
                            {{-- Drag and Drop Upload Zone --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[13px] font-semibold text-zinc-300">Archivo Anexo <span class="text-rose-400">*</span></label>
                                
                                <div
                                    id="dropzone-container"
                                    class="border-2 border-dashed border-zinc-700 hover:border-violet-500 rounded-2xl p-6 text-center transition-all bg-zinc-900/50 cursor-pointer flex flex-col items-center justify-center gap-3 group"
                                    onclick="document.getElementById('ancillary-file-input').click()"
                                    ondragover="handleDragOver(event)"
                                    ondragleave="handleDragLeave(event)"
                                    ondrop="handleDrop(event)"
                                >
                                    <input
                                        type="file"
                                        id="ancillary-file-input"
                                        name="file"
                                        class="hidden"
                                        accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                        onchange="handleFileSelected(this)"
                                        required
                                    >

                                    <div class="w-12 h-12 rounded-xl bg-violet-600/10 group-hover:bg-violet-600/20 border border-violet-500/20 flex items-center justify-center text-violet-400 transition-colors">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>

                                    <div id="dropzone-prompt" class="flex flex-col gap-1">
                                        <span class="text-[13px] font-bold text-white group-hover:text-violet-300 transition-colors">Arrastra tu documento anexo aquí o haz clic para explorar</span>
                                        <span class="text-[11px] text-zinc-500 font-mono">Formatos admitidos: PDF o Word (.docx) — Máx. 50 MB</span>
                                    </div>

                                    <div id="file-info" class="hidden items-center gap-2 px-3 py-1.5 rounded-lg bg-violet-600/20 border border-violet-500/30 text-violet-300 text-[12px] font-semibold">
                                        <span id="file-name" class="truncate max-w-xs">archivo.pdf</span>
                                        <span id="file-size" class="text-zinc-400 font-mono text-[11px]">(0 MB)</span>
                                        <button type="button" onclick="clearSelectedFile(event)" class="text-rose-400 hover:text-rose-300 ml-1 p-0.5">✕</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Relationship Type --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="attach-relationship" class="text-[13px] font-semibold text-zinc-300">Tipo de Relación Contractual</label>
                                <select id="attach-relationship" name="relationship_type" class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] text-white focus:border-violet-500 focus:outline-none transition-colors">
                                    <option value="amendment">Adenda / Modificación Contractual</option>
                                    <option value="framework">Contrato Marco / Acuerdo Vinculante</option>
                                    <option value="guarantee">Garantía / Fianza / Pagaré</option>
                                    <option value="power_of_attorney">Poder Notarial / Mandato</option>
                                    <option value="technical_annex">Anexo Técnico / Operativo</option>
                                    <option value="settlement">Finiquito / Acta de Término</option>
                                    <option value="complementary">Documento Complementario General</option>
                                </select>
                            </div>

                            {{-- Notes --}}
                            <div class="flex flex-col gap-1.5">
                                <label for="attach-notes" class="text-[13px] font-semibold text-zinc-300">Notas u Observaciones <span class="text-zinc-500 font-normal">(opcional)</span></label>
                                <textarea
                                    id="attach-notes"
                                    name="notes"
                                    rows="2"
                                    maxlength="1000"
                                    placeholder="Indica aspectos clave a auditar en conjunto con este documento..."
                                    class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-3 py-2.5 text-[13px] text-white placeholder-zinc-600 focus:border-violet-500 focus:outline-none transition-colors resize-none"
                                ></textarea>
                            </div>

                            {{-- Progress Box --}}
                            <div id="attach-progress" class="hidden flex-col gap-2.5 p-3.5 bg-violet-600/10 border border-violet-500/20 rounded-xl">
                                <div class="flex items-center justify-between text-[12px] font-semibold text-violet-300">
                                    <span id="attach-progress-text" class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin text-violet-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                        Subiendo documento anexo...
                                    </span>
                                    <span id="attach-progress-pct" class="font-mono text-[11px] text-violet-400">Procesando</span>
                                </div>
                                <div class="w-full bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                    <div id="attach-progress-bar" class="bg-linear-to-r from-violet-600 to-fuchsia-500 h-1.5 rounded-full w-1/3 animate-pulse"></div>
                                </div>
                            </div>

                            {{-- Error alert --}}
                            <div id="attach-error" class="hidden text-[12px] text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-xl px-3.5 py-2.5"></div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" onclick="closeAttachModal()" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-[13px] font-semibold text-zinc-300 transition-colors cursor-pointer">
                                    Cancelar
                                </button>
                                <button type="submit" id="attach-submit-btn" class="flex items-center gap-2 px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white transition-all cursor-pointer shadow-lg shadow-violet-500/25">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    Subir y Auditar Anexo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    @endif

</div>

@push('scripts')
<script>
    const ANALYSIS_ID = '{{ $analysis?->id }}';
    const UPLOAD_URL  = '{{ route("analysis.ancillary.upload", $analysis ?? "0") }}';

    function openAttachModal() {
        const modal = document.getElementById('attach-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('attach-error')?.classList.add('hidden');
    }

    function closeAttachModal() {
        const modal = document.getElementById('attach-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Drag & Drop handlers
    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        document.getElementById('dropzone-container')?.classList.add('border-violet-500', 'bg-violet-950/20');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        document.getElementById('dropzone-container')?.classList.remove('border-violet-500', 'bg-violet-950/20');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = document.getElementById('dropzone-container');
        dropzone?.classList.remove('border-violet-500', 'bg-violet-950/20');

        if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
            const input = document.getElementById('ancillary-file-input');
            input.files = e.dataTransfer.files;
            handleFileSelected(input);
        }
    }

    function handleFileSelected(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);

        document.getElementById('dropzone-prompt')?.classList.add('hidden');
        const fileInfo = document.getElementById('file-info');
        if (fileInfo) {
            fileInfo.classList.remove('hidden');
            fileInfo.classList.add('flex');
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = `(${sizeMb} MB)`;
        }
    }

    function clearSelectedFile(e) {
        e.stopPropagation();
        const input = document.getElementById('ancillary-file-input');
        if (input) input.value = '';
        const fileInfo = document.getElementById('file-info');
        fileInfo?.classList.add('hidden');
        fileInfo?.classList.remove('flex');
        document.getElementById('dropzone-prompt')?.classList.remove('hidden');
    }

    // Submit upload form & poll status
    async function submitAttachForm(event) {
        event.preventDefault();
        const btn = document.getElementById('attach-submit-btn');
        const errEl = document.getElementById('attach-error');
        const progressBox = document.getElementById('attach-progress');
        const progressText = document.getElementById('attach-progress-text');
        const form = document.getElementById('attach-form');

        btn.disabled = true;
        errEl.classList.add('hidden');
        progressBox.classList.remove('hidden');
        progressBox.classList.add('flex');

        const formData = new FormData(form);

        try {
            const res = await fetch(UPLOAD_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: formData,
            });

            const data = await res.json();

            if (!res.ok) {
                progressBox.classList.add('hidden');
                progressBox.classList.remove('flex');
                errEl.textContent = data.message ?? 'Error al subir el documento anexo.';
                errEl.classList.remove('hidden');
                btn.disabled = false;
                return;
            }

            // Polling status
            progressText.innerHTML = '<svg class="w-4 h-4 animate-spin text-violet-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Extrayendo texto y procesando auditoría contextual...';
            pollStatus(data.status_url);
        } catch (e) {
            progressBox.classList.add('hidden');
            progressBox.classList.remove('flex');
            errEl.textContent = 'Error de conexión. Intente nuevamente.';
            errEl.classList.remove('hidden');
            btn.disabled = false;
        }
    }

    function pollStatus(statusUrl) {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.status === 'processed' && data.analysis_url) {
                    clearInterval(interval);
                    document.getElementById('attach-progress-text').textContent = '¡Auditoría completada! Redirigiendo...';
                    window.location.href = data.analysis_url;
                } else if (data.status === 'failed') {
                    clearInterval(interval);
                    document.getElementById('attach-progress').classList.add('hidden');
                    const errEl = document.getElementById('attach-error');
                    errEl.textContent = 'El procesamiento del anexo falló: ' + (data.error ?? 'Error desconocido.');
                    errEl.classList.remove('hidden');
                    document.getElementById('attach-submit-btn').disabled = false;
                }
            } catch (e) {
                // Continue polling
            }
        }, 2500);
    }
</script>
@endpush

@endsection

