@extends('layouts.app')

@section('title', 'Gestión de Organizaciones - Admin Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-icy/60 hover:text-icy text-[13px] font-mono">← Panel</a>
                <span class="text-icy/40">/</span>
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Gestión de Organizaciones</h1>
            </div>
            <p class="text-[13px] font-mono text-icy/70">Supervisión de tenants, estado de suscripciones y cuotas de créditos IA</p>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-[#1B4332] border-2 border-gunmetal text-icy px-4 py-3 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Search Card --}}
    <form method="GET" action="{{ route('admin.organizations.index') }}" class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="relative flex-1 w-full">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Buscar por nombre, slug o RUT..." 
                class="w-full bg-navy-dark border border-gunmetal text-icy text-[13px] font-mono px-3.5 py-2 placeholder-icy/40 focus:border-icy focus:outline-none"
            >
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <button type="submit" class="px-4 py-2 bg-navy-light hover:bg-navy-dark border border-icy/40 text-icy text-[12px] font-mono font-bold uppercase tracking-wider transition-all">
                Buscar
            </button>
            @if($search)
                <a href="{{ route('admin.organizations.index') }}" class="px-3 py-2 bg-transparent text-icy/60 hover:text-icy text-[12px] font-mono underline">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    {{-- Organizations Table --}}
    <div class="bg-gunmetal-card border-2 border-gunmetal shadow-brutal overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gunmetal bg-navy-dark/70 text-[11px] font-mono font-bold text-icy/80 uppercase tracking-wider">
                        <th class="py-3 px-4">Organización</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4">Plan Activo</th>
                        <th class="py-3 px-4 text-center">Miembros</th>
                        <th class="py-3 px-4 text-center">Documentos</th>
                        <th class="py-3 px-4 text-center">Análisis</th>
                        <th class="py-3 px-4">Créditos IA</th>
                        <th class="py-3 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gunmetal text-[13px] font-mono">
                    @forelse($organizations as $org)
                        <tr class="hover:bg-navy-dark/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-icy">{{ $org->name }}</span>
                                    <span class="text-[11px] text-icy/50 font-mono">{{ $org->slug }} · UUID: {{ substr($org->uuid, 0, 8) }}...</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($org->is_active)
                                    <span class="px-2 py-0.5 text-[10.5px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                                        Activa
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10.5px] font-bold uppercase tracking-wider bg-red-500/20 text-red-300 border border-red-500/40">
                                        Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $plan = $org->activePlan() ?? $org->currentPlan();
                                @endphp
                                <span class="uppercase text-[12px] font-bold text-icy/90">
                                    {{ $plan?->name ?? 'Free' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-icy font-semibold">
                                {{ $org->users_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-icy font-semibold">
                                {{ $org->documents_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-icy font-semibold">
                                {{ $org->analyses_count }}
                            </td>
                            <td class="py-3.5 px-4 text-icy/80">
                                @if($org->creditLedger)
                                    {{ number_format($org->creditLedger->credits_remaining) }} / {{ number_format($org->creditLedger->credits_quota) }}
                                @else
                                    <span class="text-icy/40">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('admin.organizations.toggle', $org->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="px-2.5 py-1 text-[11px] font-bold uppercase transition-colors cursor-pointer border {{ $org->is_active ? 'bg-red-950/30 hover:bg-red-900/60 border-red-800 text-red-300' : 'bg-emerald-950/30 hover:bg-emerald-900/60 border-emerald-800 text-emerald-300' }}"
                                        onclick="return confirm('¿Confirmas cambiar el estado de esta organización?');"
                                    >
                                        {{ $org->is_active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-icy/50 italic">
                                No se encontraron organizaciones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organizations->hasPages())
            <div class="p-4 border-t border-gunmetal bg-navy-dark/30">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
