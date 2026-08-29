@extends('layouts.app')

@section('title', 'Detalle de Auditoría - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    @if(!$analysis)
        <!-- Empty / Pending State -->
        <div class="flex-1 flex flex-col items-center justify-center text-center gap-5 max-w-lg mx-auto w-full min-h-[75vh]">
            <!-- Icon -->
            <div class="w-16 h-16 bg-[#4B262F] border-2 border-[#151311] flex items-center justify-center text-[#EED3BA] shadow-brutal">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h1 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight">Análisis no disponible</h1>
                @if($document->status->value === 'processing')
                    <p class="text-[13px] font-mono text-[#EED3BA]/70 leading-relaxed">
                        Este documento se está analizando actualmente. Por favor, recarga la página en unos segundos.
                    </p>
                @elseif($document->status->value === 'failed')
                    <p class="text-[13px] font-mono text-[#F8CECC] leading-relaxed">
                        El procesamiento de este documento falló: {{ $document->metadata['last_error'] ?? 'Error desconocido' }}
                    </p>
                @else
                    <p class="text-[13px] font-mono text-[#EED3BA]/60 leading-relaxed">
                        No hay análisis completados para este documento. Sube un archivo en tu Dashboard para generar el reporte.
                    </p>
                @endif
            </div>
            <a href="{{ route('dashboard') }}" class="mt-2 px-5 py-2.5 bg-[#4B262F] hover:bg-[#5C2F3A] border border-[#EED3BA]/40 text-[13px] font-mono font-bold text-[#EED3BA] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                Ir al Dashboard
            </a>
        </div>
    @else
        @php
            $content = $analysis->content;
        @endphp

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b-2 border-[#4B262F]/40">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="text-[#EED3BA]/70 hover:text-[#EED3BA] transition-colors text-[13px] font-mono font-bold flex items-center gap-1.5 uppercase">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-[#EED3BA] tracking-tight">{{ $document->original_filename }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5 font-mono">
                    <span class="px-2.5 py-0.5 text-[11px] font-bold bg-[#4B262F] text-[#EED3BA] border border-[#EED3BA]/40 shadow-brutal-sm uppercase">
                        {{ $content['tipo_documento'] ?? 'Documento Legal' }}
                    </span>
                    <span class="text-[12px] text-[#EED3BA]/70">
                        Auditado {{ $analysis->created_at->diffForHumans() }}
                    </span>
                    <span class="px-2.5 py-0.5 text-[11px] font-bold {{ str_contains(strtolower($analysis->llm_provider), 'nvidia') ? 'bg-[#2D4F3B] text-[#D5E8D4] border border-[#151311]' : 'bg-[#4B262F] text-[#EED3BA] border border-[#EED3BA]/40' }} shadow-brutal-sm uppercase">
                        Motor IA: {{ str_contains(strtolower($analysis->llm_provider), 'nvidia') ? 'NVIDIA NIM' : 'Groq Cloud' }}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('analysis.clauses', $document->id) }}" class="flex items-center gap-2 px-4 py-2.5 bg-[#4B262F] hover:bg-[#5C2F3A] border border-[#EED3BA]/40 text-[13px] font-mono font-bold text-[#EED3BA] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                    <svg class="w-4.5 h-4.5 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Ver Cláusulas
                </a>

                <form action="{{ route('dashboard.documents.destroy', $document->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este documento y su análisis en cascada? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-[#4B262F] hover:bg-[#5C2F3A] border border-[#151311] text-[13px] font-mono font-bold text-[#F8CECC] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
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
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-6 md:p-7 flex flex-col gap-4 shadow-brutal">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Resumen General
                    </h2>
                    <p class="text-[13.5px] font-sans text-[#EED3BA]/90 leading-relaxed font-normal">
                        {{ $content['resumen_general'] ?? 'No se generó resumen.' }}
                    </p>
                </section>

                <!-- Riesgos Identificados -->
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-6 md:p-7 flex flex-col gap-5 shadow-brutal">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#F8CECC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Riesgos Detectados ({{ is_array($content['riesgos_identificados'] ?? null) ? count($content['riesgos_identificados']) : 0 }})
                    </h2>

                    @if(empty($content['riesgos_identificados']))
                        <div class="text-center py-6 text-[#EED3BA]/60 font-mono text-[13px] italic">
                            No se detectaron riesgos de severidad en el documento.
                        </div>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($content['riesgos_identificados'] as $r)
                                <div class="p-4 bg-[#151311] border-2 border-[#4B262F] flex flex-col gap-2 shadow-brutal-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <h3 class="text-[14px] font-serif font-bold text-[#EED3BA]">{{ $r['riesgo'] ?? 'Riesgo indefinido' }}</h3>
                                        @php
                                            $severity = strtolower($r['severidad'] ?? 'baja');
                                            $badgeClass = match($severity) {
                                                'alta' => 'bg-[#4B262F] text-[#F8CECC] border-[#151311]',
                                                'media' => 'bg-[#B87326] text-[#FFE6CC] border-[#151311]',
                                                default => 'bg-[#151311] text-[#EED3BA]/70 border-[#4B262F]'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider border shadow-brutal-sm {{ $badgeClass }}">
                                            Severidad {{ $severity }}
                                        </span>
                                    </div>
                                    <p class="text-[12.5px] font-sans text-[#EED3BA]/80 leading-relaxed">{{ $r['explicacion'] ?? '' }}</p>
                                    @if(!empty($r['referencia_legal']))
                                        <div class="mt-1 flex items-center gap-1.5 text-[11px] font-mono text-[#EED3BA]/70 bg-[#2A171B] p-2 border border-[#4B262F]">
                                            <svg class="w-3.5 h-3.5 text-[#EED3BA] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-6 md:p-7 flex flex-col gap-5 shadow-brutal">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        Cláusulas Clave Analizadas
                    </h2>

                    @if(empty($content['clausulas_principales']))
                        <div class="text-center py-6 text-[#EED3BA]/60 font-mono text-[13px] italic">
                            No se desglosaron cláusulas.
                        </div>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($content['clausulas_principales'] as $c)
                                <div class="p-4 bg-[#151311] border-2 border-[#4B262F] flex flex-col gap-2 shadow-brutal-sm">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($c['numero']))
                                            <span class="w-6 h-6 bg-[#4B262F] border border-[#EED3BA]/40 flex items-center justify-center text-[11px] font-mono font-bold text-[#EED3BA]">
                                                {{ $c['numero'] }}
                                            </span>
                                        @endif
                                        <h3 class="text-[13.5px] font-serif font-bold text-[#EED3BA]">{{ $c['titulo'] ?? 'Cláusula' }}</h3>
                                    </div>
                                    <p class="text-[12.5px] font-sans text-[#EED3BA]/85 leading-relaxed">{{ $c['explicacion'] ?? '' }}</p>
                                    @if(!empty($c['implicacion_legal']))
                                        <p class="text-[11.5px] font-mono text-[#EED3BA]/70 italic mt-0.5"><strong class="text-[#EED3BA] not-italic font-bold">Impacto Legal:</strong> {{ $c['implicacion_legal'] }}</p>
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
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-5 flex flex-col gap-3.5 shadow-brutal">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                            <svg class="w-4.5 h-4.5 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Inferencia y Motor IA
                        </h3>
                        @if(str_contains(strtolower($analysis->llm_provider), 'nvidia'))
                            <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold bg-[#2D4F3B] text-[#D5E8D4] border border-[#151311] shadow-brutal-sm uppercase tracking-wider">
                                NVIDIA NIM
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold bg-[#4B262F] text-[#EED3BA] border border-[#EED3BA]/40 shadow-brutal-sm uppercase tracking-wider">
                                Groq Cloud
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-[12px] font-mono pt-1 border-t border-[#4B262F]">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[#EED3BA]/60 uppercase">Proveedor</span>
                            <span class="text-[#EED3BA] font-bold uppercase">{{ $analysis->llm_provider }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[#EED3BA]/60 uppercase">Modelo</span>
                            <span class="text-[#EED3BA] font-bold truncate" title="{{ $analysis->llm_model }}">{{ $analysis->llm_model }}</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[#EED3BA]/60 uppercase">Respuesta</span>
                            <span class="text-[#EED3BA] font-bold">{{ number_format($analysis->processing_time_ms / 1000, 2) }} s</span>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[#EED3BA]/60 uppercase">Tokens Consumidos</span>
                            <span class="text-[#EED3BA] font-bold">{{ number_format($analysis->totalTokens(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </section>
                
                <!-- Diagnóstico & Abogado -->
                @if(isset($content['requiere_abogado']) && $content['requiere_abogado'])
                    <div class="bg-[#4B262F] border-2 border-[#151311] p-5 flex flex-col gap-3 shadow-brutal">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#151311] border border-[#EED3BA]/40 flex items-center justify-center text-[#EED3BA]">
                                <svg class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-[14px] font-serif font-bold text-[#EED3BA]">Recomendación Legal</h3>
                        </div>
                        <p class="text-[12px] font-mono text-[#EED3BA]/90 leading-relaxed">
                            Este documento contiene cláusulas de riesgo o compromisos significativos según la legislación chilena. Se recomienda encarecidamente consultar a un abogado calificado antes de firmar.
                        </p>
                    </div>
                @endif

                <!-- Partes Involucradas -->
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-5 flex flex-col gap-3.5 shadow-brutal">
                    <h3 class="text-[14px] font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Partes Identificadas
                    </h3>
                    @if(empty($content['partes_involucradas']))
                        <span class="text-[12px] font-mono text-[#EED3BA]/60 italic">No identificadas</span>
                    @else
                        <ul class="flex flex-col gap-2 font-mono">
                            @foreach($content['partes_involucradas'] as $p)
                                <li class="text-[12.5px] text-[#EED3BA] flex items-center gap-2 px-3 py-2 bg-[#151311] border border-[#4B262F]">
                                    <span class="w-1.5 h-1.5 bg-[#EED3BA]"></span>
                                    {{ $p }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <!-- Recomendaciones Proactivas -->
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-5 flex flex-col gap-4 shadow-brutal">
                    <h3 class="text-[14px] font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Acciones Sugeridas
                    </h3>
                    @if(empty($content['recomendaciones']))
                        <span class="text-[12px] font-mono text-[#EED3BA]/60 italic">Sin recomendaciones</span>
                    @else
                        <div class="flex flex-col gap-3.5">
                            @foreach($content['recomendaciones'] as $rec)
                                <div class="flex flex-col gap-1.5 p-3 bg-[#151311] border border-[#4B262F]">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="text-[12.5px] font-serif font-bold text-[#EED3BA]">{{ $rec['recomendacion'] ?? 'Recomendación' }}</h4>
                                        @php
                                            $priority = strtolower($rec['prioridad'] ?? 'baja');
                                            $prioClass = match($priority) {
                                                'alta' => 'text-[#F8CECC]',
                                                'media' => 'text-[#FFE6CC]',
                                                default => 'text-[#EED3BA]/60'
                                            };
                                        @endphp
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider {{ $prioClass }}">
                                            {{ $priority }}
                                        </span>
                                    </div>
                                    @if(!empty($rec['justificacion']))
                                        <p class="text-[11.5px] font-sans text-[#EED3BA]/80 leading-relaxed">{{ $rec['justificacion'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Marco Legal Chileno Aplicable -->
                <section class="bg-[#2A171B] border-2 border-[#4B262F] p-5 flex flex-col gap-3.5 shadow-brutal">
                    <h3 class="text-[14px] font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                        </svg>
                        Marco Legal Aplicable
                    </h3>
                    @if(empty($content['leyes_aplicables']))
                        <span class="text-[12px] font-mono text-[#EED3BA]/60 italic">No declaradas</span>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($content['leyes_aplicables'] as $ley)
                                <span class="px-2 py-1 bg-[#151311] border border-[#4B262F] text-[11px] font-mono text-[#EED3BA]">
                                    {{ $ley }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    @endif

</div>
@endsection

