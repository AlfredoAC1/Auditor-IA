<aside class="w-full md:w-64 md:h-screen md:sticky md:top-0 bg-gunmetal-dark flex flex-col justify-between border-b-2 md:border-b-0 md:border-r-2 border-gunmetal px-4 py-6 shrink-0">
    <div class="flex flex-col gap-8 overflow-y-auto flex-1 min-h-0">
        <!-- Logo and Brand -->
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-2 py-1 group">
            <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA Logo" class="h-11 w-auto object-contain transition-transform group-hover:scale-150 drop-shadow-[0_0_8px_rgba(164,216,255,0.25)] shrink-0">
            <img src="{{ asset('images/text-logo.png') }}" alt="Auditor-IA" class="h-7.5 w-auto object-contain group-hover:brightness-110 transition-all">
        </a>

        <!-- Main Navigation Links -->
        <nav class="flex flex-col gap-2" aria-label="Menú principal">
            <!-- Dashboard Link -->
            <a href="{{ url('/dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono transition-all {{ request()->is('dashboard') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                Dashboard
            </a>

            <!-- Mis análisis Link (Points to latest processed document, if any) -->
            @php
                $latestDoc = auth()->check() ? \App\Models\Document::query()
                    ->where('organization_id', auth()->user()->organization_id)
                    ->where('status', \App\Enums\DocumentStatus::Processed)
                    ->orderByDesc('created_at')
                    ->first() : null;
            @endphp
            <a href="{{ $latestDoc ? route('analysis.show', $latestDoc->id) : '#' }}" 
               class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono transition-all {{ request()->is('analisis*') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Mis análisis
            </a>

            <!-- Plans Link -->
            <a href="{{ route('landing') }}#planes"
               class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.773-.564-.373-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
                </svg>
                Planes
            </a>

            <!-- Settings Link -->
            <a href="{{ route('settings') }}"
               class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono transition-all {{ request()->is('configuracion*') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Configuración
            </a>

            @if(auth()->check() && auth()->user()->isAdmin())
                <!-- Administration Section -->
                <div class="mt-4 pt-3 border-t border-gunmetal flex flex-col gap-1">
                    <span class="text-[10.5px] font-mono font-bold text-icy/50 uppercase tracking-widest px-3 mb-1">Administración</span>

                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-[13px] font-mono transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Métricas & Panel
                    </a>

                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-[13px] font-mono transition-all {{ request()->routeIs('admin.users*') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Usuarios & Roles
                    </a>

                    <a href="{{ route('admin.organizations.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-[13px] font-mono transition-all {{ request()->routeIs('admin.organizations*') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Organizaciones
                    </a>

                    <a href="{{ route('admin.analyses.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-[13px] font-mono transition-all {{ request()->routeIs('admin.analyses*') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                        <svg class="w-4 h-4 shrink-0 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Auditoría Análisis
                    </a>

                    @php
                        $unresolvedTicketsCount = \App\Models\BugTicket::query()->where('status', \App\Enums\BugTicketStatus::New)->count();
                    @endphp
                    <a href="{{ route('admin.tickets.index') }}" 
                       class="flex items-center justify-between px-3 py-2 text-[13px] font-mono transition-all {{ request()->routeIs('admin.tickets*') ? 'bg-navy-dark text-icy font-bold border border-icy/40 shadow-brutal-sm' : 'text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Tickets Soporte
                        </span>
                        @if($unresolvedTicketsCount > 0)
                            <span class="px-1.5 py-0.2 bg-amber-500 text-black text-[10px] font-black rounded-xs">
                                {{ $unresolvedTicketsCount }}
                            </span>
                        @endif
                    </a>
                </div>
            @endif
        </nav>

        <!-- Recents List Section -->
        <div id="sidebar-recents" class="flex flex-col gap-3">
            <span class="text-[11px] font-mono font-bold text-icy/60 tracking-widest px-3 uppercase border-b border-gunmetal pb-1">Recientes</span>
            @php
                $recentDocs = auth()->check() ? \App\Models\Document::query()
                    ->where('organization_id', auth()->user()->organization_id)
                    ->where('status', \App\Enums\DocumentStatus::Processed)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get() : collect();
            @endphp
            @if($recentDocs->isEmpty())
                <div class="px-3 py-2 bg-navy-light/40 border border-dashed border-gunmetal flex items-center justify-center">
                    <span class="text-[12px] font-mono text-icy/50 italic">No hay análisis recientes</span>
                </div>
            @else
                <div class="flex flex-col gap-1 px-1">
                    @foreach($recentDocs as $rDoc)
                        <a href="{{ route('analysis.show', $rDoc->id) }}" class="flex items-center gap-2 px-2.5 py-1.5 text-[12.5px] font-mono text-icy/80 hover:text-icy hover:bg-navy-light border border-transparent transition-colors truncate" title="{{ $rDoc->original_filename }}">
                            <svg class="w-3.5 h-3.5 text-icy/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ $rDoc->original_filename }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @auth
            <!-- Report Bug CTA Button -->
            <div class="px-1">
                <button 
                    type="button" 
                    onclick="openBugTicketModal()" 
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-gunmetal-card hover:bg-navy-light border border-gunmetal text-[12px] font-mono text-icy/80 hover:text-icy transition-colors cursor-pointer shadow-brutal-sm"
                >
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Reportar un Error
                </button>
            </div>
        @endauth
    </div>

    <!-- User Profile Card -->
    <div class="mt-8 pt-4 border-t-2 border-gunmetal flex items-center justify-between px-2">
        @if(auth()->check())
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-navy-dark border border-icy/40 flex items-center justify-center text-icy text-[13px] font-mono font-bold shadow-brutal-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-[13px] font-bold text-icy truncate max-w-27.5">{{ auth()->user()->name }}</span>
                    @php
                        $sidebarPlanSlug = config('billing.default_plan', 'free');
                        $sidebarActivePlan = auth()->user()->organization?->activePlan();
                        if ($sidebarActivePlan !== null) {
                            $sidebarPlanSlug = $sidebarActivePlan->slug;
                        }
                        $sidebarPlanName = config("billing.plans.{$sidebarPlanSlug}.name", 'Free');
                    @endphp
                    <span @class([
                        'text-[11px] font-mono uppercase tracking-wider',
                        'text-icy font-bold' => $sidebarPlanSlug === 'pro' || $sidebarPlanSlug === 'lifetime',
                        'text-icy/60' => $sidebarPlanSlug === 'free',
                    ])>Plan {{ $sidebarPlanName }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-icy/60 hover:text-icy transition-colors cursor-pointer" aria-label="Cerrar sesión" title="Cerrar sesión">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        @else
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-navy-light border border-gunmetal flex items-center justify-center text-icy/70 text-[13px] font-mono font-bold">
                    G
                </div>
                <div class="flex flex-col">
                    <span class="text-[13px] font-mono text-icy/70">Invitado</span>
                    <a href="{{ route('login') }}" class="text-[11px] font-mono text-icy underline">Iniciar sesión</a>
                </div>
            </div>
        @endif
    </div>
</aside>
