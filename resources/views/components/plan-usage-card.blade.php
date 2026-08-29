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

<section id="plan-usage-card" class="bg-[#222629] border-2 border-[#35393C] p-6 flex flex-col gap-5 shadow-brutal">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#35393C] pb-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-serif font-bold text-[#A4D8FF] tracking-tight">Tu plan</h2>
                <span @class([
                    'inline-flex px-2.5 py-0.5 text-[11px] font-mono font-bold uppercase tracking-wide border shadow-brutal-sm',
                    'bg-[#15181B] text-[#A4D8FF]/70 border-[#35393C]' => $planSlug === 'free',
                    'bg-[#0F1E36] text-[#A4D8FF] border-[#A4D8FF]/40' => $planSlug === 'pro',
                    'bg-[#A4D8FF] text-[#0F1E36] border-[#0F1E36]' => $planSlug === 'lifetime',
                ])>
                    {{ $planName }}
                </span>
            </div>
            <p class="text-[13px] font-mono text-[#A4D8FF]/70">Uso del periodo actual · se renueva cada mes</p>
        </div>
        @if ($planSlug !== 'lifetime')
            <a href="{{ route('landing') }}#planes" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all shrink-0 uppercase tracking-wider">
                <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Mejorar plan
            </a>
        @endif
    </div>

    <div class="grid sm:grid-cols-3 gap-5">
        {{-- Documents --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between text-[12px] font-mono">
                <span class="text-[#A4D8FF]/70 uppercase">Documentos</span>
                <span class="text-[#A4D8FF] font-bold">
                    {{ $documentsUsed }} / {{ $limitLabel($documentsLimit) }}
                </span>
            </div>
            <div class="h-3 border-2 border-[#15181B] bg-[#15181B]">
                <div
                    class="h-full bg-[#0F1E36] border-r border-[#A4D8FF]/60 transition-all duration-300"
                    style="width: {{ $percent($documentsUsed, $documentsLimit) }}%"
                ></div>
            </div>
        </div>

        {{-- Analyses --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between text-[12px] font-mono">
                <span class="text-[#A4D8FF]/70 uppercase">Análisis IA</span>
                <span class="text-[#A4D8FF] font-bold">
                    {{ $analysesUsed }} / {{ $limitLabel($analysesLimit) }}
                </span>
            </div>
            <div class="h-3 border-2 border-[#15181B] bg-[#15181B]">
                <div
                    class="h-full bg-[#0F1E36] border-r border-[#A4D8FF]/60 transition-all duration-300"
                    style="width: {{ $percent($analysesUsed, $analysesLimit) }}%"
                ></div>
            </div>
        </div>

        {{-- Storage --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between text-[12px] font-mono">
                <span class="text-[#A4D8FF]/70 uppercase">Almacenamiento</span>
                <span class="text-[#A4D8FF] font-bold">
                    {{ $formatStorage($storageUsedBytes) }} / {{ $storageLimitBytes === null ? 'Ilimitado' : $formatStorage($storageLimitBytes) }}
                </span>
            </div>
            <div class="h-3 border-2 border-[#15181B] bg-[#15181B]">
                <div
                    class="h-full bg-[#0F1E36] border-r border-[#A4D8FF]/60 transition-all duration-300"
                    style="width: {{ $percent($storageUsedBytes, $storageLimitBytes) }}%"
                ></div>
            </div>
        </div>
    </div>
</section>


