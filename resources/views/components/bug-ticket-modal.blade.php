@auth
<div 
    id="bug-ticket-modal" 
    class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-xs items-center justify-center p-4"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="bug-modal-title"
>
    <div class="bg-gunmetal-dark border-2 border-gunmetal w-full max-w-lg shadow-brutal flex flex-col gap-5 p-6 relative">
        {{-- Close button --}}
        <button 
            type="button" 
            onclick="closeBugTicketModal()" 
            class="absolute top-4 right-4 text-icy/50 hover:text-icy transition-colors cursor-pointer"
            aria-label="Cerrar modal"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-gunmetal pb-3">
            <div class="w-8 h-8 bg-amber-500/20 border border-amber-500/50 flex items-center justify-center text-amber-400 shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h2 id="bug-modal-title" class="text-lg font-serif font-bold text-icy">Reportar un Error</h2>
                <p class="text-[12px] font-mono text-icy/60">Envía información detallada sobre el fallo encontrado</p>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('tickets.store') }}" class="flex flex-col gap-4">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="bug-title" class="text-[12px] font-mono font-bold text-icy uppercase tracking-wider">
                    Título o asunto <span class="text-red-400">*</span>
                </label>
                <input 
                    type="text" 
                    id="bug-title" 
                    name="title" 
                    required 
                    placeholder="Ej. Error al procesar archivo PDF de contrato" 
                    class="w-full bg-navy-dark border-2 border-gunmetal focus:border-icy text-icy font-mono text-[13px] px-3.5 py-2 focus:outline-none"
                >
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="bug-description" class="text-[12px] font-mono font-bold text-icy uppercase tracking-wider">
                    Descripción del problema <span class="text-red-400">*</span>
                </label>
                <textarea 
                    id="bug-description" 
                    name="description" 
                    rows="4" 
                    required 
                    placeholder="Describe qué estabas haciendo y qué error apareció..." 
                    class="w-full bg-navy-dark border-2 border-gunmetal focus:border-icy text-icy font-mono text-[13px] px-3.5 py-2 focus:outline-none resize-y"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gunmetal">
                <button 
                    type="button" 
                    onclick="closeBugTicketModal()" 
                    class="px-4 py-2 bg-transparent hover:bg-navy-light text-icy/70 hover:text-icy font-mono text-[12px] uppercase tracking-wider transition-colors cursor-pointer"
                >
                    Cancelar
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2 bg-navy-dark hover:bg-navy-light border border-icy/60 text-icy font-mono font-bold text-[12px] uppercase tracking-wider shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px transition-all cursor-pointer"
                >
                    Enviar Reporte
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBugTicketModal() {
    const modal = document.getElementById('bug-ticket-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('bug-title')?.focus();
    }
}

function closeBugTicketModal() {
    const modal = document.getElementById('bug-ticket-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeBugTicketModal();
    }
});
</script>
@endauth
