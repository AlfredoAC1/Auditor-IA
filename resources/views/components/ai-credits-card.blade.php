@props([
    'creditsRemaining',
    'creditsQuota',
    'creditRefreshInterval', // 'weekly' | 'monthly' | 'none'
    'periodExpiresAt',       // ?Carbon
    'planSlug',
    'planName',
    'allowsModelSelection' => false,
])

@php
    $unlimited    = $creditsQuota === 0 || $creditRefreshInterval === 'none';
    $percent      = $unlimited ? 100 : ($creditsQuota > 0 ? min(100, ($creditsRemaining / $creditsQuota) * 100) : 0);
    $depleted     = !$unlimited && $creditsRemaining <= 0;
    $critical     = !$unlimited && !$depleted && $percent < 15;
    $low          = !$unlimited && !$depleted && !$critical && $percent < 35;

    // Bar colour
    $barColor = match(true) {
        $depleted => 'bg-red-600',
        $critical => 'bg-orange-500',
        $low      => 'bg-yellow-400',
        default   => 'bg-[#A4D8FF]',
    };

    // Human refresh label
    $refreshLabel = match($creditRefreshInterval) {
        'weekly'  => 'semanal',
        'monthly' => 'mensual',
        default   => 'sin renovación',
    };

    // Time remaining label
    $timeLabel = null;
    if ($periodExpiresAt !== null) {
        $diff = now()->diffAsCarbonInterval($periodExpiresAt, false);
        if ($diff->totalSeconds <= 0) {
            $timeLabel = 'Renovación pendiente';
        } elseif ($diff->totalDays >= 1) {
            $days = (int) $diff->totalDays;
            $timeLabel = "Renueva en {$days} " . ($days === 1 ? 'día' : 'días');
        } else {
            $hours = max(1, (int) $diff->totalHours);
            $timeLabel = "Renueva en {$hours} " . ($hours === 1 ? 'hora' : 'horas');
        }
    }

    // Tier labels for plan badge
    $tierDisplay = match($planSlug) {
        'pro'      => 'Pro — Todos los modelos',
        'lifetime' => 'Lifetime — Ilimitado',
        default    => 'Free — Modelos básicos',
    };
@endphp

<section id="ai-credits-card" class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#35393C] pb-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                {{-- Bolt icon --}}
                <svg class="w-4 h-4 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <h2 class="text-xl font-serif font-bold text-[#A4D8FF] tracking-tight">Créditos IA</h2>
                <span class="inline-flex px-2.5 py-0.5 text-[11px] font-mono font-bold uppercase tracking-wide border shadow-brutal-sm
                    @if($planSlug === 'free') bg-[#15181B] text-[#A4D8FF]/70 border-[#35393C]
                    @elseif($planSlug === 'lifetime') bg-[#A4D8FF] text-[#0F1E36] border-[#0F1E36]
                    @else bg-[#0F1E36] text-[#A4D8FF] border-[#A4D8FF]/40
                    @endif">
                    {{ $tierDisplay }}
                </span>
            </div>
            <p class="text-[12px] font-mono text-[#A4D8FF]/60">
                Renovación {{ $refreshLabel }}
                @if($timeLabel !== null)
                    · <span class="{{ $depleted ? 'text-red-400' : ($critical ? 'text-orange-400' : 'text-[#A4D8FF]/60') }}">{{ $timeLabel }}</span>
                @endif
            </p>
        </div>
        @if($planSlug === 'free')
            <a href="{{ route('landing') }}#planes"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                Obtener más
            </a>
        @endif
    </div>

    {{-- Balance row --}}
    <div class="flex flex-col gap-3">

        @if($unlimited)
            {{-- Unlimited plan --}}
            <div class="flex items-center gap-4">
                <span class="text-5xl font-serif font-extrabold text-[#A4D8FF] leading-none">∞</span>
                <div class="flex flex-col gap-0.5">
                    <span class="text-[13px] font-mono font-bold text-[#A4D8FF]">Créditos ilimitados</span>
                    <span class="text-[11px] font-mono text-[#A4D8FF]/60">Tu plan Lifetime incluye acceso sin restricciones</span>
                </div>
            </div>
        @else
            {{-- Numeric balance --}}
            <div class="flex items-end justify-between gap-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-5xl font-serif font-extrabold leading-none
                        {{ $depleted ? 'text-red-400' : ($critical ? 'text-orange-400' : ($low ? 'text-yellow-400' : 'text-[#A4D8FF]')) }}">
                        {{ number_format($creditsRemaining, 0, ',', '.') }}
                    </span>
                    <span class="text-[14px] font-mono text-[#A4D8FF]/50">/ {{ number_format($creditsQuota, 0, ',', '.') }}</span>
                </div>
                <span class="text-[11px] font-mono text-[#A4D8FF]/60 pb-1">créditos restantes</span>
            </div>

            {{-- Progress bar --}}
            <div class="flex flex-col gap-1.5">
                <div class="h-3 border-2 border-[#15181B] bg-[#15181B] relative overflow-hidden">
                    <div
                        class="h-full transition-all duration-700 ease-out {{ $barColor }}"
                        style="width: {{ number_format($percent, 2, '.', '') }}%"
                    ></div>
                    {{-- Animated shimmer when healthy --}}
                    @if(!$depleted && !$critical && !$low)
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-pulse pointer-events-none"></div>
                    @endif
                </div>
                <div class="flex items-center justify-between text-[11px] font-mono text-[#A4D8FF]/50">
                    <span>{{ number_format($percent, 0) }}% disponible</span>
                    @if($depleted)
                        <span class="text-red-400 font-bold animate-pulse">Sin créditos · análisis bloqueados</span>
                    @elseif($critical)
                        <span class="text-orange-400 font-bold">Créditos críticos — pronto se agotarán</span>
                    @elseif($low)
                        <span class="text-yellow-400 font-bold">Créditos bajos</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Cost legend (hidden for unlimited) --}}
    @if(!$unlimited)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-[#35393C]">
            @foreach([
                ['label' => 'Básico', 'cost' => '1 cr.', 'dot' => 'bg-[#A4D8FF]/60'],
                ['label' => 'Estándar', 'cost' => '2 cr.', 'dot' => 'bg-[#A4D8FF]'],
                ['label' => 'Avanzado', 'cost' => '3 cr.', 'dot' => 'bg-blue-400'],
                ['label' => 'Premium', 'cost' => '4 cr.', 'dot' => 'bg-indigo-400'],
            ] as $tier)
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full shrink-0 {{ $tier['dot'] }}"></span>
                    <span class="text-[11px] font-mono text-[#A4D8FF]/60">
                        {{ $tier['label'] }}
                        <span class="text-[#A4D8FF]/40">·</span>
                        <strong class="text-[#A4D8FF]/80">{{ $tier['cost'] }}</strong>
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Model selection note (Pro+ only) --}}
    @if($allowsModelSelection)
        <p class="text-[11px] font-mono text-[#A4D8FF]/50 border-t border-[#35393C] pt-3">
            <svg class="inline w-3 h-3 mr-1 text-[#A4D8FF]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.346.09A4.97 4.97 0 0012 17a4.97 4.97 0 00-2.071-.445L9.637 17z"/>
            </svg>
            Puedes seleccionar el modelo IA al realizar cada análisis. Los modelos más potentes consumen más créditos.
        </p>
    @endif
</section>
