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
        <h2 class="text-2xl md:text-3xl font-serif font-bold text-[#A4D8FF] tracking-tight mb-3">Planes y precios</h2>
        <p class="font-mono text-[#A4D8FF]/70 text-[15px] max-w-xl mx-auto">
            Elige el plan que se adapte a tu volumen de contratos. Cambia o cancela cuando quieras.
        </p>
    </div>

    {{-- Billing toggle --}}
    <div class="flex flex-col items-center gap-3 mb-10" data-pricing-root>
        <div class="inline-flex items-center gap-1 p-1 bg-[#15181B] border-2 border-[#35393C] shadow-brutal-sm">
            <button
                type="button"
                data-billing-toggle="monthly"
                class="pricing-toggle px-5 py-2 text-[13px] font-mono font-bold transition-all bg-[#0F1E36] text-[#A4D8FF] border border-[#A4D8FF]/40"
            >
                Mensual
            </button>
            <button
                type="button"
                data-billing-toggle="yearly"
                class="pricing-toggle px-5 py-2 text-[13px] font-mono font-bold transition-all text-[#A4D8FF]/60 hover:text-[#A4D8FF]"
            >
                Anual
            </button>
        </div>
        <p class="text-[12px] font-mono text-[#A4D8FF]">
            <span data-yearly-badge class="hidden">Ahorra 2 meses con facturación anual en Pro</span>
            <span data-monthly-badge>Facturación flexible mes a mes</span>
        </p>
    </div>

    {{-- Explanation Card: How AI Credits work --}}
    <div class="bg-[#222629] border-2 border-[#35393C] p-6 md:p-8 shadow-brutal mb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#35393C] pb-5 mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-[#0F1E36] border border-[#A4D8FF]/40 text-[#A4D8FF] shrink-0">
                    <svg class="w-6 h-6 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-serif font-bold text-[#A4D8FF] tracking-tight">¿Cómo funcionan los Créditos IA en Auditor-IA?</h3>
                    <p class="text-[13px] font-mono text-[#A4D8FF]/70">Cada análisis consume créditos según la cantidad de tokens (texto) y el multiplicador del modelo de IA que elijas.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#0F1E36] border border-[#A4D8FF]/40 text-[11px] font-mono font-bold text-[#A4D8FF] uppercase tracking-wider shrink-0 self-start md:self-auto">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Sistema de Créditos Flexibles
            </span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-[#15181B] border border-[#35393C] p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-mono font-bold text-[#A4D8FF]">Modelo Ligero</span>
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF] bg-[#0F1E36] px-2 py-0.5 border border-[#A4D8FF]/30">1.0x peso</span>
                </div>
                <p class="text-[12px] font-sans text-[#A4D8FF]/70">Modelo liviano para revisiones rápidas y resúmenes generales de contratos.</p>
            </div>
            <div class="bg-[#15181B] border border-[#35393C] p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-mono font-bold text-[#A4D8FF]">Modelo Estándar</span>
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF] bg-[#0F1E36] px-2 py-0.5 border border-[#A4D8FF]/30">2.0x peso</span>
                </div>
                <p class="text-[12px] font-sans text-[#A4D8FF]/70">Mayor detalle y precisión en análisis de cláusulas y obligaciones.</p>
            </div>
            <div class="bg-[#15181B] border border-[#35393C] p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-mono font-bold text-[#A4D8FF]">Modelo Avanzado</span>
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF] bg-[#0F1E36] px-2 py-0.5 border border-[#A4D8FF]/30">3.0x peso</span>
                </div>
                <p class="text-[12px] font-sans text-[#A4D8FF]/70">Auditoría profunda de riesgos legales y detección de ambigüedades.</p>
            </div>
            <div class="bg-[#15181B] border border-[#35393C] p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-mono font-bold text-[#A4D8FF]">Modelo Premium</span>
                    <span class="text-[11px] font-mono font-bold text-[#A4D8FF] bg-[#0F1E36] px-2 py-0.5 border border-[#A4D8FF]/30">4.0x peso</span>
                </div>
                <p class="text-[12px] font-sans text-[#A4D8FF]/70">Máxima potencia de razonamiento jurídico para contratos complejos.</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-[12px] font-mono text-[#A4D8FF]/80 pt-4 border-t border-[#35393C]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#A4D8FF]/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><strong>Renovación de créditos:</strong> Semanal en Free, mensual en Pro y mensual de por vida en Lifetime.</span>
            </div>
            <span class="text-[11px] text-[#A4D8FF]/60">Fórmula: ((Tokens entrada + salida) / 175) × peso del modelo.</span>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6 items-stretch">
        @foreach ($plans as $slug => $plan)
            @php
                $isPopular = ! empty($plan['popular']);
                $isLifetime = ($plan['billing_type'] ?? '') === 'lifetime';
                $isFree = ! empty($plan['is_free']);
                $isLimitedOffer = ! empty($plan['limited_offer']);
            @endphp

            <div @class([
                'relative flex flex-col p-8 gap-6 transition-all bg-[#222629] border-2 border-[#35393C] shadow-brutal',
                'border-[#A4D8FF]/60' => $isPopular,
            ])>
                @if ($isPopular)
                    <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 bg-[#0F1E36] text-[11px] font-mono font-bold text-[#A4D8FF] tracking-wider uppercase border border-[#A4D8FF]/40 shadow-brutal-sm">
                        Más popular
                    </span>
                @endif

                <div class="flex flex-col gap-2">
                    <h3 class="text-xl font-serif font-bold text-[#A4D8FF]">{{ $plan['name'] }}</h3>
                    <p class="text-[13px] font-sans text-[#A4D8FF]/80 leading-relaxed min-h-[40px]">{{ $plan['description'] }}</p>
                </div>

                {{-- Price display --}}
                <div class="flex flex-col gap-1 min-h-[72px]">
                    @if ($isFree)
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-serif font-extrabold text-[#A4D8FF] tracking-tight">$0</span>
                            <span class="text-[13px] font-mono text-[#A4D8FF]/70">/ siempre</span>
                        </div>
                        <span class="text-[12px] font-mono text-[#A4D8FF]/60">Sin tarjeta de crédito</span>
                    @elseif ($isLifetime)
                        <div class="flex flex-col gap-1">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="text-4xl font-serif font-extrabold text-[#A4D8FF] tracking-tight">{{ $formatPrice($plan['price_lifetime_clp'] ?? null) }}</span>
                                @if ($isLimitedOffer)
                                    <span class="inline-block px-2 py-0.5 bg-amber-500/20 border border-amber-400/40 text-amber-300 text-[11px] font-mono font-bold uppercase tracking-wider animate-pulse">
                                        Oferta Limitada
                                    </span>
                                @endif
                            </div>
                            <span class="text-[12px] font-mono text-[#A4D8FF]/60">Pago único · acceso de por vida</span>
                        </div>
                    @else
                        <div class="flex items-baseline gap-1">
                            <span
                                class="text-4xl font-serif font-extrabold text-[#A4D8FF] tracking-tight"
                                data-plan-price="{{ $slug }}"
                                data-monthly="{{ $plan['price_monthly_clp'] ?? 0 }}"
                                data-yearly="{{ $plan['price_yearly_clp'] ?? 0 }}"
                            >
                                {{ $formatPrice($plan['price_monthly_clp'] ?? 0) }}
                            </span>
                            <span class="text-[13px] font-mono text-[#A4D8FF]/70" data-plan-period="{{ $slug }}">/ mes</span>
                        </div>
                        <span class="text-[12px] font-mono text-[#A4D8FF]/60" data-plan-note="{{ $slug }}">Facturación mensual</span>
                    @endif
                </div>

                {{-- Limits summary --}}
                <ul class="flex flex-col gap-2.5 flex-1 font-mono text-[13px]">
                    <li class="flex items-center gap-2.5 text-[#A4D8FF]">
                        <svg class="w-4 h-4 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatLimit($plan['max_documents_per_month'] ?? null) }} documentos/mes
                    </li>
                    <li class="flex items-center gap-2.5 text-[#A4D8FF]">
                        <svg class="w-4 h-4 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        @if (($plan['ai_credits_quota'] ?? null) === null)
                            Créditos IA ilimitados
                        @else
                            {{ number_format($plan['ai_credits_quota'], 0, ',', '.') }} créditos IA / {{ ($plan['credit_refresh_interval'] ?? '') === 'weekly' ? 'semana' : 'mes' }}
                        @endif
                    </li>
                    <li class="flex items-center gap-2.5 text-[#A4D8FF]">
                        <svg class="w-4 h-4 text-[#A4D8FF] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $formatStorage($plan['max_storage_bytes'] ?? null) }} de almacenamiento
                    </li>
                    @foreach ($plan['highlights'] ?? [] as $highlight)
                        <li class="flex items-center gap-2.5 text-[#A4D8FF]/70">
                            <svg class="w-4 h-4 text-[#A4D8FF]/50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $highlight }}
                        </li>
                    @endforeach
                </ul>

                @php $planCtaUrl = ($isStaticDemo ?? false) ? route('demo.dashboard') : route('login'); @endphp
                @if ($isFree)
                    <a href="{{ $planCtaUrl }}" class="w-full text-center px-6 py-3.5 bg-[#15181B] border-2 border-[#35393C] hover:bg-[#162A45] text-[14px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                        {{ ($isStaticDemo ?? false) ? 'Probar en Demo' : 'Comenzar gratis' }}
                    </a>
                @elseif ($isLifetime)
                    <a href="{{ $planCtaUrl }}" class="w-full text-center px-6 py-3.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[14px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                        {{ ($isStaticDemo ?? false) ? 'Explorar Demo Pro' : 'Obtener acceso Lifetime' }}
                    </a>
                @else
                    <a
                        href="{{ $planCtaUrl }}"
                        class="w-full text-center px-6 py-3.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[14px] font-mono font-bold text-[#A4D8FF] shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider"
                    >
                        {{ ($isStaticDemo ?? false) ? 'Ver Demo Pro' : 'Elegir Pro' }}
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
                btn.classList.toggle('bg-[#0F1E36]', active);
                btn.classList.toggle('text-[#A4D8FF]', active);
                btn.classList.toggle('border', active);
                btn.classList.toggle('border-[#A4D8FF]/40', active);
                btn.classList.toggle('text-[#A4D8FF]/60', !active);
                btn.classList.toggle('hover:text-[#A4D8FF]', !active);
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


