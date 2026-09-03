@extends('layouts.app')

@section('title', 'Auditoría de Análisis - Admin Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-icy/60 hover:text-icy text-[13px] font-mono">← Panel</a>
                <span class="text-icy/40">/</span>
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Auditoría de Análisis</h1>
            </div>
            <p class="text-[13px] font-mono text-icy/70">Registro y trazabilidad técnica de todas las ejecuciones IA del sistema</p>
        </div>
    </header>

    {{-- Filters Card --}}
    <form method="GET" action="{{ route('admin.analyses.index') }}" class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 w-full flex-1">
            {{-- Search input --}}
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Buscar por documento o usuario..." 
                class="bg-navy-dark border border-gunmetal text-icy text-[12.5px] font-mono px-3 py-2 placeholder-icy/40 focus:border-icy focus:outline-none"
            >

            {{-- Type filter --}}
            <select name="type" class="bg-navy-dark border border-gunmetal text-icy text-[12.5px] font-mono px-3 py-2 focus:border-icy focus:outline-none">
                <option value="">Todos los tipos</option>
                @foreach($types as $t)
                    <option value="{{ $t->value }}" @selected($selectedType === $t->value)>
                        {{ $t->label() }}
                    </option>
                @endforeach
            </select>

            {{-- Status filter --}}
            <select name="status" class="bg-navy-dark border border-gunmetal text-icy text-[12.5px] font-mono px-3 py-2 focus:border-icy focus:outline-none">
                <option value="">Todos los estados</option>
                @foreach($statuses as $st)
                    <option value="{{ $st->value }}" @selected($selectedStatus === $st->value)>
                        {{ $st->label() }}
                    </option>
                @endforeach
            </select>

            {{-- Organization filter --}}
            <select name="organization_id" class="bg-navy-dark border border-gunmetal text-icy text-[12.5px] font-mono px-3 py-2 focus:border-icy focus:outline-none truncate">
                <option value="">Todas las orgs</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected($selectedOrg === $org->id)>
                        {{ $org->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2 self-end md:self-auto shrink-0">
            <button type="submit" class="px-4 py-2 bg-navy-light hover:bg-navy-dark border border-icy/40 text-icy text-[12px] font-mono font-bold uppercase tracking-wider transition-all">
                Filtrar
            </button>
            @if($search || $selectedType || $selectedStatus || $selectedOrg)
                <a href="{{ route('admin.analyses.index') }}" class="px-2.5 py-2 text-icy/60 hover:text-icy text-[12px] font-mono underline">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    {{-- Analyses Table --}}
    <div class="bg-gunmetal-card border-2 border-gunmetal shadow-brutal overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gunmetal bg-navy-dark/70 text-[11px] font-mono font-bold text-icy/80 uppercase tracking-wider">
                        <th class="py-3 px-4">Documento</th>
                        <th class="py-3 px-4">Tipo</th>
                        <th class="py-3 px-4">Organización / Usuario</th>
                        <th class="py-3 px-4">Modelo LLM</th>
                        <th class="py-3 px-4 text-right">Tokens In / Out</th>
                        <th class="py-3 px-4 text-center">Latencia</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gunmetal text-[13px] font-mono">
                    @forelse($analyses as $analysis)
                        <tr class="hover:bg-navy-dark/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-icy truncate max-w-xs" title="{{ $analysis->document?->original_filename }}">
                                        {{ $analysis->document?->original_filename ?? '—' }}
                                    </span>
                                    <span class="text-[11px] text-icy/50 font-mono">{{ $analysis->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="text-[11px] px-2 py-0.5 bg-navy-dark border border-gunmetal text-icy/90">
                                    {{ $analysis->analysis_type->label() }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-icy/80">
                                <div class="flex flex-col text-[12px]">
                                    <span class="font-bold truncate max-w-40">{{ $analysis->organization?->name ?? '—' }}</span>
                                    <span class="text-icy/50 truncate max-w-40">{{ $analysis->requester?->email ?? '—' }}</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex flex-col text-[11.5px]">
                                    <span class="text-icy/90 font-bold truncate max-w-32">{{ $analysis->llm_model }}</span>
                                    <span class="text-icy/50 uppercase text-[10px]">{{ $analysis->llm_provider }}</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="flex flex-col text-[11.5px]">
                                    <span class="text-icy font-bold">{{ number_format($analysis->totalTokens()) }}</span>
                                    <span class="text-icy/50 text-[10px]">
                                        {{ number_format($analysis->input_tokens) }} / {{ number_format($analysis->output_tokens) }}
                                    </span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center text-[12px] text-icy/80">
                                {{ $analysis->processing_time_ms ? number_format($analysis->processing_time_ms / 1000, 1) . 's' : '—' }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <x-document-status-badge :status="$analysis->status" />
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('admin.analyses.show', $analysis->id) }}" 
                                   class="px-2.5 py-1 bg-navy-dark hover:bg-navy-light border border-gunmetal text-icy text-[11px] font-bold uppercase transition-colors"
                                   title="Ver auditoría detallada">
                                    Inspeccionar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-icy/50 italic">
                                No se encontraron registros de análisis con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($analyses->hasPages())
            <div class="p-4 border-t border-gunmetal bg-navy-dark/30">
                {{ $analyses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
