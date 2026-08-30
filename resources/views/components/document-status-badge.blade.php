@props(['status'])

@if($status === 'processed')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#1B4332] text-[#A4D8FF] border border-[#15181B] shadow-brutal-sm uppercase tracking-wider">
        <span class="w-1.5 h-1.5 bg-[#A4D8FF]"></span>
        Listo
    </span>
@elseif($status === 'processing')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#162A45] text-[#A4D8FF] border border-[#15181B] shadow-brutal-sm uppercase tracking-wider animate-pulse">
        <span class="w-1.5 h-1.5 bg-[#A4D8FF]"></span>
        Analizando...
    </span>
@elseif($status === 'failed')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#0F1E36] text-[#A4D8FF] border border-[#15181B] shadow-brutal-sm uppercase tracking-wider">
        <span class="w-1.5 h-1.5 bg-[#A4D8FF]"></span>
        Falló
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#15181B] text-[#A4D8FF]/80 border border-[#35393C] shadow-brutal-sm uppercase tracking-wider">
        <span class="w-1.5 h-1.5 bg-[#A4D8FF]/60 animate-pulse"></span>
        Pendiente
    </span>
@endif


