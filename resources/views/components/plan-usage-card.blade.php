@props([
    'planName',
    'planSlug',
    'documentsUsed',
    'documentsLimit',
    'analysesUsed',
    'analysesLimit',
    'storageUsedBytes',
    'storageLimitBytes',
])

@php
    $percent = fn (?int $used, ?int $limit): float => ($limit === null || $limit === 0) ? 0 : min(100, ($used / $limit) * 100);
    $formatStorage = function (int $bytes): string {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1, ',', '.') . ' GB';
        }

        return number_format($bytes / 1048576, 0, ',', '.') . ' MB';
    };
    $limitLabel = fn (?int $limit, string $suffix = ''): string => $limit === null ? 'Ilimitado' : number_format($limit, 0, ',', '.') . $suffix;
@endphp

<section id="plan-usage-card" class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-6 flex flex-col gap-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-white tracking-tight">Tu plan</h2>
                <span @class([
                    'inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide',
                    'bg-zinc-800 text-zinc-400' => $planSlug === 'free',
                    'bg-violet-500/10 text-violet-300 border border-violet-500/20' => $planSlug === 'pro',
                    'bg-amber-500/10 text-amber-300 border border-amber-500/20' => $planSlug === 'lifetime',
                ])>
                    {{ $planName }}
                </span>
            </div>
            <p class="text-[13px] text-zinc-500">Uso del periodo actual · se renueva cada mes</p>
        </div>
        @if ($planSlug !== 'lifetime')
            <a href="{{ route('landing') }}#planes" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600/10 hover:bg-violet-600/20 border border-violet-500/20 text-[13px] font-bold text-violet-300 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Mejorar plan
            </a>
        @endif
    </div>

    <div class="grid sm:grid-cols-3 gap-5">
        {{-- Documents --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between text-[12px]">
                <span class="text-zinc-400 font-medium">Documentos</span>
                <span class="text-zinc-300 font-semibold">
                    {{ $documentsUsed }} / {{ $limitLabel($documentsLimit) }}
                </span>
            </div>
            <div class="h-2 rounded-full bg-zinc-900 overflow-hidden">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-violet-600 to-fuchsia-600 transition-all duration-500"
                    style="width: {{ $percent($documentsUsed, $documentsLimit) }}%"
                ></div>
            </div>
        </div>

        {{-- Analyses --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between text-[12px]">
                <span class="text-zinc-400 font-medium">Análisis IA</span>
                <span class="text-zinc-300 font-semibold">
                    {{ $analysesUsed }} / {{ $limitLabel($analysesLimit) }}
                </span>
            </div>
            <div class="h-2 rounded-full bg-zinc-900 overflow-hidden">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-violet-600 to-fuchsia-600 transition-all duration-500"
                    style="width: {{ $percent($analysesUsed, $analysesLimit) }}%"
                ></div>
            </div>
        </div>

        {{-- Storage --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between text-[12px]">
                <span class="text-zinc-400 font-medium">Almacenamiento</span>
                <span class="text-zinc-300 font-semibold">
                    {{ $formatStorage($storageUsedBytes) }} / {{ $storageLimitBytes === null ? 'Ilimitado' : $formatStorage($storageLimitBytes) }}
                </span>
            </div>
            <div class="h-2 rounded-full bg-zinc-900 overflow-hidden">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-violet-600 to-fuchsia-600 transition-all duration-500"
                    style="width: {{ $percent($storageUsedBytes, $storageLimitBytes) }}%"
                ></div>
            </div>
        </div>
    </div>
</section>
