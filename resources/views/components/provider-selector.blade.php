@props([
    'currentProvider' => 'groq',
    'isPro' => false,
])

<div class="bg-[#161616] border {{ $isPro ? 'border-violet-500/20' : 'border-zinc-800/60' }} rounded-2xl p-5 sm:p-6 flex flex-col gap-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-white tracking-tight">Proveedor de Inteligencia Artificial</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-violet-600/10 text-violet-400 border border-violet-500/20">
                    Modo Pruebas / Selección Activa
                </span>
            </div>
            <p class="text-[12px] text-zinc-400">
                Selecciona el motor de inferencia con el que se procesará tu próximo documento.
            </p>
        </div>

        <span class="text-[11px] text-zinc-500 flex items-center gap-1.5 self-start sm:self-auto">
            <span class="w-2 h-2 rounded-full {{ $currentProvider === 'nvidia_nim' ? 'bg-emerald-400' : 'bg-amber-400' }} animate-pulse"></span>
            Activo: <strong class="text-white font-semibold">{{ $currentProvider === 'nvidia_nim' ? 'NVIDIA NIM' : 'Groq Cloud' }}</strong>
        </span>
    </div>

    <!-- Provider Cards / Buttons -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">

        <!-- Groq Provider Card -->
        <form action="{{ route('dashboard.provider') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" name="provider" value="groq">
            <button type="submit" class="w-full text-left p-4 rounded-xl border transition-all duration-200 cursor-pointer flex flex-col gap-2 {{ $currentProvider === 'groq' ? 'bg-violet-600/10 border-violet-500/50 shadow-lg shadow-violet-500/5 ring-1 ring-violet-500/30' : 'bg-zinc-900/40 border-zinc-800/60 hover:border-zinc-700 hover:bg-zinc-900/70' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-400 font-black text-[11px]">
                            G
                        </div>
                        <span class="text-[13px] font-bold text-white">Groq Cloud</span>
                    </div>
                    @if($currentProvider === 'groq')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-violet-500 text-white uppercase tracking-wider">
                            Seleccionado
                        </span>
                    @else
                        <span class="text-[11px] font-medium text-zinc-500 group-hover:text-zinc-300">
                            Activar
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-1">
                    <span>Modelo: <strong class="text-zinc-300">llama-3.3-70b-versatile</strong></span>
                    <span class="text-emerald-400 font-medium">Ultrarrápido</span>
                </div>
            </button>
        </form>

        <!-- NVIDIA NIM Provider Card -->
        <form action="{{ route('dashboard.provider') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" name="provider" value="nvidia_nim">
            <button type="submit" class="w-full text-left p-4 rounded-xl border transition-all duration-200 cursor-pointer flex flex-col gap-2 {{ $currentProvider === 'nvidia_nim' ? 'bg-emerald-500/10 border-emerald-500/50 shadow-lg shadow-emerald-500/5 ring-1 ring-emerald-500/30' : 'bg-zinc-900/40 border-zinc-800/60 hover:border-zinc-700 hover:bg-zinc-900/70' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-black text-[10px] tracking-tight">
                            NIM
                        </div>
                        <span class="text-[13px] font-bold text-white">NVIDIA NIM</span>
                    </div>
                    @if($currentProvider === 'nvidia_nim')
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500 text-black uppercase tracking-wider">
                            Seleccionado
                        </span>
                    @else
                        <span class="text-[11px] font-medium text-zinc-500 group-hover:text-zinc-300">
                            Activar
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-1">
                    <span>Modelo: <strong class="text-zinc-300">llama-3.3-70b-instruct</strong></span>
                    <span class="text-emerald-400 font-medium">Límite: 40 RPM</span>
                </div>
            </button>
        </form>

    </div>
</div>
