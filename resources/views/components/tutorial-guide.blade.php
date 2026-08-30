@if(!Auth::user()->dismissed_tutorial)
<div id="tutorial-container" class="relative z-[99999]">
    <!-- SVG Overlay Mask for Spotlight -->
    <svg id="tutorial-overlay" class="fixed inset-0 w-full h-full pointer-events-auto transition-opacity duration-300 opacity-0 hidden" style="z-index: 9998;">
        <defs>
            <mask id="tutorial-mask">
                <!-- White covers screen (fully dark) -->
                <rect width="100%" height="100%" fill="white" />
                <!-- Black cuts hole (fully transparent) -->
                <rect id="tutorial-spotlight" x="0" y="0" width="0" height="0" rx="0" fill="black" class="transition-all duration-300" />
            </mask>
        </defs>
        <!-- Dark backdrop -->
        <rect width="100%" height="100%" fill="rgba(21, 24, 27, 0.85)" mask="url(#tutorial-mask)" />
    </svg>

    <!-- Tutorial Dialog Card -->
    <div id="tutorial-card" class="fixed bottom-6 right-6 w-[360px] max-w-[calc(100vw-32px)] bg-[#222629] border-2 border-[#35393C] shadow-brutal p-5 flex flex-col gap-4 transform transition-all duration-300 scale-95 opacity-0 translate-y-4 hidden" style="z-index: 9999;">
        <!-- Header -->
        <div class="flex items-center justify-between gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-[#0F1E36] border border-[#A4D8FF]/40 text-[10px] font-mono font-bold uppercase tracking-wider text-[#A4D8FF]">
                Guía de Ayuda
            </span>
            <button onclick="dismissTutorial(false)" class="font-mono text-[#A4D8FF]/60 hover:text-[#A4D8FF] transition-colors cursor-pointer text-xs uppercase tracking-wider">
                Omitir
            </button>
        </div>

        <!-- Content -->
        <div class="flex flex-col gap-2">
            <h3 id="tutorial-title" class="text-[15px] font-serif font-bold text-[#A4D8FF] tracking-tight"></h3>
            <p id="tutorial-description" class="text-[12px] font-mono text-[#A4D8FF]/80 leading-relaxed"></p>
        </div>

        <!-- Footer / Navigation -->
        <div class="flex items-center justify-between gap-4 mt-2">
            <!-- Steps Indicator -->
            <div class="flex items-center gap-1">
                <span class="text-[11px] font-mono font-bold text-[#A4D8FF]/70" id="tutorial-progress"></span>
            </div>

            <!-- Controls -->
            <div class="flex items-center gap-2">
                <button id="tutorial-prev-btn" onclick="prevStep()" class="px-3 py-1.5 bg-[#15181B] border-2 border-[#35393C] hover:bg-[#222629] text-[11px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer hidden uppercase tracking-wider">
                    Atrás
                </button>
                <button id="tutorial-next-btn" onclick="nextStep()" class="px-4 py-1.5 bg-[#0F1E36] hover:bg-[#162A45] border border-[#A4D8FF]/40 text-[11px] font-mono font-bold text-[#A4D8FF] shadow-brutal-sm hover:translate-x-[-1px] hover:translate-y-[-1px] active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all cursor-pointer uppercase tracking-wider">
                    Siguiente
                </button>
            </div>
        </div>

        <!-- Dismiss Option -->
        <div class="border-t-2 border-[#35393C] pt-3 mt-1 flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="tutorial-dont-show-again" class="w-3.5 h-3.5 rounded-none border-2 border-[#35393C] bg-[#15181B] accent-[#0F1E36]">
                <span class="text-[11px] font-mono text-[#A4D8FF]/70 font-medium hover:text-[#A4D8FF] transition-colors">No volver a mostrar este tutorial</span>
            </label>
        </div>
    </div>
</div>

<script>
    const tutorialSteps = [
        {
            title: "¡Bienvenido a Auditor-IA!",
            description: "Esta guía interactiva te enseñará rápidamente cómo utilizar la plataforma para auditar tus documentos y contratos legales de forma inteligente.",
            target: null
        },
        {
            title: "1. Control de Plan y Consumo",
            description: "En esta sección puedes ver el estado de tu plan actual (Básico o Pro) junto con tus límites mensuales de documentos, análisis y almacenamiento.",
            target: "#plan-usage-card"
        },
        {
            title: "2. Sube tus Contratos",
            description: "Arrastra y suelta tu archivo PDF o DOCX aquí. El procesamiento asincrónico iniciará automáticamente para extraer texto y auditar riesgos legales.",
            target: "#upload-zone"
        },
        {
            title: "3. Historial de Análisis",
            description: "Aquí se listarán tus documentos subidos. Una vez procesados con nuestra IA, podrás ver el estado y hacer clic en 'Ver reporte' para abrir el informe de auditoría detallado.",
            target: "#sidebar-recents"
        },
        {
            title: "¡Listo para comenzar!",
            description: "Ya tienes el control total para auditar tus documentos de manera rápida y segura. Si tienes dudas, revisa nuestros recursos integrados.",
            target: null
        }
    ];

    const isPro = {{ Auth::user()->hasProAccess() ? 'true' : 'false' }};
    if (isPro) {
        // Insert Pro analysis types panel step at index 3
        tutorialSteps.splice(3, 0, {
            title: "3. Tipos de Análisis Especializados",
            description: "Como usuario Pro, tienes acceso ilimitado a todos los tipos de informes (comercial, laboral, etc.). Cada informe aplica reglas y normativas específicas para auditar riesgos.",
            target: "#analysis-types-panel"
        });
        // Update the numbering of the subsequent step
        tutorialSteps[4].title = "4. Historial de Análisis";
    }

    let currentStepIndex = 0;
    let isDismissSaved = false;

    function initTutorial() {
        const container = document.getElementById('tutorial-container');
        const overlay = document.getElementById('tutorial-overlay');
        const card = document.getElementById('tutorial-card');

        if (!container || !overlay || !card) return;

        overlay.classList.remove('hidden');
        card.classList.remove('hidden');

        // Trigger animations
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            card.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
        }, 100);

        renderStep();
    }

    function renderStep() {
        const step = tutorialSteps[currentStepIndex];
        const titleEl = document.getElementById('tutorial-title');
        const descEl = document.getElementById('tutorial-description');
        const progressEl = document.getElementById('tutorial-progress');
        const prevBtn = document.getElementById('tutorial-prev-btn');
        const nextBtn = document.getElementById('tutorial-next-btn');
        const spotlight = document.getElementById('tutorial-spotlight');

        titleEl.textContent = step.title;
        descEl.textContent = step.description;
        progressEl.textContent = `${currentStepIndex + 1} de ${tutorialSteps.length}`;

        // Controls visibility
        if (currentStepIndex === 0) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }

        if (currentStepIndex === tutorialSteps.length - 1) {
            nextBtn.textContent = "Comenzar";
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

    function nextStep() {
        if (currentStepIndex < tutorialSteps.length - 1) {
            currentStepIndex++;
            renderStep();
        } else {
            dismissTutorial(true);
        }
    }

    function prevStep() {
        if (currentStepIndex > 0) {
            currentStepIndex--;
            renderStep();
        }
    }

    function dismissTutorial(completed = false) {
        const overlay = document.getElementById('tutorial-overlay');
        const card = document.getElementById('tutorial-card');
        const dontShowAgain = document.getElementById('tutorial-dont-show-again').checked;

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
            saveDismissState();
        }
    }

    function saveDismissState() {
        if (isDismissSaved) return;
        isDismissSaved = true;

        fetch("{{ route('user.dismiss-tutorial') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                console.error("Error saving tutorial dismissal state.");
                isDismissSaved = false;
            }
        })
        .catch(error => {
            console.error("AJAX Error:", error);
            isDismissSaved = false;
        });
    }

    // Auto-initialize tutorial when page loads
    document.addEventListener("DOMContentLoaded", function() {
        const dontShowAgainCheckbox = document.getElementById('tutorial-dont-show-again');
        if (dontShowAgainCheckbox) {
            dontShowAgainCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    saveDismissState();
                }
            });
        }

        // Only launch tutorial if we are on the main dashboard view
        const path = window.location.pathname;
        if (path.endsWith('/dashboard') || path.includes('/dashboard')) {
            setTimeout(initTutorial, 800);
        }
    });

    // Recalculate spotlight size on window resize
    window.addEventListener('resize', () => {
        if (tutorialSteps[currentStepIndex].target) {
            renderStep();
        }
    });
</script>
@endif

