@extends('layouts.app')

@section('title', 'Crear Usuario o Administrador - Admin Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-3xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col gap-1 pb-4 border-b-2 border-gunmetal">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-icy/60 hover:text-icy text-[13px] font-mono">← Usuarios</a>
            <span class="text-icy/40">/</span>
            <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Crear Usuario o Administrador</h1>
        </div>
        <p class="text-[13px] font-mono text-icy/70">Registra una nueva cuenta con credenciales y asigna roles y organización</p>
    </header>

    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-gunmetal-card border-2 border-gunmetal p-6 md:p-8 shadow-brutal flex flex-col gap-6">
        @csrf

        {{-- Name --}}
        <div class="flex flex-col gap-2">
            <label for="name" class="text-[12.5px] font-mono font-bold text-icy uppercase tracking-wider">
                Nombre Completo <span class="text-red-400">*</span>
            </label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                required 
                placeholder="Ej. Constanza Silva" 
                class="w-full bg-navy-dark border-2 border-gunmetal focus:border-icy text-icy font-mono text-[13.5px] px-4 py-2.5 focus:outline-none"
            >
            @error('name')
                <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Email --}}
        <div class="flex flex-col gap-2">
            <label for="email" class="text-[12.5px] font-mono font-bold text-icy uppercase tracking-wider">
                Correo Electrónico <span class="text-red-400">*</span>
            </label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                placeholder="usuario@dominio.cl" 
                class="w-full bg-navy-dark border-2 border-gunmetal focus:border-icy text-icy font-mono text-[13.5px] px-4 py-2.5 focus:outline-none"
            >
            @error('email')
                <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="flex flex-col gap-2">
            <label for="password" class="text-[12.5px] font-mono font-bold text-icy uppercase tracking-wider">
                Contraseña Inicial <span class="text-red-400">*</span>
            </label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                placeholder="Mínimo 8 caracteres" 
                class="w-full bg-navy-dark border-2 border-gunmetal focus:border-icy text-icy font-mono text-[13.5px] px-4 py-2.5 focus:outline-none"
            >
            <span class="text-[11.5px] font-mono text-icy/50">El usuario podrá cambiar su contraseña desde su perfil una vez ingrese.</span>
            @error('password')
                <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Role --}}
        <div class="flex flex-col gap-2">
            <label class="text-[12.5px] font-mono font-bold text-icy uppercase tracking-wider">
                Rol del Usuario <span class="text-red-400">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($roles as $role)
                    <label class="flex items-center gap-3 p-3.5 bg-navy-dark border-2 border-gunmetal hover:border-icy/60 cursor-pointer transition-colors">
                        <input 
                            type="radio" 
                            name="role" 
                            value="{{ $role->value }}" 
                            @checked(old('role', 'user') === $role->value) 
                            class="text-icy focus:ring-0 focus:ring-offset-0"
                        >
                        <div class="flex flex-col">
                            <span class="font-bold text-icy text-[13px] font-mono">{{ $role->label() }}</span>
                            <span class="text-[11px] font-mono text-icy/60">
                                {{ $role === \App\Enums\UserRole::Admin ? 'Acceso total a métricas, gestión y tickets' : 'Acceso estándar para analizar contratos y documentos' }}
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('role')
                <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Organization Selection --}}
        <div class="flex flex-col gap-3 pt-2 border-t border-gunmetal">
            <label for="organization_id" class="text-[12.5px] font-mono font-bold text-icy uppercase tracking-wider">
                Organización (Tenant)
            </label>
            <select 
                id="organization_id" 
                name="organization_id" 
                class="w-full bg-navy-dark border-2 border-gunmetal focus:border-icy text-icy font-mono text-[13.5px] px-4 py-2.5 focus:outline-none"
            >
                <option value="">-- Crear espacio u organización nueva --</option>
                @foreach($organizations as $org)
                    <option value="{{ $org->id }}" @selected(old('organization_id') == $org->id)>
                        {{ $org->name }} (ID: {{ $org->id }})
                    </option>
                @endforeach
            </select>
            <div class="flex flex-col gap-1.5 mt-1">
                <span class="text-[11.5px] font-mono text-icy/60">O especifica el nombre de una nueva organización:</span>
                <input 
                    type="text" 
                    name="new_organization_name" 
                    value="{{ old('new_organization_name') }}" 
                    placeholder="Nombre de la nueva organización (opcional)" 
                    class="w-full bg-navy-dark border border-gunmetal focus:border-icy text-icy font-mono text-[13px] px-3.5 py-2 focus:outline-none"
                >
            </div>
            @error('organization_id')
                <span class="text-[12px] font-mono text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit and Cancel --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gunmetal">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-transparent hover:bg-navy-light text-icy/70 hover:text-icy font-mono text-[13px] uppercase tracking-wider transition-colors">
                Cancelar
            </a>
            <button 
                type="submit" 
                class="px-6 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/60 text-icy font-mono font-bold text-[13px] uppercase tracking-wider shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px transition-all cursor-pointer"
            >
                Crear Usuario
            </button>
        </div>

    </form>
</div>
@endsection
