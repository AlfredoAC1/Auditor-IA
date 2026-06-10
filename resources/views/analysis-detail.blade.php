@extends('layouts.app')

@section('title', 'Detalle de Auditoría - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    @if(!$analysis)
        <!-- Empty / Pending State -->
        <div class="flex-1 flex flex-col items-center justify-center text-center gap-5 max-w-lg mx-auto w-full min-h-[75vh]">
            <!-- Icon -->
            <div class="w-16 h-16 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-550 shadow-inner">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h1 class="text-xl font-bold text-white tracking-tight">Análisis no disponible</h1>
                @if($document->status->value === 'processing')
                    <p class="text-[13px] text-zinc-400 leading-relaxed">
                        Este documento se está analizando actualmente. Por favor, recarga la página en unos segundos.
                    </p>
                @elseif($document->status->value === 'failed')
                    <p class="text-[13px] text-rose-400 leading-relaxed">
                        El procesamiento de este documento falló: {{ $document->metadata['last_error'] ?? 'Error desconocido' }}
                    </p>
                @else
                    <p class="text-[13px] text-zinc-500 leading-relaxed">
                        No hay análisis completados para este documento. Sube un archivo en tu Dashboard para generar el reporte.
                    </p>
                @endif
            </div>
            <a href="{{ route('dashboard') }}" class="mt-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white shadow-lg shadow-violet-500/10 hover:shadow-violet-500/20 transition-all duration-200">
                Ir al Dashboard
            </a>
        </div>
    @else
        @php
            $content = $analysis->content;
        @endphp

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-zinc-800/60">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="text-zinc-500 hover:text-white transition-colors text-[13px] font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">{{ $document->original_filename }}</h1>
                <div class="flex flex-wrap items-center gap-3 mt-1.5">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-violet-600/10 text-violet-400 border border-violet-500/10">
                        {{ $content['tipo_documento'] ?? 'Documento Legal' }}
                    </span>
                    <span class="text-[12px] text-zinc-500">
                        Auditado {{ $analysis->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('analysis.clauses', $document->id) }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-850 hover:border-zinc-750 text-[13px] font-bold text-white transition-all">
                    <svg class="w-4.5 h-4.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                <section class="bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-6 md:p-7 flex flex-col gap-4">
                    <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Resumen General
                    </h2>
                    <p class="text-[13.5px] text-zinc-300 leading-relaxed font-normal">
                        {{ $content['resumen_general'] ?? 'No se generó resumen.' }}
                    </p>
                </section>

                <!-- Riesgos Identificados -->
                <section class="bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-6 md:p-7 flex flex-col gap-5">
                    <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Riesgos Detectados ({{ is_array($content['riesgos_identificados'] ?? null) ? count($content['riesgos_identificados']) : 0 }})
                    </h2>

                    @if(empty($content['riesgos_identificados']))
                        <div class="text-center py-6 text-zinc-550 text-[13px] italic">
                            No se detectaron riesgos de severidad en el documento.
                        </div>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($content['riesgos_identificados'] as $r)
                                <div class="p-4 rounded-xl bg-zinc-900/40 border border-zinc-850 flex flex-col gap-2">
                                    <div class="flex items-center justify-between gap-4">
                                        <h3 class="text-[14px] font-bold text-white">{{ $r['riesgo'] ?? 'Riesgo indefinido' }}</h3>
                                        @php
                                            $severity = strtolower($r['severidad'] ?? 'baja');
                                            $badgeClass = match($severity) {
                                                'alta' => 'bg-rose-550/10 text-rose-400 border-rose-500/20',
                                                'media' => 'bg-amber-550/10 text-amber-400 border-amber-500/20',
                                                default => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                            Severidad {{ $severity }}
                                        </span>
                                    </div>
                                    <p class="text-[12.5px] text-zinc-400 leading-relaxed">{{ $r['explicacion'] ?? '' }}</p>
                                    @if(!empty($r['referencia_legal']))
                                        <div class="mt-1 flex items-center gap-1.5 text-[11px] text-zinc-500 font-semibold bg-zinc-900/50 p-2 rounded-lg border border-zinc-850/50">
                                            <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                <section class="bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-6 md:p-7 flex flex-col gap-5">
                    <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        Cláusulas Clave Analizadas
                    </h2>

                    @if(empty($content['clausulas_principales']))
                        <div class="text-center py-6 text-zinc-550 text-[13px] italic">
                            No se desglosaron cláusulas.
                        </div>
                    @else
                        <div class="flex flex-col gap-4">
                            @foreach($content['clausulas_principales'] as $c)
                                <div class="p-4 rounded-xl bg-zinc-900/20 border border-zinc-850/60 flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($c['numero']))
                                            <span class="w-6 h-6 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-[11px] font-bold text-zinc-400">
                                                {{ $c['numero'] }}
                                            </span>
                                        @endif
                                        <h3 class="text-[13.5px] font-bold text-white">{{ $c['titulo'] ?? 'Cláusula' }}</h3>
                                    </div>
                                    <p class="text-[12.5px] text-zinc-350 leading-relaxed">{{ $c['explicacion'] ?? '' }}</p>
                                    @if(!empty($c['implicacion_legal']))
                                        <p class="text-[11.5px] text-zinc-500 italic mt-0.5"><strong class="text-zinc-400 not-italic font-semibold">Impacto Legal:</strong> {{ $c['implicacion_legal'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <!-- Right Column: Metadata, Parties, Recommendations -->
            <div class="flex flex-col gap-8">
                
                <!-- Diagnóstico & Abogado -->
                @if(isset($content['requiere_abogado']) && $content['requiere_abogado'])
                    <div class="bg-violet-950/20 border border-violet-500/20 rounded-2xl p-5 flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-violet-400 shadow-inner">
                                <svg class="w-5.5 h-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-[14px] font-bold text-white">Recomendación Legal</h3>
                        </div>
                        <p class="text-[12px] text-zinc-300 leading-relaxed">
                            Este documento contiene cláusulas de riesgo o compromisos significativos según la legislación chilena. Se recomienda encarecidamente consultar a un abogado calificado antes de firmar.
                        </p>
                    </div>
                @endif

                <!-- Partes Involucradas -->
                <section class="bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3.5">
                    <h3 class="text-[14px] font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Partes Identificadas
                    </h3>
                    @if(empty($content['partes_involucradas']))
                        <span class="text-[12px] text-zinc-550 italic">No identificadas</span>
                    @else
                        <ul class="flex flex-col gap-2">
                            @foreach($content['partes_involucradas'] as $p)
                                <li class="text-[12.5px] text-zinc-300 flex items-center gap-2 px-3 py-2 rounded-lg bg-zinc-900/30 border border-zinc-850/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>
                                    {{ $p }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <!-- Recomendaciones Proactivas -->
                <section class="bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-4">
                    <h3 class="text-[14px] font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Acciones Sugeridas
                    </h3>
                    @if(empty($content['recomendaciones']))
                        <span class="text-[12px] text-zinc-550 italic">Sin recomendaciones</span>
                    @else
                        <div class="flex flex-col gap-3.5">
                            @foreach($content['recomendaciones'] as $rec)
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="text-[12.5px] font-bold text-white">{{ $rec['recomendacion'] ?? 'Recomendación' }}</h4>
                                        @php
                                            $priority = strtolower($rec['prioridad'] ?? 'baja');
                                            $prioClass = match($priority) {
                                                'alta' => 'text-rose-400',
                                                'media' => 'text-amber-400',
                                                default => 'text-zinc-500'
                                            };
                                        @endphp
                                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $prioClass }}">
                                            {{ $priority }}
                                        </span>
                                    </div>
                                    @if(!empty($rec['justificacion']))
                                        <p class="text-[11.5px] text-zinc-400 leading-relaxed">{{ $rec['justificacion'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- Marco Legal Chileno Aplicable -->
                <section class="bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-5 flex flex-col gap-3.5">
                    <h3 class="text-[14px] font-bold text-white tracking-tight flex items-center gap-2">
                        <svg class="w-4.5 h-4.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253" />
                        </svg>
                        Marco Legal Aplicable
                    </h3>
                    @if(empty($content['leyes_aplicables']))
                        <span class="text-[12px] text-zinc-550 italic">No declaradas</span>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($content['leyes_aplicables'] as $ley)
                                <span class="px-2 py-1 rounded bg-zinc-900 border border-zinc-800 text-[11px] font-medium text-zinc-300">
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
