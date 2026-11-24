@extends('demo.layout', ['activeSection' => 'analysis'])

@section('title', 'Visor de Cláusulas (Demo) - Auditor-IA')

@section('content')
@php
    $content = $document['analysis']['content'];
    $clauses = $content['clausulas_principales'] ?? [];
@endphp

<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-5xl mx-auto w-full">

    <!-- Header -->
    <header class="flex flex-col gap-2 pb-6 border-b-2 border-[#35393C]">
        <div class="flex items-center gap-2">
            <a href="{{ route('demo.analysis.show', $document['slug']) }}" class="text-[#A4D8FF]/70 hover:text-[#A4D8FF] transition-colors text-[13px] font-mono font-bold flex items-center gap-1.5 uppercase">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Reporte
            </a>
        </div>
        <h1 class="text-2xl md:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight">Desglose de Cláusulas</h1>
        <p class="text-[13px] font-mono text-[#A4D8FF]/70">{{ $document['original_filename'] }} · Analizado detalladamente para simplificar términos legales.</p>
    </header>

    <!-- Main Clauses List -->
    <div class="flex flex-col gap-6">
        @if(empty($clauses))
            <div class="bg-[#222629] border-2 border-[#35393C] p-10 text-center text-[13px] font-mono text-[#A4D8FF]/60 italic shadow-brutal">
                No se encontraron cláusulas desglosadas en el análisis.
            </div>
        @else
            @foreach($clauses as $c)
                <div class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-4 shadow-brutal transition-all hover:border-[#A4D8FF]/50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @if(!empty($c['numero']))
                                <span class="w-8 h-8 bg-[#0F1E36] border border-[#A4D8FF]/40 flex items-center justify-center text-[13px] font-mono font-bold text-[#A4D8FF] shrink-0">
                                    {{ $c['numero'] }}
                                </span>
                            @endif
                            <h2 class="text-lg font-serif font-bold text-[#A4D8FF] tracking-tight">{{ $c['titulo'] ?? 'Cláusula' }}</h2>
                        </div>
                    </div>

                    <!-- Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <!-- Explicación Simple -->
                        <div class="p-4.5 bg-[#15181B] border-2 border-[#35393C] flex flex-col gap-2 shadow-brutal-sm">
                            <h3 class="text-[12.5px] font-serif font-bold text-[#A4D8FF] flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Explicación Sencilla
                            </h3>
                            <p class="text-[13px] font-sans text-[#A4D8FF]/85 leading-relaxed font-normal">
                                {{ $c['explicacion'] ?? 'Sin explicación provista.' }}
                            </p>
                        </div>

                        <!-- Implicación Legal -->
                        <div class="p-4.5 bg-[#15181B] border-2 border-[#35393C] flex flex-col gap-2 shadow-brutal-sm">
                            <h3 class="text-[12.5px] font-serif font-bold text-[#A4D8FF] flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Implicación Legal
                            </h3>
                            <p class="text-[13px] font-sans text-[#A4D8FF]/85 leading-relaxed font-normal">
                                {{ $c['implicacion_legal'] ?? 'Sin implicaciones detectadas.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</div>
@endsection

