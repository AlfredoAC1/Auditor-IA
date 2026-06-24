@props([
    'documents',
])

<div class="overflow-x-auto bg-[#161616]/30 border border-zinc-800/40 rounded-2xl">
    <table class="w-full text-left border-collapse" id="documents-table">
        <thead>
            <tr class="border-b border-zinc-850/60 text-[12px] font-bold text-zinc-500 uppercase tracking-wider">
                <th class="px-6 py-4">Documento</th>
                <th class="px-6 py-4">Fecha</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-850/40 text-[13px] text-zinc-300">
            @foreach($documents as $doc)
                <tr
                    class="hover:bg-zinc-900/20 transition-colors"
                    data-document-row="{{ $doc->id }}"
                    data-document-status="{{ $doc->status->value }}"
                >
                    <td class="px-6 py-4.5 font-medium text-white max-w-xs truncate">
                        {{ $doc->original_filename }}
                    </td>
                    <td class="px-6 py-4.5 text-zinc-500">
                        {{ $doc->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4.5" data-document-status-cell="{{ $doc->id }}">
                        @include('components.document-status-badge', ['status' => $doc->status->value])
                    </td>
                    <td class="px-6 py-4.5 text-right" data-document-action-cell="{{ $doc->id }}">
                        @if($doc->status->value === 'processed')
                            <a href="{{ route('analysis.show', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-[12px] font-bold text-white transition-colors">
                                Ver reporte
                            </a>
                        @else
                            <span class="text-zinc-600 font-semibold text-[12px]">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
