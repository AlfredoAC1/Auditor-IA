@extends('layouts.app')

@section('title', 'Visor de Cláusulas - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    @if(!$analysis)
        <!-- Empty / Pending State -->
        <div class="flex-1 flex flex-col items-center justify-center text-center gap-5 max-w-lg mx-auto w-full min-h-[75vh]">
            <div class="w-16 h-16 bg-[#4B262F] border-2 border-[#151311] flex items-center justify-center text-[#EED3BA] shadow-brutal">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex flex-col gap-2">
                <h1 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight">Análisis no disponible</h1>
                <p class="text-[13px] font-mono text-[#EED3BA]/70 leading-relaxed">
                    No hay cláusulas disponibles para este documento. Sube un archivo en tu Dashboard para generar el reporte.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="mt-2 px-5 py-2.5 bg-[#4B262F] hover:bg-[#5C2F3A] border border-[#EED3BA]/40 text-[13px] font-mono font-bold text-[#EED3BA] shadow-brutal uppercase tracking-wider">
                Ir al Dashboard
            </a>
        </div>
    @else
        @php
            $content = $analysis->content;
            $clauses = $content['clausulas_principales'] ?? [];
        @endphp

        <!-- Header -->
        <header class="flex flex-col gap-2 pb-6 border-b-2 border-[#4B262F]/40">
            <div class="flex items-center gap-2">
                <a href="{{ route('analysis.show', $document->id) }}" class="text-[#EED3BA]/70 hover:text-[#EED3BA] transition-colors text-[13px] font-mono font-bold flex items-center gap-1.5 uppercase">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al Reporte
                </a>
            </div>
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-[#EED3BA] tracking-tight">Desglose de Cláusulas</h1>
            <p class="text-[13px] font-mono text-[#EED3BA]/70">Analizado detalladamente con inteligencia artificial para simplificar términos legales.</p>
        </header>

        <!-- Main Clauses List -->
        <div class="flex flex-col gap-6">
            @if(empty($clauses))
                <div class="bg-[#2A171B] border-2 border-[#4B262F] p-10 text-center text-[13px] font-mono text-[#EED3BA]/60 italic shadow-brutal">
                    No se encontraron cláusulas desglosadas en el análisis.
                </div>
            @else
                @foreach($clauses as $c)
                    <div class="bg-[#2A171B] border-2 border-[#4B262F] p-6 flex flex-col gap-4 shadow-brutal transition-all hover:border-[#EED3BA]/50">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                @if(!empty($c['numero']))
                                    <span class="w-8 h-8 bg-[#4B262F] border border-[#EED3BA]/40 flex items-center justify-center text-[13px] font-mono font-bold text-[#EED3BA]">
                                        {{ $c['numero'] }}
                                    </span>
                                @endif
                                <h2 class="text-lg font-serif font-bold text-[#EED3BA] tracking-tight">{{ $c['titulo'] ?? 'Cláusula' }}</h2>
                            </div>
                        </div>

                        <!-- Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                            <!-- Explicación Simple -->
                            <div class="p-4.5 bg-[#151311] border-2 border-[#4B262F] flex flex-col gap-2 shadow-brutal-sm">
                                <h3 class="text-[12.5px] font-serif font-bold text-[#EED3BA] flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Explicación Sencilla
                                </h3>
                                <p class="text-[13px] font-sans text-[#EED3BA]/85 leading-relaxed font-normal">
                                    {{ $c['explicacion'] ?? 'Sin explicación provista.' }}
                                </p>
                            </div>

                            <!-- Implicación Legal -->
                            <div class="p-4.5 bg-[#151311] border-2 border-[#4B262F] flex flex-col gap-2 shadow-brutal-sm">
                                <h3 class="text-[12.5px] font-serif font-bold text-[#EED3BA] flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#EED3BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Implicación Legal
                                </h3>
                                <p class="text-[13px] font-sans text-[#EED3BA]/85 leading-relaxed font-normal">
                                    {{ $c['implicacion_legal'] ?? 'Sin implicaciones detectadas.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif

</div>
@endsection

