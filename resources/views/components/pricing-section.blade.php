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
        <h2 class="text-2xl md:text-3xl font-serif font-bold text-[#EED3BA] tracking-tight mb-3">Planes y precios</h2>
        <p class="font-mono text-[#EED3BA]/70 text-[15px] max-w-xl mx-auto">
            Elige el plan que se adapte a tu volumen de contratos. Cambia o cancela cuando quieras.
        </p>
    </div>

    {{-- Billing toggle --}}
    <div class="flex flex-col items-center gap-3 mb-10" data-pricing-root>
        <div class="inline-flex items-center gap-1 p-1 bg-[#151311] border-2 border-[#4B262F] shadow-brutal-sm">
            <button
                type="button"
                data-billing-toggle="monthly"
                class="pricing-toggle px-5 py-2 text-[13px] font-mono font-bold transition-all bg-[#4B262F] text-[#EED3BA] border border-[#EED3BA]/40"
            >
                Mensual
            </button>
            <button
                type="button"
                data-billing-toggle="yearly"
                class="pricing-toggle px-5 py-2 text-[13px] font-mono font-bold transition-all text-[#EED3BA]/60 hover:text-[#EED3BA]"
            >
                Anual
            </button>
        </div>
        <p class="text-[12px] font-mono text-[#D5E8D4]">
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
                'relative flex flex-col p-8 gap-6 transition-all bg-[#2A171B] border-2 border-[#4B262F] shadow-brutal',
                'border-[#EED3BA]/60' => $isPopular,
            ])>
                @if ($isPopular)
                    <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 bg-[#4B262F] text-[11px] font-mono font-bold text-[#EED3BA] tracking-wider uppercase border border-[#EED3BA]/40 shadow-brutal-sm">
                        Más popular
                    </span>
                @endif

                <div class="flex flex-col gap-2">
                    <h3 class="text-xl font-serif font-bold text-[#EED3BA]">{{ $plan['name'] }}</h3>
                    <p class="text-[13px] font-sans text-[#EED3BA]/80 leading-relaxed min-h-[40px]">{{ $plan['description'] }}</p>
                </div>

                {{-- Price display --}}
                <div class="flex flex-col gap-1 min-h-[72px]">
                    @if ($isFree)
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-serif font-extrabold text-[#EED3BA] tracking-tight">$0</span>
                            <span class="text-[13px] font-mono text-[#EED3BA]/70">/ siempre</span>
                        </div>
                        <span class="text-[12px] font-mono text-[#EED3BA]/60">Sin tarjeta de crédito</span>
                    @elseif ($isLifetime)
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-serif font-extrabold text-[#EED3BA] tracking-tight">{{ $formatPrice($plan['price_lifetime_clp'] ?? null) }}</span>
                        </div>
                        <span class="text-[12px] font-mono text-[#EED3BA]/60">Pago único · acceso de por vida</span>
                    @else
                        <div class="flex items-baseline gap-1">
                            <span
                                class="text-4xl font-serif font-extrabold text-[#EED3BA] tracking-tight"
                                data-plan-price="{{ $slug }}"
                                data-monthly="{{ $plan['price_monthly_clp'] ?? 0 }}"
                                data-yearly="{{ $plan['price_yearly_clp'] ?? 0 }}"
                            >
                                {{ $formatPrice($plan['price_monthly_clp'] ?? 0) }}
                            </span>
                            <span class="text-[13px] font-mono text-[#EED3BA]/70" data-plan-period="{{ $slug }}">/ mes</span>
                        </div>
                        <span class="text-[12px] font-mono text-[#EED3BA]/60" data-plan-note="{{ $slug }}">Facturación mensual</span>
                    @endif
                </div>

                {{-- Limits summary --}}
                <ul class="flex flex-col gap-2.5 flex-1 font-mono text-[13px]">
                    <li class="flex items-center gap-2.5 text-[#EED3BA]">
                        <svg class="w-4 h-4 text-[#EED3BA] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatLimit($plan['max_documents_per_month'] ?? null) }} documentos/mes
                    </li>
                    <li class="flex items-center gap-2.5 text-[#EED3BA]">
                        <svg class="w-4 h-4 text-[#EED3BA] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatLimit($plan['max_analyses_per_month'] ?? null) }} análisis/mes
                    </li>
                    <li class="flex items-center gap-2.5 text-[#EED3BA]">
                        <svg class="w-4 h-4 text-[#EED3BA] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatStorage($plan['max_storage_bytes'] ?? null) }} de almacenamiento
                    </li>
                    @foreach ($plan['highlights'] ?? [] as $highlight)
                        <li class="flex items-center gap-2.5 text-[#EED3BA]/70">
                            <svg class="w-4 h-4 text-[#EED3BA]/50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $highlight }}
                        </li>
                    @endforeach
                </ul>

                @if ($isFree)
                    <a href="{{ route('login') }}" class="w-full text-center px-6 py-3.5 bg-[#151311] border-2 border-[#4B262F] hover:bg-[#2A171B] text-[14px] font-mono font-bold text-[#EED3BA] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                        Comenzar gratis
                    </a>
                @elseif ($isLifetime)
                    <a href="{{ route('login') }}" class="w-full text-center px-6 py-3.5 bg-[#4B262F] hover:bg-[#5C2F3A] border border-[#EED3BA]/40 text-[14px] font-mono font-bold text-[#EED3BA] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                        Obtener acceso Lifetime
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="w-full text-center px-6 py-3.5 bg-[#4B262F] hover:bg-[#5C2F3A] border border-[#EED3BA]/40 text-[14px] font-mono font-bold text-[#EED3BA] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider"
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
                btn.classList.toggle('bg-[#4B262F]', active);
                btn.classList.toggle('text-[#EED3BA]', active);
                btn.classList.toggle('border', active);
                btn.classList.toggle('border-[#EED3BA]/40', active);
                btn.classList.toggle('text-[#EED3BA]/60', !active);
                btn.classList.toggle('hover:text-[#EED3BA]', !active);
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

