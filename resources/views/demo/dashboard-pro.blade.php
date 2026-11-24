@extends('demo.layout', ['activeSection' => 'dashboard'])

@section('title', 'Dashboard Pro (Demo) - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    <!-- Pro Header -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Dashboard Pro</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-mono font-bold uppercase tracking-wide bg-navy-dark text-icy border border-icy/40 shadow-brutal-sm">
                    <svg class="w-3.5 h-3.5 text-icy" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Pro
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-mono font-bold bg-[#1B4332] text-icy border border-gunmetal-dark shadow-brutal-sm uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F5C87A] animate-pulse"></span>
                    Vista Demostrativa Estática
                </span>
            </div>
            <p class="text-[13px] font-mono text-icy/70">Bienvenido, {{ $user['name'] }} · {{ $user['organization_name'] }}</p>
        </div>
        <button type="button" onclick="document.getElementById('demo-file-input').click()" class="flex items-center gap-2 px-5 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all cursor-pointer shrink-0 uppercase tracking-wider">
            <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Subir documento (Demo)
        </button>
    </header>

    {{-- AI Credits card --}}
    <x-ai-credits-card
        :credits-remaining="$credits['creditsRemaining']"
        :credits-quota="$credits['creditsQuota']"
        :credit-refresh-interval="$credits['creditRefreshInterval']"
        :period-expires-at="now()->addDays($credits['daysUntilRefresh'])"
        :plan-slug="$credits['planSlug']"
        :plan-name="$credits['planName']"
        :allows-model-selection="$credits['allowsModelSelection']"
    />

    <!-- Pro metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-icy uppercase tracking-wide">Documentos analizados</span>
            <span class="text-4xl font-serif font-extrabold text-icy">{{ $metrics['analyzedCount'] }}</span>
            <span class="text-[11px] font-mono text-icy/60">{{ $metrics['totalDocuments'] }} de muestra precargados</span>
        </div>
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-icy/80 uppercase tracking-wide">Riesgos detectados</span>
            <span class="text-4xl font-serif font-extrabold text-icy">{{ $metrics['riskCount'] }}</span>
            <span class="text-[11px] font-mono text-icy/60">En documentos de demostración</span>
        </div>
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col gap-2 shadow-brutal">
            <span class="text-[12px] font-mono font-bold text-icy/80 uppercase tracking-wide">Tiempo prom. análisis</span>
            <span class="text-4xl font-serif font-extrabold text-icy">{{ $metrics['avgTime'] }}s</span>
            <span class="text-[11px] font-mono text-icy/60">Referencia de procesamiento</span>
        </div>
    </div>

    <!-- Analysis types panel (Pro exclusive) -->
    <section id="analysis-types-panel" class="bg-gunmetal-card border-2 border-gunmetal p-6 flex flex-col gap-5 shadow-brutal">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-serif font-bold text-icy">Tipos de análisis disponibles</h2>
            <p class="text-[13px] font-mono text-icy/70">Como usuario Pro tienes acceso a todos los informes especializados.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach(\App\Enums\AnalysisType::cases() as $type)
                <div class="flex items-start gap-3 p-4 bg-gunmetal-dark border-2 border-gunmetal shadow-brutal-sm">
                    <div class="w-8 h-8 bg-navy-dark border border-icy/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <span class="text-[13.5px] font-bold text-icy">{{ $type->label() }}</span>
                        <span class="text-[11px] text-icy/70 leading-relaxed">{{ $type->description() }}</span>
                        <span class="text-[11px] font-mono font-bold text-icy mt-1">{{ $analysisTypeCounts[$type->value] ?? 0 }} este mes</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Pro AI Model Ecosystem Showcase (Pure Client-Side Selection) -->
    <section id="ai-models-panel" class="bg-gunmetal-card border-2 border-gunmetal p-6 flex flex-col gap-5 shadow-brutal">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gunmetal pb-3">
            <div class="flex flex-col gap-0.5">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-serif font-bold text-icy">Ecosistema de Modelos IA</h2>
                    <span class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider bg-[#3B2D1A] text-[#F5C87A] border border-[#7A5C2E] shadow-brutal-sm">
                        Vista Interactiva Local
                    </span>
                </div>
                <p class="text-[13px] font-mono text-icy/70">Haz clic en cualquier modelo para previsualizar la selección en interfaz (sin llamadas a APIs).</p>
            </div>
            <span class="text-[11px] font-mono text-icy/60 bg-navy-dark px-3 py-1 border border-icy/30 shadow-brutal-sm self-start sm:self-auto">
                Simulación UI · Cero peticiones HTTP
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @foreach($aiModels as $index => $model)
                @php $isSelected = $index === 0; @endphp
                <button
                    type="button"
                    data-demo-model-card
                    data-model-slug="{{ $model['slug'] }}"
                    data-model-name="{{ $model['display_name'] }}"
                    data-model-provider="{{ $model['provider_label'] }}"
                    class="w-full text-left p-4 h-full border-2 transition-all duration-200 cursor-pointer flex flex-col justify-between gap-3 relative overflow-hidden {{ $isSelected ? 'bg-[#2A2012] border-[#F5C87A] shadow-brutal text-icy' : 'bg-gunmetal-dark border-gunmetal hover:border-icy/60 shadow-brutal-sm' }}"
                >
                    @if(!empty($model['badge_text']))
                        <div class="absolute top-0 right-0 bg-[#7A5C2E] text-[#F5C87A] text-[9px] font-mono font-black uppercase px-2 py-0.5 tracking-wider">
                            {{ $model['badge_text'] }}
                        </div>
                    @endif
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span data-model-dot class="w-2.5 h-2.5 rounded-full {{ $model['accent'] === 'amber' ? 'bg-[#F5C87A]' : ($model['accent'] === 'emerald' ? 'bg-[#D5E8D4]' : 'bg-icy') }} {{ $isSelected ? 'animate-pulse' : '' }}"></span>
                            <span class="text-[13px] font-bold {{ $model['accent'] === 'amber' ? 'text-[#F5C87A]' : 'text-icy' }}">{{ $model['display_name'] }}</span>
                        </div>
                        <span class="text-[10px] font-mono font-bold text-icy/60 uppercase">{{ $model['provider_label'] }} · {{ $model['tier_label'] }}</span>
                        <p class="text-[11.5px] text-icy/80 leading-relaxed mt-1">
                            {{ $model['description'] }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between text-[10.5px] font-mono pt-2 border-t border-gunmetal w-full">
                        <span class="text-icy/60">{{ $model['footer_note'] }}</span>
                        <span data-model-status-pill class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider {{ $isSelected ? 'bg-[#F5C87A] text-gunmetal-dark' : 'bg-navy-dark text-icy border border-icy/40' }}">
                            {{ $isSelected ? 'Seleccionado' : 'Activar' }}
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    </section>

    <!-- Simulated Upload Zone (No network / no backend) -->
    <div id="demo-upload-zone" class="bg-gunmetal-card border-2 border-dashed border-gunmetal hover:border-icy/80 p-8 md:p-12 flex flex-col items-center justify-center gap-6 shadow-brutal transition-colors">
        <input type="file" id="demo-file-input" class="hidden" accept=".pdf,.docx">

        <div onclick="document.getElementById('demo-file-input').click()" class="w-14 h-14 bg-navy-dark border-2 border-gunmetal-dark flex items-center justify-center text-icy shadow-brutal-sm cursor-pointer hover:-translate-x-px hover:-translate-y-px transition-all">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
        </div>

        <div class="flex flex-col gap-2 text-center">
            <h2 class="text-xl font-serif font-bold text-icy tracking-tight">Zona de Carga de Contratos (Simulación)</h2>
            <p class="text-[13px] font-mono text-icy/70 leading-relaxed">
                PDF o DOCX · hasta 50MB · marco legal chileno vigente <br>
                <span class="inline-flex items-center gap-2 mt-2 text-xs font-mono text-icy bg-gunmetal-dark px-3.5 py-1.5 border border-gunmetal shadow-brutal-sm">
                    <span class="w-2 h-2 rounded-full bg-[#F5C87A] animate-pulse"></span>
                    Modelo visualizado: <strong id="demo-active-model-name" class="font-bold text-[#F5C87A]">Gemma 4 31B</strong>
                    <span class="text-icy/40">·</span>
                    <span id="demo-active-model-provider" class="text-icy/70">Cerebras AI</span>
                </span>
            </p>
        </div>

        <div id="demo-upload-feedback" class="hidden w-full max-w-2xl bg-[#0F1E36] border-2 border-[#A4D8FF]/40 p-4 text-[12.5px] font-mono text-icy shadow-brutal-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="font-bold uppercase tracking-wider text-[#F5C87A]">Subida deshabilitada en despliegue estático</p>
                    <p id="demo-upload-feedback-text" class="text-icy/80 mt-1">
                        Ningún archivo es enviado a servidores ni APIs externas en este ambiente de demostración.
                    </p>
                </div>
                @if(!empty($documents[0]))
                    <a href="{{ route('demo.analysis.show', $documents[0]['slug']) }}" class="px-4 py-2 bg-gunmetal-dark border border-icy/40 text-icy font-bold uppercase tracking-wider text-[11px] shrink-0 text-center hover:bg-navy-light">
                        Ver reporte ejemplo
                    </a>
                @endif
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3">
            <button type="button" onclick="document.getElementById('demo-file-input').click()" class="px-5 py-2.5 bg-gunmetal-dark border border-gunmetal text-[13px] font-mono font-bold text-icy shadow-brutal-sm hover:bg-navy-light transition-colors cursor-pointer uppercase tracking-wider">
                Probar selector local
            </button>
            @if(!empty($documents[0]))
                <a href="{{ route('demo.analysis.show', $documents[0]['slug']) }}" class="px-5 py-2.5 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[13px] font-mono font-bold text-icy flex items-center gap-2 shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                    <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Abrir Auditoría de Ejemplo
                </a>
            @endif
        </div>
    </div>

    <!-- Sample Documents Table -->
    <section id="recent-analyses-container" class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-serif font-bold text-icy tracking-tight">Documentos de prueba pre-auditados</h2>
            <span class="text-[11px] font-mono text-icy/70 bg-navy-dark px-2.5 py-1 border border-icy/30 shadow-brutal-sm">
                {{ count($documents) }} reportes disponibles
            </span>
        </div>

        <div class="overflow-x-auto bg-[#222629] border-2 border-[#35393C] shadow-brutal">
            <table class="w-full text-left border-collapse" id="documents-table">
                <thead>
                    <tr class="border-b-2 border-[#35393C] text-[12px] font-mono font-bold text-[#A4D8FF] uppercase tracking-wider bg-[#15181B]">
                        <th class="px-6 py-4">Documento</th>
                        <th class="px-6 py-4">Motor IA</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#35393C]/40 text-[13px] font-mono text-[#A4D8FF]">
                    @foreach($documents as $doc)
                        <tr class="hover:bg-[#15181B]/60 transition-colors">
                            <td class="px-6 py-4.5 font-bold text-[#A4D8FF] max-w-xs truncate" title="{{ $doc['original_filename'] }}">
                                {{ $doc['original_filename'] }}
                            </td>
                            <td class="px-6 py-4.5 text-[#A4D8FF]/80 text-[12px]">
                                {{ $doc['analysis']['llm_model'] }}
                            </td>
                            <td class="px-6 py-4.5 text-[#A4D8FF]/70">
                                {{ $doc['created_at_human'] }}
                            </td>
                            <td class="px-6 py-4.5">
                                @include('components.document-status-badge', ['status' => $doc['status']])
                            </td>
                            <td class="px-6 py-4.5 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a href="{{ route('demo.analysis.show', $doc['slug']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[12px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px transition-all uppercase tracking-wider">
                                        Ver reporte
                                    </a>
                                    <a href="{{ route('demo.analysis.clauses', $doc['slug']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#15181B] hover:bg-[#162A45] border border-[#35393C] text-[12px] font-mono font-bold text-[#A4D8FF]/80 hover:text-[#A4D8FF] shadow-brutal-sm transition-all uppercase tracking-wider">
                                        Cláusulas
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modelCards = document.querySelectorAll('[data-demo-model-card]');
    const activeModelName = document.getElementById('demo-active-model-name');
    const activeModelProvider = document.getElementById('demo-active-model-provider');

    modelCards.forEach((card) => {
        card.addEventListener('click', () => {
            modelCards.forEach((other) => {
                other.classList.remove('bg-[#2A2012]', 'border-[#F5C87A]', 'bg-navy-dark', 'border-icy', 'shadow-brutal');
                other.classList.add('bg-gunmetal-dark', 'border-gunmetal', 'shadow-brutal-sm');
                const pill = other.querySelector('[data-model-status-pill]');
                if (pill) {
                    pill.textContent = 'Activar';
                    pill.className = 'px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider bg-navy-dark text-icy border border-icy/40';
                }
                const dot = other.querySelector('[data-model-dot]');
                dot?.classList.remove('animate-pulse');
            });

            card.classList.remove('bg-gunmetal-dark', 'border-gunmetal', 'shadow-brutal-sm');
            card.classList.add('bg-navy-dark', 'border-icy', 'shadow-brutal');

            const pill = card.querySelector('[data-model-status-pill]');
            if (pill) {
                pill.textContent = 'Seleccionado';
                pill.className = 'px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider bg-icy text-navy-dark';
            }
            card.querySelector('[data-model-dot]')?.classList.add('animate-pulse');

            if (activeModelName) {
                activeModelName.textContent = card.dataset.modelName || '';
            }
            if (activeModelProvider) {
                activeModelProvider.textContent = card.dataset.modelProvider || '';
            }
        });
    });

    const fileInput = document.getElementById('demo-file-input');
    const feedback = document.getElementById('demo-upload-feedback');
    const feedbackText = document.getElementById('demo-upload-feedback-text');

    if (fileInput && feedback && feedbackText) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            feedbackText.textContent = `Archivo seleccionado localmente: "${file.name}" (${sizeMb} MB). En el despliegue estático no se envía ni procesa mediante APIs; explora los documentos pre-auditados abajo.`;
            feedback.classList.remove('hidden');
            fileInput.value = '';
        });
    }
});
</script>
@endpush

