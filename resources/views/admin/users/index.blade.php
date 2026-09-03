@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Admin Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="text-icy/60 hover:text-icy text-[13px] font-mono">← Panel</a>
                <span class="text-icy/40">/</span>
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Gestión de Usuarios</h1>
            </div>
            <p class="text-[13px] font-mono text-icy/70">Control de credenciales, roles administrativos y asignación de organizaciones</p>
        </div>

        <a href="{{ route('admin.users.create') }}" 
           class="flex items-center gap-2 px-4 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/60 text-[12px] font-mono font-bold text-icy shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px transition-all uppercase tracking-wider self-start md:self-auto">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Usuario / Admin
        </a>
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
    @if($errors->has('user'))
        <div class="bg-red-950/80 border-2 border-red-800 text-red-200 px-4 py-3 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ $errors->first('user') }}
        </div>
    @endif

    {{-- Filters Card --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="bg-gunmetal-card border-2 border-gunmetal p-4 shadow-brutal flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex flex-1 flex-col sm:flex-row gap-3 w-full">
            {{-- Search input --}}
            <div class="relative flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Buscar por nombre o correo..." 
                    class="w-full bg-navy-dark border border-gunmetal text-icy text-[13px] font-mono px-3.5 py-2 placeholder-icy/40 focus:border-icy focus:outline-none"
                >
            </div>

            {{-- Role filter --}}
            <select name="role" class="bg-navy-dark border border-gunmetal text-icy text-[13px] font-mono px-3.5 py-2 focus:border-icy focus:outline-none">
                <option value="">Todos los roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected($selectedRole === $role->value)>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </select>

            {{-- Organization filter --}}
            <select name="organization_id" class="bg-navy-dark border border-gunmetal text-icy text-[13px] font-mono px-3.5 py-2 focus:border-icy focus:outline-none max-w-xs truncate">
                <option value="">Todas las organizaciones</option>
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
            @if($search || $selectedRole || $selectedOrg)
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-transparent text-icy/60 hover:text-icy text-[12px] font-mono underline">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    {{-- Users Table --}}
    <div class="bg-gunmetal-card border-2 border-gunmetal shadow-brutal overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gunmetal bg-navy-dark/70 text-[11px] font-mono font-bold text-icy/80 uppercase tracking-wider">
                        <th class="py-3 px-4">Usuario</th>
                        <th class="py-3 px-4">Rol</th>
                        <th class="py-3 px-4">Organización</th>
                        <th class="py-3 px-4 text-center">Docs</th>
                        <th class="py-3 px-4 text-center">Análisis</th>
                        <th class="py-3 px-4 text-right">Tokens IA</th>
                        <th class="py-3 px-4">Registro</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gunmetal text-[13px] font-mono">
                    @forelse($users as $user)
                        <tr class="hover:bg-navy-dark/40 transition-colors">
                            {{-- User details --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-navy-dark border border-icy/30 flex items-center justify-center text-icy text-[11px] font-bold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col truncate">
                                        <span class="font-bold text-icy truncate">{{ $user->name }}</span>
                                        <span class="text-[11.5px] text-icy/60 truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 text-[10.5px] font-bold uppercase tracking-wider {{ $user->role->badgeClasses() }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>

                            {{-- Organization --}}
                            <td class="py-3.5 px-4 text-icy/80">
                                {{ $user->organization?->name ?? '—' }}
                            </td>

                            {{-- Docs --}}
                            <td class="py-3.5 px-4 text-center text-icy font-semibold">
                                {{ $user->documents_count }}
                            </td>

                            {{-- Analyses --}}
                            <td class="py-3.5 px-4 text-center text-icy font-semibold">
                                {{ $user->analyses_count }}
                            </td>

                            {{-- Tokens --}}
                            <td class="py-3.5 px-4 text-right text-icy/90">
                                {{ $user->total_tokens ? number_format((int) $user->total_tokens) : '0' }}
                            </td>

                            {{-- Date --}}
                            <td class="py-3.5 px-4 text-[11.5px] text-icy/60">
                                {{ $user->created_at?->format('d/m/Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                       class="px-2.5 py-1 bg-navy-dark hover:bg-navy-light border border-gunmetal text-icy text-[11px] font-bold uppercase transition-colors"
                                       title="Editar usuario">
                                        Editar
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario? Esta acción es irreversible.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-2.5 py-1 bg-red-950/40 hover:bg-red-900 border border-red-800 text-red-300 text-[11px] font-bold uppercase transition-colors cursor-pointer"
                                                    title="Eliminar usuario">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-icy/50 italic">
                                No se encontraron usuarios con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="p-4 border-t border-gunmetal bg-navy-dark/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
