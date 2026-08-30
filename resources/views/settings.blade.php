@extends('layouts.app')

@section('title', 'Configuración - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-4xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col gap-1 pb-4 border-b-2 border-[#35393C]">
        <h1 class="text-2xl md:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight">Configuración</h1>
        <p class="text-[13px] font-mono text-[#A4D8FF]/70">Administra tu cuenta, seguridad y preferencias</p>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div id="flash-success" class="bg-[#1B4332] border-2 border-[#35393C] text-[#A4D8FF] px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div id="flash-error" class="bg-[#0F1E36] border-2 border-[#35393C] text-[#A4D8FF] px-4 py-3.5 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div id="settings-tabs" class="flex flex-wrap gap-1 border-b-2 border-[#35393C]">
        @foreach([
            ['profile',    'Perfil',      'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['security',   'Seguridad',   'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
            ['appearance', 'Apariencia',  'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
            ['account',    'Cuenta',      'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
        ] as [$key, $label, $icon])
            <button
                id="tab-btn-{{ $key }}"
                onclick="switchTab('{{ $key }}')"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 text-[13px] font-mono font-bold uppercase tracking-wider transition-all border-b-2 -mb-0.5"
                data-tab="{{ $key }}"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ================================================================ --}}
    {{-- TAB: PERFIL --}}
    {{-- ================================================================ --}}
    <div id="tab-profile" class="settings-tab flex flex-col gap-6">

        {{-- Avatar card --}}
        <section class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-serif font-bold text-[#A4D8FF]">Foto de perfil</h2>
                <p class="text-[13px] font-mono text-[#A4D8FF]/70">Formatos permitidos: JPG, PNG, WebP · Máximo 2 MB</p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                {{-- Current avatar / fallback --}}
                <div class="relative group shrink-0">
                    @if($user->avatarUrl())
                        <img
                            id="avatar-preview"
                            src="{{ $user->avatarUrl() }}"
                            alt="Avatar de {{ $user->name }}"
                            class="w-24 h-24 object-cover border-2 border-[#35393C] shadow-brutal"
                        >
                    @else
                        <div id="avatar-preview-initials" class="w-24 h-24 bg-[#0F1E36] border-2 border-[#35393C] shadow-brutal flex items-center justify-center">
                            <span class="text-3xl font-serif font-extrabold text-[#A4D8FF]">{{ $user->initials() }}</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
                         onclick="document.getElementById('avatar-file').click()">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Upload form --}}
                <form id="avatar-form" action="{{ route('settings.avatar') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 flex-1">
                    @csrf
                    <input type="file" id="avatar-file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewAvatar(this)">
                    @error('avatar')
                        <p class="text-[12px] font-mono text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="document.getElementById('avatar-file').click()"
                            class="flex items-center gap-2 px-4 py-2 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Elegir imagen
                        </button>
                        <button type="submit" id="avatar-save-btn"
                            class="hidden flex items-center gap-2 px-4 py-2 bg-[#1B4332] hover:bg-[#22543D] border border-[#A4D8FF]/30 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider cursor-pointer">
                            Guardar foto
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Name card --}}
        <section class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-serif font-bold text-[#A4D8FF]">Información personal</h2>
                <p class="text-[13px] font-mono text-[#A4D8FF]/70">Actualiza tu nombre visible en la plataforma</p>
            </div>

            <form action="{{ route('settings.profile') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                {{-- Name --}}
                <div class="flex flex-col gap-1.5">
                    <label for="name" class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wider">Nombre</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="100"
                        class="bg-[#15181B] border-2 border-[#35393C] text-[#A4D8FF] text-[13px] font-mono px-3 py-2.5 focus:outline-none focus:border-[#A4D8FF]/60 w-full transition-colors @error('name') border-red-500 @enderror"
                        placeholder="Tu nombre completo"
                    >
                    @error('name')
                        <p class="text-[12px] font-mono text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (read-only) --}}
                <div class="flex flex-col gap-1.5">
                    <label for="email-display" class="text-[12px] font-mono font-bold text-[#A4D8FF]/50 uppercase tracking-wider">
                        Correo electrónico
                        <span class="ml-2 px-1.5 py-0.5 bg-[#15181B] border border-[#35393C] text-[10px] normal-case tracking-normal">Solo lectura</span>
                    </label>
                    <input
                        type="email"
                        id="email-display"
                        value="{{ $user->email }}"
                        disabled
                        class="bg-[#15181B]/50 border-2 border-[#35393C]/50 text-[#A4D8FF]/40 text-[13px] font-mono px-3 py-2.5 w-full cursor-not-allowed"
                    >
                </div>

                <div class="pt-1">
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- ================================================================ --}}
    {{-- TAB: SEGURIDAD --}}
    {{-- ================================================================ --}}
    <div id="tab-security" class="settings-tab hidden flex flex-col gap-6">
        <section class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-serif font-bold text-[#A4D8FF]">Cambiar contraseña</h2>
                <p class="text-[13px] font-mono text-[#A4D8FF]/70">Usa una contraseña de al menos 8 caracteres</p>
            </div>

            <form action="{{ route('settings.password') }}" method="POST" class="flex flex-col gap-4 max-w-md">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label for="current_password" class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wider">Contraseña actual</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        class="bg-[#15181B] border-2 border-[#35393C] text-[#A4D8FF] text-[13px] font-mono px-3 py-2.5 focus:outline-none focus:border-[#A4D8FF]/60 w-full transition-colors @error('current_password') border-red-500 @enderror"
                        placeholder="••••••••"
                    >
                    @error('current_password')
                        <p class="text-[12px] font-mono text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wider">Nueva contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="bg-[#15181B] border-2 border-[#35393C] text-[#A4D8FF] text-[13px] font-mono px-3 py-2.5 focus:outline-none focus:border-[#A4D8FF]/60 w-full transition-colors @error('password') border-red-500 @enderror"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-[12px] font-mono text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="password_confirmation" class="text-[12px] font-mono font-bold text-[#A4D8FF]/80 uppercase tracking-wider">Confirmar nueva contraseña</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="bg-[#15181B] border-2 border-[#35393C] text-[#A4D8FF] text-[13px] font-mono px-3 py-2.5 focus:outline-none focus:border-[#A4D8FF]/60 w-full transition-colors"
                        placeholder="••••••••"
                    >
                </div>

                <div class="pt-1">
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Actualizar contraseña
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- ================================================================ --}}
    {{-- TAB: APARIENCIA --}}
    {{-- ================================================================ --}}
    <div id="tab-appearance" class="settings-tab hidden flex flex-col gap-6">
        <section class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-serif font-bold text-[#A4D8FF]">Apariencia</h2>
                <p class="text-[13px] font-mono text-[#A4D8FF]/70">Personaliza el aspecto visual de la plataforma</p>
            </div>

            <div class="flex flex-col gap-4">
                {{-- Theme toggle placeholder --}}
                <div class="flex items-center justify-between p-4 bg-[#15181B] border-2 border-[#35393C]">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[13px] font-mono font-bold text-[#A4D8FF]">Tema de color</span>
                        <span class="text-[11px] font-mono text-[#A4D8FF]/60">Modo oscuro · modo claro · sistema</span>
                    </div>
                    <button disabled
                        class="flex items-center gap-2 px-3 py-1.5 bg-[#222629] border border-[#35393C] text-[11px] font-mono font-bold text-[#A4D8FF]/40 cursor-not-allowed uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        Próximamente
                    </button>
                </div>

                {{-- Language placeholder --}}
                <div class="flex items-center justify-between p-4 bg-[#15181B] border-2 border-[#35393C]">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[13px] font-mono font-bold text-[#A4D8FF]">Idioma de la interfaz</span>
                        <span class="text-[11px] font-mono text-[#A4D8FF]/60">Español · English · Português</span>
                    </div>
                    <button disabled
                        class="flex items-center gap-2 px-3 py-1.5 bg-[#222629] border border-[#35393C] text-[11px] font-mono font-bold text-[#A4D8FF]/40 cursor-not-allowed uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        Próximamente
                    </button>
                </div>

                {{-- Density placeholder --}}
                <div class="flex items-center justify-between p-4 bg-[#15181B] border-2 border-[#35393C]">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[13px] font-mono font-bold text-[#A4D8FF]">Densidad del panel</span>
                        <span class="text-[11px] font-mono text-[#A4D8FF]/60">Compacto · Normal · Espacioso</span>
                    </div>
                    <button disabled
                        class="flex items-center gap-2 px-3 py-1.5 bg-[#222629] border border-[#35393C] text-[11px] font-mono font-bold text-[#A4D8FF]/40 cursor-not-allowed uppercase tracking-wider">
                        Próximamente
                    </button>
                </div>
            </div>
        </section>
    </div>

    {{-- ================================================================ --}}
    {{-- TAB: CUENTA (DANGER ZONE) --}}
    {{-- ================================================================ --}}
    <div id="tab-account" class="settings-tab hidden flex flex-col gap-6">

        {{-- Plan info --}}
        <section class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-4 shadow-brutal">
            <h2 class="text-xl font-serif font-bold text-[#A4D8FF]">Información de cuenta</h2>
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="flex flex-col gap-1 p-4 bg-[#15181B] border border-[#35393C]">
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/60 uppercase tracking-wider">Usuario</span>
                    <span class="text-[14px] font-mono font-bold text-[#A4D8FF]">{{ $user->email }}</span>
                </div>
                <div class="flex flex-col gap-1 p-4 bg-[#15181B] border border-[#35393C]">
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/60 uppercase tracking-wider">Miembro desde</span>
                    <span class="text-[14px] font-mono font-bold text-[#A4D8FF]">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex flex-col gap-1 p-4 bg-[#15181B] border border-[#35393C]">
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/60 uppercase tracking-wider">Organización</span>
                    <span class="text-[14px] font-mono font-bold text-[#A4D8FF]">{{ $user->organization?->name ?? 'Sin organización' }}</span>
                </div>
                <div class="flex flex-col gap-1 p-4 bg-[#15181B] border border-[#35393C]">
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/60 uppercase tracking-wider">Plan activo</span>
                    @php
                        $settingsPlanSlug = $user->organization?->currentPlanSlug() ?? 'free';
                        $settingsPlanName = config("billing.plans.{$settingsPlanSlug}.name", 'Free');
                    @endphp
                    <span class="text-[14px] font-mono font-bold text-[#A4D8FF]">{{ $settingsPlanName }}</span>
                </div>
            </div>
        </section>

        {{-- Danger zone --}}
        <section class="bg-[#222629] border-2 border-red-900/60 p-6 flex flex-col gap-5 shadow-brutal">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h2 class="text-xl font-serif font-bold text-red-400">Zona de peligro</h2>
                </div>
                <p class="text-[13px] font-mono text-[#A4D8FF]/70">
                    Esta acción es <strong class="text-red-400">permanente e irreversible</strong>.
                    Se eliminarán tu cuenta, organización, todos los documentos y análisis asociados.
                </p>
            </div>

            <div class="border border-red-900/50 bg-[#15181B] p-4 flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <h3 class="text-[14px] font-mono font-bold text-red-400">Eliminar cuenta permanentemente</h3>
                    <ul class="text-[12px] font-mono text-[#A4D8FF]/60 flex flex-col gap-1 list-disc ml-4">
                        <li>Se eliminará tu organización y todos sus datos</li>
                        <li>Se eliminarán todos tus documentos del almacenamiento</li>
                        <li>Se cancelarán todos los análisis pendientes</li>
                        <li>No podrás recuperar ningún dato</li>
                    </ul>
                </div>

                <form id="delete-account-form" action="{{ route('settings.delete') }}" method="POST" class="flex flex-col gap-3">
                    @csrf
                    @method('DELETE')
                    <div class="flex flex-col gap-1.5">
                        <label for="confirmation" class="text-[12px] font-mono font-bold text-red-400/80 uppercase tracking-wider">
                            Escribe <span class="text-red-300">ELIMINAR</span> para confirmar
                        </label>
                        <input
                            type="text"
                            id="confirmation"
                            name="confirmation"
                            autocomplete="off"
                            class="bg-[#0F1E36] border-2 border-red-900/60 text-[#A4D8FF] text-[13px] font-mono px-3 py-2.5 focus:outline-none focus:border-red-500/60 w-full max-w-xs transition-colors @error('confirmation') border-red-500 @enderror"
                            placeholder="ELIMINAR"
                        >
                        @error('confirmation')
                            <p class="text-[12px] font-mono text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        id="delete-account-btn"
                        disabled
                        class="flex items-center gap-2 px-5 py-2.5 bg-red-900/40 border border-red-900/60 text-[13px] font-mono font-bold text-red-400/50 uppercase tracking-wider cursor-not-allowed transition-all w-fit"
                        onclick="return confirm('¿Estás completamente seguro? Esta acción no se puede deshacer.')">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Eliminar mi cuenta
                    </button>
                </form>
            </div>
        </section>
    </div>

</div>
@endsection

@push('scripts')
<script>
// -----------------------------------------------------------------------
// Tab switching
// -----------------------------------------------------------------------
const TAB_ACTIVE   = 'border-[#A4D8FF] text-[#A4D8FF]';
const TAB_INACTIVE = 'border-transparent text-[#A4D8FF]/50 hover:text-[#A4D8FF]/80';

function switchTab(key) {
    document.querySelectorAll('.settings-tab').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.classList.remove(...TAB_ACTIVE.split(' '));
        btn.classList.add(...TAB_INACTIVE.split(' '));
    });

    const tab = document.getElementById('tab-' + key);
    const btn = document.getElementById('tab-btn-' + key);
    if (tab) tab.classList.remove('hidden');
    if (btn) {
        btn.classList.remove(...TAB_INACTIVE.split(' '));
        btn.classList.add(...TAB_ACTIVE.split(' '));
    }

    const url = new URL(window.location);
    url.searchParams.set('tab', key);
    history.replaceState({}, '', url);
}

(function () {
    const params = new URLSearchParams(window.location.search);
    switchTab(params.get('tab') || 'profile');
})();

// -----------------------------------------------------------------------
// Avatar preview
// -----------------------------------------------------------------------
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const existing = document.getElementById('avatar-preview');
        const initials  = document.getElementById('avatar-preview-initials');
        if (existing) {
            existing.src = e.target.result;
        } else if (initials) {
            const img = document.createElement('img');
            img.id        = 'avatar-preview';
            img.src       = e.target.result;
            img.alt       = 'Vista previa';
            img.className = 'w-24 h-24 object-cover border-2 border-[#35393C] shadow-brutal';
            initials.replaceWith(img);
        }
        document.getElementById('avatar-save-btn')?.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// -----------------------------------------------------------------------
// Delete account confirmation guard
// -----------------------------------------------------------------------
const confirmInput = document.getElementById('confirmation');
const deleteBtn    = document.getElementById('delete-account-btn');

if (confirmInput && deleteBtn) {
    confirmInput.addEventListener('input', () => {
        const valid = confirmInput.value === 'ELIMINAR';
        deleteBtn.disabled = !valid;
        if (valid) {
            deleteBtn.className = 'flex items-center gap-2 px-5 py-2.5 bg-red-900 hover:bg-red-800 border border-red-600 text-[13px] font-mono font-bold text-red-200 uppercase tracking-wider shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all w-fit cursor-pointer';
        } else {
            deleteBtn.className = 'flex items-center gap-2 px-5 py-2.5 bg-red-900/40 border border-red-900/60 text-[13px] font-mono font-bold text-red-400/50 uppercase tracking-wider cursor-not-allowed transition-all w-fit';
        }
    });
}

// -----------------------------------------------------------------------
// Auto-dismiss flash messages after 5s
// -----------------------------------------------------------------------
setTimeout(() => {
    document.getElementById('flash-success')?.remove();
    document.getElementById('flash-error')?.remove();
}, 5000);
</script>
@endpush
