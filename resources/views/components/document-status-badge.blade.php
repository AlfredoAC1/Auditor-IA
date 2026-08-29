@props(['status'])

@if($status === 'processed')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#2D4F3B] text-[#D5E8D4] border border-[#151311] shadow-brutal-sm uppercase tracking-wider">
        <span class="w-1.5 h-1.5 bg-[#D5E8D4]"></span>
        Listo
    </span>
@elseif($status === 'processing')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#B87326] text-[#FFE6CC] border border-[#151311] shadow-brutal-sm uppercase tracking-wider animate-pulse">
        <span class="w-1.5 h-1.5 bg-[#FFE6CC]"></span>
        Analizando...
    </span>
@elseif($status === 'failed')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#4B262F] text-[#F8CECC] border border-[#151311] shadow-brutal-sm uppercase tracking-wider">
        <span class="w-1.5 h-1.5 bg-[#F8CECC]"></span>
        Falló
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10.5px] font-mono font-bold bg-[#151311] text-[#EED3BA]/80 border border-[#4B262F] shadow-brutal-sm uppercase tracking-wider">
        <span class="w-1.5 h-1.5 bg-[#EED3BA]/60 animate-pulse"></span>
        Pendiente
    </span>
@endif

