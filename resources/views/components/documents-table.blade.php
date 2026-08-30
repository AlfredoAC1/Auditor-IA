@props([
    'documents',
])

<div class="overflow-x-auto bg-[#222629] border-2 border-[#35393C] shadow-brutal">
    <table class="w-full text-left border-collapse" id="documents-table">
        <thead>
            <tr class="border-b-2 border-[#35393C] text-[12px] font-mono font-bold text-[#A4D8FF] uppercase tracking-wider bg-[#15181B]">
                <th class="px-6 py-4">Documento</th>
                <th class="px-6 py-4">Fecha</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#35393C]/40 text-[13px] font-mono text-[#A4D8FF]">
            @foreach($documents as $doc)
                <tr
                    class="hover:bg-[#15181B]/60 transition-colors"
                    data-document-row="{{ $doc->id }}"
                    data-document-status="{{ $doc->status->value }}"
                >
                    <td class="px-6 py-4.5 font-bold text-[#A4D8FF] max-w-xs truncate">
                        {{ $doc->original_filename }}
                    </td>
                    <td class="px-6 py-4.5 text-[#A4D8FF]/70">
                        {{ $doc->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4.5" data-document-status-cell="{{ $doc->id }}">
                        @include('components.document-status-badge', ['status' => $doc->status->value])
                    </td>
                    <td class="px-6 py-4.5 text-right flex items-center justify-end gap-2" data-document-action-cell="{{ $doc->id }}">
                        @if($doc->status->value === 'processed')
                            <a href="{{ route('analysis.show', $doc->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase tracking-wider">
                                Ver reporte
                            </a>
                        @else
                            <span class="text-[#A4D8FF]/40 font-semibold text-[12px]">—</span>
                        @endif

                        <form action="{{ route('dashboard.documents.destroy', $doc->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este documento y todo su análisis asociado en cascada?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center p-1.5 bg-[#0F1E36] hover:bg-[#162A45] text-[#A4D8FF] border border-[#15181B] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer" title="Eliminar documento en cascada">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


