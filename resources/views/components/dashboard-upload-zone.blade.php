@props([
    'isPro' => false,
    'selectedModel' => null,
    'selectedModelSlug' => null,
    'currentProvider' => null,
])

@php
    $modelSlug = $selectedModelSlug ?? $selectedModel?->slug ?? 'cerebras-gemma4';
    $provider = $selectedModel?->provider ?? $currentProvider ?? 'cerebras';
    $isCerebras = $provider === 'cerebras' || str_contains($modelSlug, 'gemma4') || str_contains($modelSlug, 'cerebras');
@endphp

<form id="upload-zone" action="{{ route('dashboard.upload') }}" method="POST" enctype="multipart/form-data" class="bg-[#222629] border-2 border-dashed border-[#35393C] hover:border-[#A4D8FF]/80 p-8 md:p-12 flex flex-col items-center justify-center gap-6 shadow-brutal transition-colors">
    @csrf
    <input type="hidden" name="model_slug" id="selected-model-input" value="{{ $modelSlug }}">
    <input type="hidden" name="llm_provider" value="{{ $provider }}">
    <input type="file" name="file" id="file-input" class="hidden" accept=".pdf,.docx" onchange="this.form.submit()">

    <div onclick="document.getElementById('file-input').click()" class="w-14 h-14 bg-[#0F1E36] border-2 border-[#15181B] flex items-center justify-center text-[#A4D8FF] shadow-brutal-sm cursor-pointer hover:translate-x-[-1px] hover:translate-y-[-1px] transition-all">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
    </div>

    <div class="flex flex-col gap-2 text-center">
        <h2 class="text-xl font-serif font-bold text-[#A4D8FF] tracking-tight">Arrastra tu documento aquí</h2>
        <p class="text-[13px] font-mono text-[#A4D8FF]/70 leading-relaxed">
            PDF o DOCX · hasta 50MB · marco legal chileno vigente <br>
            <span class="inline-flex items-center gap-2 mt-2 text-xs font-mono text-[#A4D8FF] bg-[#15181B] px-3.5 py-1.5 border border-[#35393C] shadow-brutal-sm">
                <span class="w-2 h-2 rounded-full {{ $isCerebras ? 'bg-[#F5C87A]' : 'bg-[#A4D8FF]' }} animate-pulse"></span>
                Modelo activo: <strong class="font-bold {{ $isCerebras ? 'text-[#F5C87A]' : 'text-[#A4D8FF]' }}">{{ $selectedModel?->display_name ?? 'Gemma 4 31B (Preview)' }}</strong>
                <span class="text-icy/40">·</span>
                <span class="text-icy/70">{{ $isCerebras ? 'Cerebras AI (40k Tokens)' : ($provider === 'nvidia_nim' ? 'NVIDIA NIM' : 'Inferencia Acelerada') }}</span>
            </span>
        </p>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3">
        <button type="button" onclick="document.getElementById('file-input').click()" class="px-5 py-2.5 bg-[#15181B] border border-[#35393C] text-[13px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:bg-[#162A45] transition-colors cursor-pointer uppercase tracking-wider">
            Explorar archivos
        </button>
        <button type="button" onclick="document.getElementById('file-input').click()" class="px-5 py-2.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[13px] font-mono font-bold text-[#A4D8FF] flex items-center gap-2 shadow-brutal hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
            <svg class="w-4 h-4 text-[#A4D8FF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Subir y analizar
        </button>
    </div>
</form>


