@props([
    'currentProvider' => 'groq',
    'isPro' => false,
])

<div class="bg-[#222629] border-2 border-[#35393C] p-5 sm:p-6 flex flex-col gap-4 shadow-brutal">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#35393C] pb-3">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-mono font-bold text-[#A4D8FF] uppercase tracking-wider">Proveedor de Inteligencia Artificial</h3>
                <span class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider bg-[#0F1E36] text-[#A4D8FF] border border-[#A4D8FF]/40 shadow-brutal-sm">
                    Modo Pruebas / Selección Activa
                </span>
            </div>
            <p class="text-[12px] font-mono text-[#A4D8FF]/70">
                Selecciona el motor de inferencia con el que se procesará tu próximo documento.
            </p>
        </div>

        <span class="text-[11px] font-mono text-[#A4D8FF]/80 flex items-center gap-1.5 self-start sm:self-auto bg-[#15181B] px-2.5 py-1 border border-[#35393C]">
            <span class="w-2 h-2 rounded-full {{ $currentProvider === 'nvidia_nim' ? 'bg-[#1B4332]' : 'bg-[#A4D8FF]' }} animate-pulse"></span>
            Activo: <strong class="text-[#A4D8FF] font-bold">{{ $currentProvider === 'nvidia_nim' ? 'NVIDIA NIM' : 'Groq Cloud' }}</strong>
        </span>
    </div>

    <!-- Provider Cards / Buttons -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">

        <!-- Groq Provider Card -->
        <form action="{{ route('dashboard.provider') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" name="provider" value="groq">
            <button type="submit" class="w-full text-left p-4 border-2 transition-all duration-200 cursor-pointer flex flex-col gap-2 {{ $currentProvider === 'groq' ? 'bg-[#0F1E36] border-[#15181B] shadow-brutal text-[#A4D8FF]' : 'bg-[#15181B] border-[#35393C] hover:bg-[#162A45] text-[#A4D8FF]/80' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 bg-[#15181B] border border-[#A4D8FF]/40 flex items-center justify-center text-[#A4D8FF] font-mono font-black text-[11px]">
                            G
                        </div>
                        <span class="text-[13px] font-bold text-[#A4D8FF]">Groq Cloud</span>
                    </div>
                    @if($currentProvider === 'groq')
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold bg-[#A4D8FF] text-[#0F1E36] border border-[#0F1E36] uppercase tracking-wider">
                            Seleccionado
                        </span>
                    @else
                        <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/70 hover:underline">
                            Activar
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between text-[11px] font-mono text-[#A4D8FF]/80 mt-1">
                    <span>Modelo: <strong class="text-[#A4D8FF]">llama-3.3-70b-versatile</strong></span>
                    <span class="text-[#A4D8FF] font-bold">Ultrarrápido</span>
                </div>
            </button>
        </form>

        <!-- NVIDIA NIM Provider Card -->
        <form action="{{ route('dashboard.provider') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" name="provider" value="nvidia_nim">
            <button type="submit" class="w-full text-left p-4 border-2 transition-all duration-200 cursor-pointer flex flex-col gap-2 {{ $currentProvider === 'nvidia_nim' ? 'bg-[#0F1E36] border-[#15181B] shadow-brutal text-[#A4D8FF]' : 'bg-[#15181B] border-[#35393C] hover:bg-[#162A45] text-[#A4D8FF]/80' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 bg-[#15181B] border border-[#A4D8FF]/40 flex items-center justify-center text-[#A4D8FF] font-mono font-black text-[10px] tracking-tight">
                            NIM
                        </div>
                        <span class="text-[13px] font-bold text-[#A4D8FF]">NVIDIA NIM</span>
                    </div>
                    @if($currentProvider === 'nvidia_nim')
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold bg-[#A4D8FF] text-[#0F1E36] border border-[#0F1E36] uppercase tracking-wider">
                            Seleccionado
                        </span>
                    @else
                        <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/70 hover:underline">
                            Activar
                        </span>
                    @endif
                </div>

                <div class="flex items-center justify-between text-[11px] font-mono text-[#A4D8FF]/80 mt-1">
                    <span>Modelo: <strong class="text-[#A4D8FF]">llama-3.3-70b-instruct</strong></span>
                    <span class="text-[#A4D8FF] font-bold">Límite: 40 RPM</span>
                </div>
            </button>
        </form>

    </div>
</div>


