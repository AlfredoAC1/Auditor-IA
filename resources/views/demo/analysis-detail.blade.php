@extends('demo.layout', ['activeSection' => 'analysis'])

@section('title', 'Detalle de Auditoría (Demo) - Auditor-IA')

@section('content')
@php
    $analysis = $document['analysis'];
    $content = $analysis['content'];
    $totalTokens = (int) ($analysis['input_tokens'] ?? 0) + (int) ($analysis['output_tokens'] ?? 0);

    $providerSlug = strtolower((string) ($analysis['llm_provider'] ?? ''));
    [$providerLabel, $providerBadgeClass] = match(true) {
        str_contains($providerSlug, 'cerebras')
            => ['Cerebras AI', 'bg-[#3B2D1A] text-[#F5C87A] border border-[#7A5C2E]'],
        str_contains($providerSlug, 'nvidia')
            => ['NVIDIA NIM', 'bg-[#2D4F3B] text-[#D5E8D4] border border-gunmetal-dark'],
        str_contains($providerSlug, 'qwen')
            => ['Qwen Cloud', 'bg-[#2D2050] text-[#C4B5F4] border border-[#5B4A9E]'],
        default
            => ['Groq Cloud', 'bg-navy-dark text-icy border border-icy/40'],
    };
@endphp

<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <a href="{{ route('demo.dashboard') }}" class="text-icy/70 hover:text-icy transition-colors text-[13px] font-mono font-bold flex items-center gap-1.5 uppercase">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al Dashboard Demo
                </a>
            </div>
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight break-all">{{ $document['original_filename'] }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-1.5 font-mono">
                <span class="px-2.5 py-0.5 text-[11px] font-bold bg-navy-dark text-icy border border-icy/40 shadow-brutal-sm uppercase">
                    {{ $content['tipo_documento'] ?? 'Documento Legal' }}
                </span>
                <span class="text-[12px] text-icy/70">
                    Auditado {{ $document['audited_at_human'] }}
                </span>
                <span class="px-2.5 py-0.5 text-[11px] font-bold {{ $providerBadgeClass }} shadow-brutal-sm uppercase">
                    Motor IA: {{ $providerLabel }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('demo.analysis.clauses', $document['slug']) }}" class="flex items-center gap-2 px-4 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Ver Cláusulas
            </a>
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
                                <div class="flex items-center justify-between gap-4 flex-wrap">
                                    <h3 class="text-[14px] font-serif font-bold text-icy">{{ $r['riesgo'] ?? 'Riesgo indefinido' }}</h3>
                                    @php
                                        $severity = strtolower((string) ($r['severidad'] ?? 'baja'));
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
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-[14px] font-serif font-bold text-icy tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Inferencia y Motor IA
                    </h3>
                    <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold {{ $providerBadgeClass }} shadow-brutal-sm uppercase tracking-wider">
                        {{ $providerLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-[12px] font-mono pt-1 border-t border-gunmetal">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-icy/60 uppercase">Proveedor</span>
                        <span class="text-icy font-bold uppercase">{{ $analysis['llm_provider'] }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-icy/60 uppercase">Modelo</span>
                        <span class="text-icy font-bold truncate" title="{{ $analysis['llm_model'] }}">{{ $analysis['llm_model'] }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-icy/60 uppercase">Respuesta</span>
                        <span class="text-icy font-bold">{{ number_format($analysis['processing_time_ms'] / 1000, 2) }} s</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-icy/60 uppercase">Tokens Demo</span>
                        <span class="text-icy font-bold">{{ number_format($totalTokens, 0, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            <!-- Diagnóstico & Abogado -->
            @if(!empty($content['requiere_abogado']))
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
                                <span class="w-1.5 h-1.5 bg-icy shrink-0"></span>
                                <span>{{ $p }}</span>
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
                                        $priority = strtolower((string) ($rec['prioridad'] ?? 'baja'));
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

</div>
@endsection

