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

            <!-- Lorebook Link -->
            <a href="#" 
               class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Lorebook
            </a>

            <!-- Plans Link -->
            <a href="{{ route('landing') }}#planes"
               class="flex items-center gap-3 px-3 py-2.5 text-[13.5px] font-mono text-icy/70 hover:text-icy hover:bg-navy-light border border-transparent transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.773-.564-.373-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
                </svg>
                Planes
            </a>
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
