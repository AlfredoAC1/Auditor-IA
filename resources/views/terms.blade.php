@extends('layouts.guest')

@section('title', 'Términos y Condiciones de Uso — Auditor-IA')
@section('meta_description', 'Conoce los términos y condiciones de uso de Auditor-IA. Exclusión de responsabilidad, alcance del análisis con IA y legislación chilena aplicable.')

@section('content')
<div class="relative overflow-hidden min-h-screen py-16 md:py-24">
    
    {{-- Decorative Background Glows --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute top-0 left-1/4 -translate-x-1/2 w-[600px] h-[400px] bg-violet-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 w-[500px] h-[500px] bg-fuchsia-600/5 rounded-full blur-[100px]"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-6">
        <!-- Breadcrumbs -->
        <div class="mb-8">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-zinc-400 hover:text-white text-sm font-medium transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al inicio
            </a>
        </div>

        <!-- Header -->
        <div class="max-w-3xl mb-16">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-500/10 border border-violet-500/20 text-[12px] font-semibold text-violet-300 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>
                Marco Legal & Términos de Uso
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight leading-tight mb-4">
                Términos y <span class="bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">Condiciones de Uso</span>
            </h1>
            <p class="text-zinc-400 text-sm md:text-base">
                Última actualización: 02 de Julio, 2026. Por favor, lea detenidamente este documento antes de utilizar nuestros servicios.
            </p>
        </div>

        <!-- Layout Grid -->
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            
            <!-- Sticky Navigation (Desktop) -->
            <aside class="hidden lg:block lg:col-span-4 sticky top-28 bg-[#161616]/40 border border-zinc-800/60 rounded-2xl p-6 backdrop-blur-md">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-4 px-2">Tabla de Contenidos</h3>
                <nav class="space-y-1" id="terms-toc">
                    <a href="#aceptacion" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all duration-200">
                        1. Aceptación de los Términos
                    </a>
                    <a href="#objeto" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all duration-200">
                        2. Naturaleza y Objeto del Servicio
                    </a>
                    <a href="#asesoria" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all duration-200">
                        3. Exclusión de Asesoría Legal
                    </a>
                    <a href="#exencion" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-semibold text-violet-400 hover:text-violet-300 hover:bg-zinc-800/40 transition-all duration-200 border-l-2 border-violet-500 pl-4 bg-violet-950/10">
                        4. Liberación de Responsabilidad e Indemnidad
                    </a>
                    <a href="#privacidad" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all duration-200">
                        5. Privacidad y Datos Personales
                    </a>
                    <a href="#propiedad" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all duration-200">
                        6. Propiedad Intelectual
                    </a>
                    <a href="#jurisdiccion" class="toc-link block px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-zinc-400 hover:text-white hover:bg-zinc-800/40 transition-all duration-200">
                        7. Jurisdicción y Ley Aplicable
                    </a>
                </nav>
            </aside>

            <!-- Legal Text Content -->
            <div class="lg:col-span-8 bg-[#161616]/30 border border-zinc-800/50 rounded-3xl p-6 md:p-12 backdrop-blur-md space-y-12 text-zinc-300 leading-relaxed text-[14.5px]">
                
                <!-- Section 1: Aceptación -->
                <section id="aceptacion" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">01.</span>
                        Aceptación de los Términos y Condiciones
                    </h2>
                    <p>
                        El presente contrato regula los términos y condiciones generales aplicables al uso de los servicios ofrecidos por la plataforma <strong>Auditor-IA</strong> ("el Servicio" o "la Plataforma"). Al acceder, navegar o utilizar la Plataforma, usted ("el Usuario") declara haber leído, comprendido y aceptado en su totalidad las estipulaciones aquí contenidas.
                    </p>
                    <p>
                        Si usted no está de acuerdo con alguna de las disposiciones de estos términos, deberá abstenerse de utilizar el Servicio de manera inmediata. El registro, la subida de documentos y la consulta de informes de auditoría implican la aceptación incondicional del presente documento.
                    </p>
                </section>

                <!-- Section 2: Objeto -->
                <section id="objeto" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">02.</span>
                        Naturaleza y Objeto del Servicio
                    </h2>
                    <p>
                        Auditor-IA es una herramienta SaaS de soporte analítico basada en inteligencia artificial (modelos de lenguaje de gran tamaño - LLM) y tecnologías de procesamiento documental. El propósito de la Plataforma es ayudar al Usuario a automatizar la extracción de texto, identificar cláusulas clave, detectar riesgos potenciales y generar sugerencias preliminares en base a patrones definidos y alineados con la legislación de la República de Chile.
                    </p>
                    <p>
                        El Usuario reconoce que los resultados generados por el sistema son análisis estadísticos y probabilísticos y que la tecnología aplicada no es infalible. Los análisis son de carácter general y preliminar, y en ningún caso constituyen dictámenes jurídicos.
                    </p>
                </section>

                <!-- Section 3: Exclusión de Asesoría Legal -->
                <section id="asesoria" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">03.</span>
                        Exclusión de Asesoría Legal Profesional
                    </h2>
                    
                    {{-- Alert Box --}}
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 flex gap-3 text-[13.5px]">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="font-bold">Advertencia Legal:</span> Auditor-IA no es una firma de abogados, no provee representación legal y no ofrece asesoría jurídica profesional en los términos del ordenamiento jurídico chileno.
                        </div>
                    </div>

                    <p>
                        En estricto cumplimiento del <strong>Código Orgánico de Tribunales de Chile</strong> (en especial, sus artículos 520 y siguientes, y las disposiciones penales relativas al ejercicio ilegal de la profesión), el Usuario declara y acepta que:
                    </p>
                    <ul class="list-disc pl-5 space-y-2 text-zinc-400 text-[14px]">
                        <li>El uso de la Plataforma no crea una relación abogado-cliente.</li>
                        <li>Los informes y recomendaciones emitidos por Auditor-IA no sustituyen, en ningún caso, la asesoría personalizada, el juicio crítico y la revisión técnica de un abogado habilitado para el ejercicio de la profesión en Chile.</li>
                        <li>La Plataforma recomienda expresamente al Usuario someter todo análisis documental y contrato a la revisión final de un profesional del derecho calificado antes de tomar decisiones vinculantes, realizar firmas o proceder a transacciones.</li>
                    </ul>
                </section>

                <!-- Section 4: EXENCIÓN Y LIBERACIÓN DE RESPONSABILIDAD (CRITICAL CLAUSE) -->
                <section id="exencion" class="scroll-mt-28 space-y-5">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">04.</span>
                        Exención y Liberación de Responsabilidad e Indemnidad
                    </h2>

                    {{-- Premium Highlighted Box for the Exculpation Clause --}}
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-950/20 to-fuchsia-950/20 border border-violet-500/35 p-6 md:p-8 space-y-4">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-violet-500/10 rounded-full blur-2xl"></div>
                        
                        <h3 class="text-base font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-violet-400 animate-pulse"></span>
                            Cláusula de Exclusión Absoluta de Responsabilidad
                        </h3>
                        
                        <p class="text-zinc-200 font-medium text-[13.5px] leading-relaxed">
                            DE CONFORMIDAD CON LAS NORMAS GENERALES DE LA RESPONSABILIDAD CIVIL CONTRACTUAL Y EXTRACONTRACTUAL BAJO EL CÓDIGO CIVIL DE LA REPÚBLICA DE CHILE, EL USUARIO ACEPTA EXPRESAMENTE LIBERAR DE TODA RESPONSABILIDAD Y EXONERAR EN SU TOTALIDAD A AUDITOR-IA, ASÍ COMO A SUS PROPIETARIOS, DESARROLLADORES, PROVEEDORES, FILIALES Y EMPLEADOS, DE CUALQUIER RECLAMO, DAÑO, PERJUICIO O ACCIÓN LEGAL QUE PUDIERA ORIGINARSE A PARTIR DEL USO DEL SERVICIO.
                        </p>

                        <div class="border-t border-zinc-800/80 pt-4 space-y-2 text-[13px] text-zinc-400">
                            <p>Esta exención incluye, de manera ilustrativa mas no limitativa, los siguientes supuestos:</p>
                            <ul class="list-decimal pl-4 space-y-1">
                                <li>Errores, omisiones, imprecisiones, falsos positivos o alucinaciones generadas por la Inteligencia Artificial en el análisis de contratos.</li>
                                <li>Pérdidas económicas, lucro cesante, daño emergente o perjuicios morales derivados de la suscripción o resolución de contratos analizados en la plataforma.</li>
                                <li>Pérdida de datos, fallos temporales de conexión, o brechas de seguridad imputables a proveedores de hosting, infraestructura en la nube o fuerza mayor.</li>
                            </ul>
                        </div>
                    </div>

                    <p>
                        El Usuario declara conocer que, bajo el amparo de la <strong>Ley N° 19.496 sobre Protección de los Derechos de los Consumidores</strong> en Chile, el Servicio se suministra como una herramienta de apoyo analítico meramente probabilístico ("as is" o "tal como está"), existiendo un consentimiento expreso y previo del Usuario acerca de las limitaciones técnicas de la inteligencia artificial. Por lo tanto, el uso que haga el Usuario de la información o de los análisis es bajo su exclusiva cuenta, costo y riesgo.
                    </p>

                    <h4 class="text-sm font-semibold text-white">Obligación de Indemnidad:</h4>
                    <p>
                        El Usuario se compromete a defender, indemnizar y mantener indemne a Auditor-IA frente a cualquier demanda, querella, multa, sanción, costo judicial u honorario de abogados derivado de reclamaciones de terceros debido a un mal uso del Servicio, violación de estos términos, o por decisiones de negocio u operaciones comerciales que el Usuario ejecute basándose en los informes analíticos del sistema.
                    </p>
                </section>

                <!-- Section 5: Privacidad -->
                <section id="privacidad" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">05.</span>
                        Privacidad y Protección de Datos (Ley N° 19.628)
                    </h2>
                    <p>
                        La privacidad de los documentos legales subidos por el Usuario es una prioridad crítica para Auditor-IA. Todo procesamiento de datos personales e información confidencial se realiza en conformidad estricta con la <strong>Ley N° 19.628 sobre Protección de la Vida Privada</strong> y las directrices de ciberseguridad chilenas:
                    </p>
                    <ul class="list-disc pl-5 space-y-2 text-zinc-400 text-[14px]">
                        <li><strong>Aislamiento de Datos (Multi-Tenant):</strong> Cada documento cargado se asocia de forma exclusiva al identificador de organización (Tenant) del usuario autenticado, asegurando la inaccesibilidad absoluta de la información por parte de terceros.</li>
                        <li><strong>Retención:</strong> El almacenamiento se efectúa en servidores privados con encriptación en tránsito y reposo. Los datos no son vendidos, arrendados ni utilizados para el entrenamiento de modelos de IA de terceros sin consentimiento expreso.</li>
                        <li><strong>Derechos ARCO:</strong> El Usuario podrá solicitar en cualquier momento el acceso, rectificación, cancelación u oposición de sus datos personales a través de los canales internos habilitados.</li>
                    </ul>
                </section>

                <!-- Section 6: Propiedad -->
                <section id="propiedad" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">06.</span>
                        Propiedad Intelectual
                    </h2>
                    <p>
                        Todos los derechos de propiedad intelectual asociados a la Plataforma (código fuente, diseño visual, algoritmos de procesamiento, marcas comerciales, logotipos y metodologías de auditoría automatizada) pertenecen exclusivamente a Auditor-IA.
                    </p>
                    <p>
                        Se concede al Usuario una licencia de uso de software no exclusiva, revocable, intransferible y limitada para acceder y utilizar el Servicio de acuerdo a los planes de suscripción contratados. Los documentos analizados subidos a la plataforma por los usuarios siguen siendo de su exclusiva propiedad intelectual.
                    </p>
                </section>

                <!-- Section 7: Jurisdicción -->
                <section id="jurisdiccion" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span class="text-violet-400 text-sm font-mono">07.</span>
                        Jurisdicción y Ley Aplicable
                    </h2>
                    <p>
                        Estos Términos y Condiciones se rigen e interpretan en su totalidad bajo las leyes de la <strong>República de Chile</strong>.
                    </p>
                    <p>
                        Para resolver cualquier controversia, disputa o reclamación que surja del uso del Servicio, interpretación, cumplimiento o incumplimiento de estos términos, las partes acuerdan someterse irrevocablemente a la jurisdicción exclusiva de los <strong>Tribunales Ordinarios de Justicia de la ciudad de Santiago de Chile</strong>, renunciando de forma expresa a cualquier otro fuero que pudiere corresponderles por domicilio presente o futuro.
                    </p>
                </section>

            </div>
        </div>
    </div>
</div>

{{-- Dynamic TOC Active State script --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sections = document.querySelectorAll('section[id]');
        const tocLinks = document.querySelectorAll('.toc-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            tocLinks.forEach(link => {
                link.classList.remove('text-violet-400', 'font-semibold', 'border-l-2', 'border-violet-500', 'pl-4', 'bg-violet-950/10');
                link.classList.add('text-zinc-400', 'font-medium');
                
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.remove('text-zinc-400', 'font-medium');
                    link.classList.add('text-violet-400', 'font-semibold', 'border-l-2', 'border-violet-500', 'pl-4', 'bg-violet-950/10');
                }
            });
        });
    });
</script>
@endsection
