{{-- First Entry Terms & Requirements Pop-Up Modal Component --}}
<div id="terms-popup-container" class="relative z-99999 hidden">
    {{-- Dark Backdrop Overlay --}}
    <div id="terms-popup-backdrop" 
         class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity duration-300 opacity-0 pointer-events-auto"
         style="z-index: 99998;"></div>

    {{-- Dialog Card --}}
    <div id="terms-popup-card" 
         class="fixed inset-0 flex items-center justify-center p-4 sm:p-6"
         style="z-index: 99999;">
        
        <div class="relative w-full max-w-2xl bg-gunmetal-card border-2 border-gunmetal shadow-brutal flex flex-col max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
            
            {{-- Header --}}
            <div class="relative p-6 sm:p-8 border-b-2 border-gunmetal flex items-start justify-between gap-4 bg-gunmetal-dark">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 bg-navy-dark border border-icy/40 flex items-center justify-center shrink-0 shadow-brutal-sm mt-1 p-1">
                        <img src="{{ asset('images/logo.png') }}" alt="Auditor-IA" class="w-full h-full object-contain">
                    </div>
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 bg-navy-dark border border-icy/40 text-[11px] font-mono font-bold text-icy uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-icy"></span>
                            Aviso de Entrada · Términos & Requisitos
                        </div>
                        <h2 class="text-xl sm:text-2xl font-serif font-bold text-icy tracking-tight">
                            Antes de comenzar en Auditor-IA
                        </h2>
                        <p class="text-xs sm:text-sm font-mono text-icy/70">
                            Por favor lee y acepta las condiciones legales y requisitos esenciales de uso del servicio.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Body Content (Scrollable) --}}
            <div class="p-6 sm:p-8 overflow-y-auto space-y-6 text-icy/90 text-xs sm:text-sm leading-relaxed">
                
                {{-- Alert Banner --}}
                <div class="p-4 bg-gunmetal-dark border-2 border-gunmetal text-icy flex items-start gap-3 text-xs sm:text-[13px] font-mono shadow-brutal-sm">
                    <svg class="w-5 h-5 shrink-0 text-icy mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <span class="font-bold uppercase">Requisito Obligatorio:</span> Todo usuario que navegue o se registre en Auditor-IA declara comprender los alcances de la Inteligencia Artificial y la normativa legal aplicable en Chile.
                    </div>
                </div>

                {{-- Points Grid --}}
                <div class="space-y-4">
                    {{-- Item 1 --}}
                    <div class="p-4 bg-gunmetal-dark border-2 border-gunmetal space-y-1.5 shadow-brutal-sm">
                        <h3 class="text-xs font-serif font-bold text-icy uppercase tracking-wider flex items-center gap-2">
                            <span class="font-mono text-icy">01.</span> Exclusión de Asesoría Legal Profesional
                        </h3>
                        <p class="text-icy/80 text-xs leading-relaxed font-sans">
                            Auditor-IA es una herramienta SaaS asistida por IA. No es una firma de abogados ni emite dictámenes jurídicos ni representación legal según el Código Orgánico de Tribunales de Chile. Los análisis deben ser validados por un abogado colegiado antes de tomar decisiones vinculantes.
                        </p>
                    </div>

                    {{-- Item 2 --}}
                    <div class="p-4 bg-gunmetal-dark border-2 border-gunmetal space-y-1.5 shadow-brutal-sm">
                        <h3 class="text-xs font-serif font-bold text-icy uppercase tracking-wider flex items-center gap-2">
                            <span class="font-mono text-icy">02.</span> Carácter Probabilístico y Exoneración
                        </h3>
                        <p class="text-icy/80 text-xs leading-relaxed font-sans">
                            Los reportes generados son análisis estadísticos basados en modelos LLM. El usuario exonera a Auditor-IA de cualquier responsabilidad por pérdidas o decisiones comerciales derivadas de la suscripción o firma de contratos (Ley N° 19.496).
                        </p>
                    </div>

                    {{-- Item 3 --}}
                    <div class="p-4 bg-gunmetal-dark border-2 border-gunmetal space-y-1.5 shadow-brutal-sm">
                        <h3 class="text-xs font-serif font-bold text-icy uppercase tracking-wider flex items-center gap-2">
                            <span class="font-mono text-icy">03.</span> Privacidad y Aislamiento Multi-Tenant
                        </h3>
                        <p class="text-icy/80 text-xs leading-relaxed font-sans">
                            Tus documentos se cifran y aíslan bajo la Ley N° 19.628 sobre Protección de la Vida Privada. Tus contratos nunca son comercializados ni utilizados para reentrenar modelos públicos.
                        </p>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-gunmetal text-xs font-mono text-icy/70">
                    <span>¿Deseas revisar el documento legal completo?</span>
                    <a href="{{ route('terms') }}" target="_blank" class="text-icy font-bold underline hover:underline transition-colors flex items-center gap-1">
                        Leer Términos y Condiciones
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

            </div>

            {{-- Footer / Action Area --}}
            <div class="p-6 sm:p-8 bg-gunmetal-dark border-t-2 border-gunmetal flex flex-col gap-4">
                
                {{-- Mandatory Checkbox --}}
                <label class="flex items-start gap-3 cursor-pointer select-none group">
                    <input type="checkbox" id="terms-popup-checkbox" class="mt-0.5 w-4 h-4 rounded-none border-2 border-gunmetal bg-gunmetal-dark accent-navy-dark cursor-pointer">
                    <span class="text-xs font-mono text-icy/80 group-hover:text-icy transition-colors leading-normal">
                        Entiendo y acepto los <strong>Términos de Uso, Requisitos y la Exclusión de Responsabilidad Legal</strong> para navegar y registrarme en Auditor-IA.
                    </span>
                </label>

                <div id="terms-popup-warning" class="hidden text-xs font-mono text-icy font-bold">
                    * Debes marcar la casilla para confirmar que has leído los términos antes de continuar.
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
                    <a href="{{ route('terms') }}" class="w-full sm:w-auto px-5 py-3 bg-gunmetal-dark border-2 border-gunmetal hover:bg-navy-light text-xs font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all text-center uppercase tracking-wider">
                        Ver Términos Completos
                    </a>

                    <button type="button" 
                            id="terms-popup-accept-btn" 
                            onclick="handleAcceptTermsPopup()"
                            class="w-full sm:w-auto px-6 py-3 bg-navy-dark hover:bg-navy-light border border-icy/40 text-xs sm:text-sm font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all cursor-pointer text-center uppercase tracking-wider">
                        Aceptar y Continuar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const TERMS_STORAGE_KEY = 'auditor_ia_terms_acknowledged_v1';
    let pendingRedirectUrl = null;

    function isTermsAcknowledged() {
        return localStorage.getItem(TERMS_STORAGE_KEY) === 'true';
    }

    function showTermsPopup(redirectTarget = null) {
        if (redirectTarget) {
            pendingRedirectUrl = redirectTarget;
        }

        const container = document.getElementById('terms-popup-container');
        const backdrop = document.getElementById('terms-popup-backdrop');
        const card = document.getElementById('terms-popup-card').firstElementChild;

        if (!container || !backdrop || !card) return;

        container.classList.remove('hidden');

        // Trigger css animation
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            card.classList.remove('opacity-0', 'scale-95');
            card.classList.add('opacity-100', 'scale-100');
        }, 50);
    }

    function hideTermsPopup() {
        const container = document.getElementById('terms-popup-container');
        const backdrop = document.getElementById('terms-popup-backdrop');
        const card = document.getElementById('terms-popup-card').firstElementChild;

        if (!container || !backdrop || !card) return;

        backdrop.classList.add('opacity-0');
        card.classList.remove('opacity-100', 'scale-100');
        card.classList.add('opacity-0', 'scale-95');

        setTimeout(() => {
            container.classList.add('hidden');
        }, 300);
    }

    function handleAcceptTermsPopup() {
        const checkbox = document.getElementById('terms-popup-checkbox');
        const warning = document.getElementById('terms-popup-warning');

        if (!checkbox.checked) {
            warning.classList.remove('hidden');
            checkbox.focus();
            return;
        }

        warning.classList.add('hidden');
        localStorage.setItem(TERMS_STORAGE_KEY, 'true');
        hideTermsPopup();

        if (pendingRedirectUrl) {
            const target = pendingRedirectUrl;
            pendingRedirectUrl = null;
            window.location.href = target;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const checkbox = document.getElementById('terms-popup-checkbox');
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                const warning = document.getElementById('terms-popup-warning');
                if (checkbox.checked && warning) {
                    warning.classList.add('hidden');
                }
            });
        }

        // Auto-popup on first visit to website unless on /terminos-y-condiciones page itself
        const currentPath = window.location.pathname;
        if (!isTermsAcknowledged() && !currentPath.includes('/terminos-y-condiciones')) {
            // Short delay so page loads smoothly before showing pop-up
            setTimeout(() => {
                showTermsPopup();
            }, 300);
        }

        // Intercept clicks on signup/login links if terms not acknowledged
        const actionLinks = document.querySelectorAll('a[href*="/login"], a[href*="register"], [data-require-terms]');
        actionLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                if (!isTermsAcknowledged() && !window.location.pathname.includes('/terminos-y-condiciones')) {
                    e.preventDefault();
                    showTermsPopup(link.getAttribute('href'));
                }
            });
        });
    });
</script>
