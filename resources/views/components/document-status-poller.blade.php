@props([
    'pendingDocumentIds' => [],
])

@if(count($pendingDocumentIds) > 0)
<script>
(function () {
    const pendingIds = @json($pendingDocumentIds);
    const statusUrlTemplate = @json(route('dashboard.document.status', ['document' => '__ID__']));
    const pollIntervalMs = 2500;

    const badgeHtml = {
        processed: `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Listo</span>`,
        processing: `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 animate-pulse"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Analizando...</span>`,
        failed: `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20"><span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>Falló</span>`,
        pending: `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-zinc-500/10 text-zinc-400 border border-zinc-500/20"><span class="w-1.5 h-1.5 rounded-full bg-zinc-400 animate-pulse"></span>Pendiente</span>`,
    };

    let activeIds = [...pendingIds];
    let timer = null;

    function statusUrl(id) {
        return statusUrlTemplate.replace('__ID__', id);
    }

    async function pollDocument(id) {
        try {
            const response = await fetch(statusUrl(id), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) return;

            const data = await response.json();
            const statusCell = document.querySelector(`[data-document-status-cell="${id}"]`);
            const actionCell = document.querySelector(`[data-document-action-cell="${id}"]`);
            const row = document.querySelector(`[data-document-row="${id}"]`);

            if (statusCell && badgeHtml[data.status]) {
                statusCell.innerHTML = badgeHtml[data.status];
            }

            if (row) {
                row.dataset.documentStatus = data.status;
            }

            if (data.status === 'processed' && actionCell && data.analysis_url) {
                actionCell.innerHTML = `<a href="${data.analysis_url}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-[12px] font-bold text-white transition-colors">Ver reporte</a>`;
            }

            if (data.is_terminal) {
                activeIds = activeIds.filter((activeId) => activeId !== id);
            }
        } catch (error) {
            console.warn('Document status poll failed', id, error);
        }
    }

    function tick() {
        if (activeIds.length === 0) {
            if (timer) clearInterval(timer);
            return;
        }

        activeIds.forEach((id) => pollDocument(id));
    }

    if (activeIds.length > 0) {
        tick();
        timer = setInterval(tick, pollIntervalMs);
    }
})();
</script>
@endif
