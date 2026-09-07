@if(Auth::user()->hasProAccess() && !Auth::user()->dismissed_ancillary_tutorial)
<div id="ancillary-tutorial-container" class="relative z-[99999]">
    <!-- SVG Overlay Mask for Spotlight -->
    <svg id="ancillary-tutorial-overlay" class="fixed inset-0 w-full h-full pointer-events-auto transition-opacity duration-300 opacity-0 hidden" style="z-index: 9998;">
        <defs>
            <mask id="ancillary-tutorial-mask">
                <!-- White covers screen (fully dark) -->
                <rect width="100%" height="100%" fill="white" />
                <!-- Black cuts hole (fully transparent) -->
                <rect id="ancillary-tutorial-spotlight" x="0" y="0" width="0" height="0" rx="0" fill="black" class="transition-all duration-300" />
            </mask>
        </defs>
        <!-- Dark backdrop -->
        <rect width="100%" height="100%" fill="rgba(21, 24, 27, 0.85)" mask="url(#ancillary-tutorial-mask)" />
    </svg>

    <!-- Tutorial Dialog Card -->
    <div id="ancillary-tutorial-card" class="fixed bottom-6 right-6 w-[360px] max-w-[calc(100vw-32px)] bg-[#222629] border-2 border-[#35393C] shadow-brutal p-5 flex flex-col gap-4 transform transition-all duration-300 scale-95 opacity-0 translate-y-4 hidden" style="z-index: 9999;">
        <!-- Header -->
        <div class="flex items-center justify-between gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-[#0F1E36] border border-[#A4D8FF]/40 text-[10px] font-mono font-bold uppercase tracking-wider text-[#A4D8FF]">
                Nueva Función PRO
            </span>
            <button onclick="dismissAncillaryTutorial(false)" class="font-mono text-[#A4D8FF]/60 hover:text-[#A4D8FF] transition-colors cursor-pointer text-xs uppercase tracking-wider">
                Omitir
            </button>
        </div>

        <!-- Content -->
        <div class="flex flex-col gap-2">
            <h3 id="ancillary-tutorial-title" class="text-[15px] font-serif font-bold text-[#A4D8FF] tracking-tight"></h3>
            <p id="ancillary-tutorial-description" class="text-[12px] font-mono text-[#A4D8FF]/80 leading-relaxed"></p>
        </div>

        <!-- Footer / Navigation -->
        <div class="flex items-center justify-between gap-4 mt-2">
            <!-- Steps Indicator -->
            <div class="flex items-center gap-1">
                <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/70" id="ancillary-tutorial-progress"></span>
            </div>

            <!-- Controls -->
            <div class="flex items-center gap-2">
                <button id="ancillary-tutorial-prev-btn" onclick="prevAncillaryStep()" class="px-3 py-1.5 bg-[#15181B] border-2 border-[#35393C] hover:bg-[#222629] text-[11px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer hidden uppercase tracking-wider">
                    Atrás
                </button>
                <button id="ancillary-tutorial-next-btn" onclick="nextAncillaryStep()" class="px-4 py-1.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[11px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
                    Siguiente
                </button>
            </div>
        </div>

        <!-- Dismiss Option -->
        <div class="border-t-2 border-[#35393C] pt-3 mt-1 flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="ancillary-tutorial-dont-show-again" class="w-3.5 h-3.5 rounded-none border-2 border-[#35393C] bg-[#15181B] accent-[#0F1E36]">
                <span class="text-[11px] font-mono text-[#A4D8FF]/70 font-medium hover:text-[#A4D8FF] transition-colors">No volver a mostrar este tutorial</span>
            </label>
        </div>
    </div>
</div>

<script>
    const ancillaryTutorialSteps = [
        {
            title: "Nueva Función PRO: Documentos Anexos",
            description: "Ahora puedes auditar documentos complementarios, como anexos técnicos o liquidaciones, en base a un documento principal.",
            target: null
        },
        {
            title: "Sección de Anexos",
            description: "Encontrarás esta nueva sección en el reporte de análisis. Aquí puedes subir documentos relacionados con el contrato principal.",
            target: "#supporting-docs-section"
        },
        {
            title: "Auditoría Contextual",
            description: "Nuestra IA analizará el documento anexo tomando en cuenta el contexto del contrato principal para detectar inconsistencias y riesgos cruzados.",
            target: null
        }
    ];

    let currentAncillaryStepIndex = 0;
    let isAncillaryDismissSaved = false;

    function initAncillaryTutorial() {
        const container = document.getElementById('ancillary-tutorial-container');
        const overlay = document.getElementById('ancillary-tutorial-overlay');
        const card = document.getElementById('ancillary-tutorial-card');

        if (!container || !overlay || !card) return;

        overlay.classList.remove('hidden');
        card.classList.remove('hidden');

        // Trigger animations
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            card.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
        }, 100);

        renderAncillaryStep();
    }

    function renderAncillaryStep() {
        const step = ancillaryTutorialSteps[currentAncillaryStepIndex];
        const titleEl = document.getElementById('ancillary-tutorial-title');
        const descEl = document.getElementById('ancillary-tutorial-description');
        const progressEl = document.getElementById('ancillary-tutorial-progress');
        const prevBtn = document.getElementById('ancillary-tutorial-prev-btn');
        const nextBtn = document.getElementById('ancillary-tutorial-next-btn');
        const spotlight = document.getElementById('ancillary-tutorial-spotlight');

        titleEl.textContent = step.title;
        descEl.textContent = step.description;
        progressEl.textContent = `${currentAncillaryStepIndex + 1} de ${ancillaryTutorialSteps.length}`;

        // Controls visibility
        if (currentAncillaryStepIndex === 0) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }

        if (currentAncillaryStepIndex === ancillaryTutorialSteps.length - 1) {
            nextBtn.textContent = "Entendido";
        } else {
            nextBtn.textContent = "Siguiente";
        }

        // Spotlight highlight logic
        let targetEl = null;
        if (step.target) {
            targetEl = document.querySelector(step.target);
        }

        if (targetEl) {
            // Scroll target element to center instantly to avoid coordinate race conditions
            targetEl.scrollIntoView({ behavior: 'auto', block: 'center' });

            // Calculate position of the element immediately after scroll
            setTimeout(() => {
                const rect = targetEl.getBoundingClientRect();
                const padding = 10;

                spotlight.setAttribute('x', (rect.left - padding).toString());
                spotlight.setAttribute('y', (rect.top - padding).toString());
                spotlight.setAttribute('width', (rect.width + padding * 2).toString());
                spotlight.setAttribute('height', (rect.height + padding * 2).toString());
            }, 50);
        } else {
            // No target, hide/clear spotlight spotlight hole
            spotlight.setAttribute('x', '0');
            spotlight.setAttribute('y', '0');
            spotlight.setAttribute('width', '0');
            spotlight.setAttribute('height', '0');
        }
    }

    function nextAncillaryStep() {
        if (currentAncillaryStepIndex < ancillaryTutorialSteps.length - 1) {
            currentAncillaryStepIndex++;
            renderAncillaryStep();
        } else {
            dismissAncillaryTutorial(true);
        }
    }

    function prevAncillaryStep() {
        if (currentAncillaryStepIndex > 0) {
            currentAncillaryStepIndex--;
            renderAncillaryStep();
        }
    }

    function dismissAncillaryTutorial(completed = false) {
        const overlay = document.getElementById('ancillary-tutorial-overlay');
        const card = document.getElementById('ancillary-tutorial-card');
        const dontShowAgain = document.getElementById('ancillary-tutorial-dont-show-again').checked;

        if (overlay && card) {
            overlay.classList.add('opacity-0');
            card.classList.add('opacity-0', 'scale-95', 'translate-y-4');

            setTimeout(() => {
                overlay.classList.add('hidden');
                card.classList.add('hidden');
            }, 300);
        }

        // Only save state to DB if the "Don't show again" checkbox is checked or completed
        if (dontShowAgain || completed) {
            saveAncillaryDismissState();
        }
    }

    function saveAncillaryDismissState() {
        if (isAncillaryDismissSaved) return;
        isAncillaryDismissSaved = true;

        fetch("{{ route('user.dismiss-ancillary-tutorial') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                console.error("Error saving ancillary tutorial dismissal state.");
                isAncillaryDismissSaved = false;
            }
        })
        .catch(error => {
            console.error("AJAX Error:", error);
            isAncillaryDismissSaved = false;
        });
    }

    // Auto-initialize tutorial when page loads
    document.addEventListener("DOMContentLoaded", function() {
        const dontShowAgainCheckbox = document.getElementById('ancillary-tutorial-dont-show-again');
        if (dontShowAgainCheckbox) {
            dontShowAgainCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    saveAncillaryDismissState();
                }
            });
        }

        // We launch the ancillary tutorial if we are on the analysis detail page
        setTimeout(initAncillaryTutorial, 800);
    });

    // Recalculate spotlight size on window resize
    window.addEventListener('resize', () => {
        if (ancillaryTutorialSteps[currentAncillaryStepIndex].target) {
            renderAncillaryStep();
        }
    });
</script>
@endif
