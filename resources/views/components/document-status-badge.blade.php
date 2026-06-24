@props(['status'])

@if($status === 'processed')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
        Listo
    </span>
@elseif($status === 'processing')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
        Analizando...
    </span>
@elseif($status === 'failed')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
        Falló
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-zinc-500/10 text-zinc-400 border border-zinc-500/20">
        <span class="w-1.5 h-1.5 rounded-full bg-zinc-400 animate-pulse"></span>
        Pendiente
    </span>
@endif
