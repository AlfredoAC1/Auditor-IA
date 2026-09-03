@extends('layouts.app')

@section('title', 'Tickets de Soporte y Errores - Admin Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-icy/60 hover:text-icy text-[13px] font-mono">← Panel</a>
                <span class="text-icy/40">/</span>
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Tickets de Reporte de Errores</h1>
            </div>
            <p class="text-[13px] font-mono text-icy/70">Revisión, gestión y resolución de fallos reportados por los usuarios</p>
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

    {{-- Status Tabs and Search --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b-2 border-gunmetal pb-2">
        <div class="flex gap-2">
            <a href="{{ route('admin.tickets.index', ['status' => 'new', 'search' => $search]) }}" 
               class="px-4 py-2 text-[12.5px] font-mono font-bold uppercase tracking-wider border-b-2 transition-all {{ $currentStatus === 'new' ? 'border-icy text-icy bg-navy-dark' : 'border-transparent text-icy/60 hover:text-icy' }}">
                Nuevos ({{ $newCount }})
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'resolved', 'search' => $search]) }}" 
               class="px-4 py-2 text-[12.5px] font-mono font-bold uppercase tracking-wider border-b-2 transition-all {{ $currentStatus === 'resolved' ? 'border-icy text-icy bg-navy-dark' : 'border-transparent text-icy/60 hover:text-icy' }}">
                Resueltos ({{ $resolvedCount }})
            </a>
            <a href="{{ route('admin.tickets.index', ['status' => 'all', 'search' => $search]) }}" 
               class="px-4 py-2 text-[12.5px] font-mono font-bold uppercase tracking-wider border-b-2 transition-all {{ $currentStatus === 'all' ? 'border-icy text-icy bg-navy-dark' : 'border-transparent text-icy/60 hover:text-icy' }}">
                Todos ({{ $newCount + $resolvedCount }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                placeholder="Buscar ticket..." 
                class="bg-navy-dark border border-gunmetal text-icy text-[12.5px] font-mono px-3 py-1.5 placeholder-icy/40 focus:border-icy focus:outline-none"
            >
            <button type="submit" class="px-3 py-1.5 bg-navy-light hover:bg-navy-dark border border-icy/40 text-icy text-[12px] font-mono font-bold uppercase">
                Buscar
            </button>
        </form>
    </div>

    {{-- Tickets List --}}
    <div class="flex flex-col gap-4">
        @forelse($tickets as $ticket)
            <div class="bg-gunmetal-card border-2 border-gunmetal p-5 shadow-brutal flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gunmetal pb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-icy/50 font-mono text-[12px]">#{{ $ticket->id }}</span>
                        <h2 class="text-base font-serif font-bold text-icy">{{ $ticket->title }}</h2>
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider {{ $ticket->status->badgeClasses() }}">
                            {{ $ticket->status->label() }}
                        </span>
                    </div>
                    <div class="text-[11.5px] font-mono text-icy/60">
                        Reportado: {{ $ticket->created_at?->format('d/m/Y H:i') }}
                    </div>
                </div>

                {{-- Reporter info & description --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-[12.5px] font-mono">
                    <div class="md:col-span-1 flex flex-col gap-1 border-r-0 md:border-r border-gunmetal pr-0 md:pr-4">
                        <span class="text-icy/60 text-[11px] uppercase">Usuario:</span>
                        <span class="font-bold text-icy truncate">{{ $ticket->user?->name ?? 'Usuario eliminado' }}</span>
                        <span class="text-icy/50 text-[11px] truncate">{{ $ticket->user?->email }}</span>
                        <span class="text-icy/60 text-[11px] uppercase mt-2">Organización:</span>
                        <span class="text-icy truncate">{{ $ticket->organization?->name ?? '—' }}</span>
                    </div>

                    <div class="md:col-span-3 flex flex-col justify-between gap-4">
                        <div class="bg-navy-dark p-3.5 border border-gunmetal text-icy text-[13px] font-mono whitespace-pre-wrap selection:bg-icy selection:text-black">
                            {{ $ticket->description }}
                        </div>

                        {{-- Resolution details / action --}}
                        <div class="flex items-center justify-between gap-4 pt-2">
                            @if($ticket->isResolved())
                                <div class="text-[11.5px] text-emerald-400 font-mono flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Resuelto por {{ $ticket->resolver?->name ?? 'Admin' }} el {{ $ticket->resolved_at?->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <div></div>
                                <form action="{{ route('admin.tickets.resolve', $ticket->id) }}" method="POST">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="px-4 py-2 bg-navy-dark hover:bg-emerald-950/70 border border-emerald-500/50 text-emerald-300 text-[11.5px] font-mono font-bold uppercase tracking-wider shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px transition-all cursor-pointer flex items-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Marcar como Resuelto (Descartar)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-gunmetal-card border-2 border-gunmetal p-12 text-center text-icy/50 font-mono text-[13px] italic shadow-brutal">
                No hay tickets en esta sección.
            </div>
        @endforelse

        @if($tickets->hasPages())
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
