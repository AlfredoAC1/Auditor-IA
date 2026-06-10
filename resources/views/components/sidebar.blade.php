<aside class="w-full md:w-64 md:h-screen md:sticky md:top-0 bg-[#0d0d0d] flex flex-col justify-between border-b md:border-b-0 md:border-r border-zinc-800/40 px-4 py-6 shrink-0">
    <div class="flex flex-col gap-8 overflow-y-auto flex-1 min-h-0">
        <!-- Logo and Brand -->
        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-2 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/10 group-hover:scale-105 transition-transform duration-200">
                <!-- Sleek Document/AI SVG Icon -->
                <svg class="w-5.5 h-5.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-white bg-clip-text bg-gradient-to-r from-white via-zinc-100 to-zinc-400">
                Auditor-IA
            </span>
        </a>

        <!-- Main Navigation Links -->
        <nav class="flex flex-col gap-1.5" aria-label="Menú principal">
            <!-- Dashboard Link -->
            <a href="{{ url('/dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] font-medium transition-all duration-200 {{ request()->is('dashboard') ? 'bg-zinc-800/60 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/50' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] font-medium transition-all duration-200 {{ request()->is('analisis*') ? 'bg-zinc-800/60 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/50' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Mis análisis
            </a>

            <!-- Lorebook Link -->
            <a href="#" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] font-medium text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/50 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Lorebook
            </a>

            <!-- Plan Pro Link -->
            <a href="#" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] font-medium text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900/50 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.773-.564-.373-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
                </svg>
                Plan Pro
            </a>
        </nav>

        <!-- Recents List Section -->
        <div class="flex flex-col gap-3">
            <span class="text-[11px] font-bold text-zinc-500 tracking-widest px-3 uppercase">Recientes</span>
            @php
                $recentDocs = auth()->check() ? \App\Models\Document::query()
                    ->where('organization_id', auth()->user()->organization_id)
                    ->where('status', \App\Enums\DocumentStatus::Processed)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get() : collect();
            @endphp
            @if($recentDocs->isEmpty())
                <div class="px-3 py-2 rounded-xl bg-zinc-900/10 border border-dashed border-zinc-850/50 flex items-center justify-center">
                    <span class="text-[12px] text-zinc-650 italic">No hay análisis recientes</span>
                </div>
            @else
                <div class="flex flex-col gap-1 px-1">
                    @foreach($recentDocs as $rDoc)
                        <a href="{{ route('analysis.show', $rDoc->id) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[12.5px] text-zinc-400 hover:text-white hover:bg-zinc-900/50 transition-colors truncate" title="{{ $rDoc->original_filename }}">
                            <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
    <div class="mt-8 pt-4 border-t border-zinc-800/40 flex items-center justify-between px-2">
        @if(auth()->check())
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white text-[13px] font-bold shadow-md shadow-violet-500/10">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-[13px] font-semibold text-zinc-200">{{ auth()->user()->name }}</span>
                    <span class="text-[11px] text-zinc-500">Plan gratuito</span>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 text-[13px] font-bold">
                    G
                </div>
                <div class="flex flex-col">
                    <span class="text-[13px] font-semibold text-zinc-400">Invitado</span>
                    <a href="{{ route('login') }}" class="text-[11px] text-violet-400 hover:underline">Iniciar sesión</a>
                </div>
            </div>
        @endif
        <button class="text-zinc-500 hover:text-zinc-300 transition-colors cursor-pointer" aria-label="Opciones de perfil">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </button>
    </div>
</aside>
