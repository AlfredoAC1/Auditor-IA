@php
    $plans = config('billing.plans', []);
    $formatPrice = fn (?int $clp): string => $clp === null ? '—' : '$' . number_format($clp, 0, ',', '.');
    $formatLimit = fn (?int $value, string $suffix = ''): string => $value === null ? 'Ilimitado' : number_format($value, 0, ',', '.') . $suffix;
    $formatStorage = function (?int $bytes): string {
        if ($bytes === null) {
            return 'Ilimitado';
        }
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 0) . ' GB';
        }

        return number_format($bytes / 1048576, 0) . ' MB';
    };
@endphp

<section id="planes" class="relative max-w-6xl mx-auto px-6 pb-24 scroll-mt-24">
    <div class="text-center mb-10">
        <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight mb-3">Planes y precios</h2>
        <p class="text-zinc-400 text-[15px] max-w-xl mx-auto">
            Elige el plan que se adapte a tu volumen de contratos. Cambia o cancela cuando quieras.
        </p>
    </div>

    {{-- Billing toggle --}}
    <div class="flex flex-col items-center gap-3 mb-10" data-pricing-root>
        <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-[#161616] border border-zinc-800/60">
            <button
                type="button"
                data-billing-toggle="monthly"
                class="pricing-toggle px-5 py-2 rounded-lg text-[13px] font-semibold transition-all bg-violet-600 text-white shadow-lg shadow-violet-500/20"
            >
                Mensual
            </button>
            <button
                type="button"
                data-billing-toggle="yearly"
                class="pricing-toggle px-5 py-2 rounded-lg text-[13px] font-semibold transition-all text-zinc-400 hover:text-zinc-200"
            >
                Anual
            </button>
        </div>
        <p class="text-[12px] text-emerald-400 font-medium">
            <span data-yearly-badge class="hidden">Ahorra 2 meses con facturación anual en Pro</span>
            <span data-monthly-badge>Facturación flexible mes a mes</span>
        </p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 items-stretch">
        @foreach ($plans as $slug => $plan)
            @php
                $isPopular = ! empty($plan['popular']);
                $isLifetime = ($plan['billing_type'] ?? '') === 'lifetime';
                $isFree = ! empty($plan['is_free']);
            @endphp

            <div @class([
                'relative flex flex-col rounded-2xl p-8 gap-6 transition-colors',
                'bg-[#161616] border border-zinc-800/60 hover:border-zinc-700/60' => ! $isPopular,
                'bg-gradient-to-b from-violet-950/40 to-[#161616] border border-violet-500/30 shadow-xl shadow-violet-500/5' => $isPopular,
            ])>
                @if ($isPopular)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-r from-violet-600 to-fuchsia-600 text-[11px] font-bold text-white tracking-wide uppercase">
                        Más popular
                    </span>
                @endif

                <div class="flex flex-col gap-2">
                    <h3 class="text-xl font-bold text-white">{{ $plan['name'] }}</h3>
                    <p class="text-[13px] text-zinc-400 leading-relaxed min-h-[40px]">{{ $plan['description'] }}</p>
                </div>

                {{-- Price display --}}
                <div class="flex flex-col gap-1 min-h-[72px]">
                    @if ($isFree)
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold text-white tracking-tight">$0</span>
                            <span class="text-[13px] text-zinc-500">/ siempre</span>
                        </div>
                        <span class="text-[12px] text-zinc-500">Sin tarjeta de crédito</span>
                    @elseif ($isLifetime)
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold text-white tracking-tight">{{ $formatPrice($plan['price_lifetime_clp'] ?? null) }}</span>
                        </div>
                        <span class="text-[12px] text-zinc-500">Pago único · acceso de por vida</span>
                    @else
                        <div class="flex items-baseline gap-1">
                            <span
                                class="text-4xl font-extrabold text-white tracking-tight"
                                data-plan-price="{{ $slug }}"
                                data-monthly="{{ $plan['price_monthly_clp'] ?? 0 }}"
                                data-yearly="{{ $plan['price_yearly_clp'] ?? 0 }}"
                            >
                                {{ $formatPrice($plan['price_monthly_clp'] ?? 0) }}
                            </span>
                            <span class="text-[13px] text-zinc-500" data-plan-period="{{ $slug }}">/ mes</span>
                        </div>
                        <span class="text-[12px] text-zinc-500" data-plan-note="{{ $slug }}">Facturación mensual</span>
                    @endif
                </div>

                {{-- Limits summary --}}
                <ul class="flex flex-col gap-2.5 flex-1">
                    <li class="flex items-center gap-2.5 text-[13px] text-zinc-300">
                        <svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatLimit($plan['max_documents_per_month'] ?? null) }} documentos/mes
                    </li>
                    <li class="flex items-center gap-2.5 text-[13px] text-zinc-300">
                        <svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatLimit($plan['max_analyses_per_month'] ?? null) }} análisis/mes
                    </li>
                    <li class="flex items-center gap-2.5 text-[13px] text-zinc-300">
                        <svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatStorage($plan['max_storage_bytes'] ?? null) }} de almacenamiento
                    </li>
                    @foreach ($plan['highlights'] ?? [] as $highlight)
                        <li class="flex items-center gap-2.5 text-[13px] text-zinc-400">
                            <svg class="w-4 h-4 text-zinc-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $highlight }}
                        </li>
                    @endforeach
                </ul>

                @if ($isFree)
                    <a href="{{ route('login') }}" class="w-full text-center px-6 py-3.5 rounded-xl border border-zinc-700/60 hover:bg-zinc-800/30 text-[14px] font-bold text-zinc-200 transition-all">
                        Comenzar gratis
                    </a>
                @elseif ($isLifetime)
                    <a href="{{ route('login') }}" class="w-full text-center px-6 py-3.5 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[14px] font-bold text-white transition-all">
                        Obtener acceso Lifetime
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        @class([
                            'w-full text-center px-6 py-3.5 rounded-xl text-[14px] font-bold text-white transition-all',
                            'bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 shadow-lg shadow-violet-500/20' => $isPopular,
                            'bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700' => ! $isPopular,
                        ])
                    >
                        Elegir Pro
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('[data-billing-toggle]');
        const priceEls = document.querySelectorAll('[data-plan-price]');
        const periodEls = document.querySelectorAll('[data-plan-period]');
        const noteEls = document.querySelectorAll('[data-plan-note]');
        const yearlyBadge = document.querySelector('[data-yearly-badge]');
        const monthlyBadge = document.querySelector('[data-monthly-badge]');

        const formatClp = (value) => '$' + Number(value).toLocaleString('es-CL');

        const setBilling = (interval) => {
            const isYearly = interval === 'yearly';

            toggles.forEach((btn) => {
                const active = btn.dataset.billingToggle === interval;
                btn.classList.toggle('bg-violet-600', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('shadow-lg', active);
                btn.classList.toggle('shadow-violet-500/20', active);
                btn.classList.toggle('text-zinc-400', !active);
                btn.classList.toggle('hover:text-zinc-200', !active);
            });

            priceEls.forEach((el) => {
                const value = isYearly ? el.dataset.yearly : el.dataset.monthly;
                el.textContent = formatClp(value);
            });

            periodEls.forEach((el) => {
                el.textContent = isYearly ? '/ año' : '/ mes';
            });

            noteEls.forEach((el) => {
                el.textContent = isYearly ? 'Facturación anual (2 meses gratis)' : 'Facturación mensual';
            });

            yearlyBadge?.classList.toggle('hidden', !isYearly);
            monthlyBadge?.classList.toggle('hidden', isYearly);
        };

        toggles.forEach((btn) => {
            btn.addEventListener('click', () => setBilling(btn.dataset.billingToggle));
        });
    });
</script>
