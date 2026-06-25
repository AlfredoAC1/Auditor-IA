@props([
    'isPro' => false,
])

<form id="upload-zone" action="{{ route('dashboard.upload') }}" method="POST" enctype="multipart/form-data" class="bg-[#161616]/40 border-2 border-dashed {{ $isPro ? 'border-violet-500/30 hover:border-violet-500/50' : 'border-zinc-850 hover:border-zinc-750' }} rounded-2xl p-8 md:p-12 flex flex-col items-center justify-center gap-6 transition-colors duration-300">
    @csrf
    <input type="file" name="file" id="file-input" class="hidden" accept=".pdf,.docx" onchange="this.form.submit()">

    <div onclick="document.getElementById('file-input').click()" class="w-14 h-14 rounded-2xl {{ $isPro ? 'bg-violet-600/20 border-violet-500/30' : 'bg-violet-600/10 border-violet-500/20' }} border flex items-center justify-center text-violet-400 shadow-inner cursor-pointer hover:bg-violet-600/20 transition-colors">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
    </div>

    <div class="flex flex-col gap-2 text-center">
        <h2 class="text-lg font-bold text-white tracking-tight">Arrastra tu documento aquí</h2>
        <p class="text-[13px] text-zinc-400 leading-relaxed">
            PDF o DOCX · hasta 50MB · en español <br>
            @if($isPro)
                <span class="text-violet-300">Procesamiento prioritario · sin worker externo</span>
            @else
                <span class="text-zinc-500">El análisis comienza automáticamente al subir</span>
            @endif
        </p>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3">
        <button type="button" onclick="document.getElementById('file-input').click()" class="px-5 py-2.5 rounded-xl border border-zinc-700/60 hover:bg-zinc-800/30 text-[13px] font-semibold text-zinc-300 flex items-center gap-2 transition-colors cursor-pointer">
            Explorar archivos
        </button>
        <button type="button" onclick="document.getElementById('file-input').click()" class="px-5 py-2.5 rounded-xl {{ $isPro ? 'bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500' : 'bg-violet-600 hover:bg-violet-500' }} text-[13px] font-bold text-white flex items-center gap-2 shadow-lg shadow-violet-500/10 transition-all duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Subir y analizar
        </button>
    </div>
</form>
