@extends('layouts.guest')

@section('title', 'Términos y Condiciones de Uso — Auditor-IA')
@section('meta_description', 'Conoce los términos y condiciones de uso de Auditor-IA. Exclusión de responsabilidad, alcance del análisis con IA y legislación chilena aplicable.')

@section('content')
<div class="relative overflow-hidden min-h-screen py-16 md:py-24 bg-[#151311]">

    <div class="relative max-w-6xl mx-auto px-6">
        <!-- Breadcrumbs -->
        <div class="mb-8">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 font-mono text-[#EED3BA]/70 hover:text-[#EED3BA] text-sm font-bold transition-colors group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al inicio
            </a>
        </div>

        <!-- Header -->
        <div class="max-w-3xl mb-16">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#4B262F] border border-[#EED3BA]/40 text-[12px] font-mono font-bold text-[#EED3BA] shadow-brutal-sm mb-6 uppercase tracking-wider">
                <span class="w-1.5 h-1.5 bg-[#EED3BA]"></span>
                Marco Legal & Términos de Uso
            </span>
            <h1 class="text-4xl md:text-5xl font-serif font-bold text-[#EED3BA] tracking-tight leading-tight mb-4">
                Términos y <span class="underline decoration-[#4B262F] underline-offset-8">Condiciones de Uso</span>
            </h1>
            <p class="font-mono text-[#EED3BA]/70 text-sm md:text-base">
                Última actualización: 02 de Julio, 2026. Por favor, lea detenidamente este documento antes de utilizar nuestros servicios.
            </p>
        </div>

        <!-- Layout Grid -->
        <div class="grid lg:grid-cols-12 gap-8 items-start">
            
            <!-- Sticky Navigation (Desktop) -->
            <aside class="hidden lg:block lg:col-span-4 sticky top-28 bg-[#2A171B] border-2 border-[#4B262F] p-6 shadow-brutal">
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-[#EED3BA]/60 mb-4 px-2">Tabla de Contenidos</h3>
                <nav class="space-y-1 font-mono" id="terms-toc">
                    <a href="#aceptacion" class="toc-link block px-3 py-2.5 text-[13px] font-medium text-[#EED3BA]/70 hover:text-[#EED3BA] hover:bg-[#151311] transition-all">
                        1. Aceptación de los Términos
                    </a>
                    <a href="#objeto" class="toc-link block px-3 py-2.5 text-[13px] font-medium text-[#EED3BA]/70 hover:text-[#EED3BA] hover:bg-[#151311] transition-all">
                        2. Naturaleza y Objeto del Servicio
                    </a>
                    <a href="#asesoria" class="toc-link block px-3 py-2.5 text-[13px] font-medium text-[#EED3BA]/70 hover:text-[#EED3BA] hover:bg-[#151311] transition-all">
                        3. Exclusión de Asesoría Legal
                    </a>
                    <a href="#exencion" class="toc-link block px-3 py-2.5 text-[13px] font-bold text-[#EED3BA] bg-[#4B262F] border-l-4 border-[#EED3BA] pl-4">
                        4. Liberación de Responsabilidad e Indemnidad
                    </a>
                    <a href="#privacidad" class="toc-link block px-3 py-2.5 text-[13px] font-medium text-[#EED3BA]/70 hover:text-[#EED3BA] hover:bg-[#151311] transition-all">
                        5. Privacidad y Datos Personales
                    </a>
                    <a href="#propiedad" class="toc-link block px-3 py-2.5 text-[13px] font-medium text-[#EED3BA]/70 hover:text-[#EED3BA] hover:bg-[#151311] transition-all">
                        6. Propiedad Intelectual
                    </a>
                    <a href="#jurisdiccion" class="toc-link block px-3 py-2.5 text-[13px] font-medium text-[#EED3BA]/70 hover:text-[#EED3BA] hover:bg-[#151311] transition-all">
                        7. Jurisdicción y Ley Aplicable
                    </a>
                </nav>
            </aside>

            <!-- Legal Text Content -->
            <div class="lg:col-span-8 bg-[#2A171B] border-2 border-[#4B262F] p-6 md:p-12 shadow-brutal space-y-12 text-[#EED3BA]/90 leading-relaxed text-[14.5px]">
                
                <!-- Section 1: Aceptación -->
                <section id="aceptacion" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">01.</span>
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
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">02.</span>
                        Naturaleza y Objeto del Servicio
                    </h2>
                    <p>
                        Auditor-IA es una herramienta SaaS (Software como Servicio) de soporte analítico basada en inteligencia artificial (modelos de lenguaje de gran tamaño - LLM) y tecnologías de procesamiento documental. El propósito de la Plataforma es ayudar al Usuario a automatizar la extracción de texto, identificar cláusulas clave, detectar riesgos potenciales y generar sugerencias preliminares en base a patrones definidos y alineados con la legislación de la República de Chile.
                    </p>
                    <p>
                        El Usuario reconoce que los resultados generados por la plataforma son análisis estadísticos y probabilísticos y que la tecnología aplicada no es infalible. Los análisis son de carácter general y preliminar, y en ningún caso constituyen dictámenes jurídicos.
                    </p>
                </section>

                <!-- Section 3: Exclusión de Asesoría Legal -->
                <section id="asesoria" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">03.</span>
                        Exclusión de Asesoría Legal Profesional
                    </h2>
                    
                    {{-- Alert Box --}}
                    <div class="p-4 bg-[#151311] border-2 border-[#4B262F] text-[#F8CECC] flex gap-3 text-[13.5px] font-mono shadow-brutal-sm">
                        <svg class="w-5 h-5 shrink-0 mt-0.5 text-[#F8CECC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="font-bold uppercase">Advertencia Legal:</span> Auditor-IA no es una firma de abogados, no provee representación legal y no ofrece asesoría jurídica profesional en los términos del ordenamiento jurídico chileno.
                        </div>
                    </div>

                    <p>
                        En estricto cumplimiento del <strong>Código Orgánico de Tribunales de Chile</strong> (en especial, sus artículos 520 y siguientes, y las disposiciones penales relativas al ejercicio ilegal de la profesión), el Usuario declara y acepta que:
                    </p>
                    <ul class="list-disc pl-5 space-y-2 font-mono text-[#EED3BA]/80 text-[13.5px]">
                        <li>El uso de la Plataforma no crea una relación abogado-cliente.</li>
                        <li>Los informes y recomendaciones emitidos por Auditor-IA no sustituyen, en ningún caso, la asesoría personalizada, el juicio crítico y la revisión técnica de un abogado habilitado para el ejercicio de la profesión en Chile.</li>
                        <li>La Plataforma recomienda expresamente al Usuario someter todo análisis documental y contrato a la revisión final de un profesional del derecho calificado antes de tomar decisiones vinculantes, realizar firmas o proceder a transacciones.</li>
                    </ul>
                </section>

                <!-- Section 4: EXENCIÓN Y LIBERACIÓN DE RESPONSABILIDAD (CRITICAL CLAUSE) -->
                <section id="exencion" class="scroll-mt-28 space-y-5">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">04.</span>
                        Exención y Liberación de Responsabilidad e Indemnidad
                    </h2>

                    {{-- Premium Highlighted Box for the Exculpation Clause --}}
                    <div class="relative overflow-hidden bg-[#151311] border-2 border-[#4B262F] p-6 md:p-8 space-y-4 shadow-brutal-sm">
                        <h3 class="text-base font-serif font-bold text-[#EED3BA] uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#EED3BA]"></span>
                            Cláusula de Exclusión Absoluta de Responsabilidad
                        </h3>
                        
                        <p class="font-mono text-[#EED3BA] font-bold text-[13px] leading-relaxed uppercase">
                            DE CONFORMIDAD CON LAS NORMAS GENERALES DE LA RESPONSABILIDAD CIVIL CONTRACTUAL Y EXTRACONTRACTUAL BAJO EL CÓDIGO CIVIL DE LA REPÚBLICA DE CHILE, EL USUARIO ACEPTA EXPRESAMENTE LIBERAR DE TODA RESPONSABILIDAD Y EXONERAR EN SU TOTALIDAD A AUDITOR-IA, ASÍ COMO A SUS PROPIETARIOS, DESARROLLADORES, PROVEEDORES, FILIALES Y EMPLEADOS, DE CUALQUIER RECLAMO, DAÑO, PERJUICIO O ACCIÓN LEGAL QUE PUDIERA ORIGINARSE A PARTIR DEL USO DEL SERVICIO.
                        </p>

                        <div class="border-t border-[#4B262F] pt-4 space-y-2 font-mono text-[12.5px] text-[#EED3BA]/70">
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

                    <h4 class="text-sm font-serif font-bold text-[#EED3BA]">Obligación de Indemnidad:</h4>
                    <p>
                        El Usuario se compromete a defender, indemnizar y mantener indemne a Auditor-IA frente a cualquier demanda, querella, multa, sanción, costo judicial u honorario de abogados derivado de reclamaciones de terceros debido a un mal uso del Servicio, violación de estos términos, o por decisiones de negocio u operaciones comerciales que el Usuario ejecute basándose en los informes analíticos del sistema.
                    </p>
                </section>

                <!-- Section 5: Privacidad -->
                <section id="privacidad" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">05.</span>
                        Privacidad y Protección de Datos (Ley N° 19.628)
                    </h2>
                    <p>
                        La privacidad de los documentos legales subidos por el Usuario es una prioridad crítica para Auditor-IA. Todo procesamiento de datos personales e información confidencial se realiza en conformidad estricta con la <strong>Ley N° 19.628 sobre Protección de la Vida Privada</strong> y las directrices de ciberseguridad chilenas:
                    </p>
                    <ul class="list-disc pl-5 space-y-2 font-mono text-[#EED3BA]/80 text-[13.5px]">
                        <li><strong>Aislamiento de Datos (Multi-Tenant):</strong> Cada documento cargado se asocia de forma exclusiva al identificador de organización (Tenant) del usuario autenticado, asegurando la inaccesibilidad absoluta de la información por parte de terceros.</li>
                        <li><strong>Retención:</strong> El almacenamiento se efectúa en servidores privados con encriptación en tránsito y reposo. Los datos no son vendidos, arrendados ni utilizados para el entrenamiento de modelos de IA de terceros sin consentimiento expreso.</li>
                        <li><strong>Derechos ARCO:</strong> El Usuario podrá solicitar en cualquier momento el acceso, rectificación, cancelación u oposición de sus datos personales a través de los canales internos habilitados.</li>
                    </ul>
                </section>

                <!-- Section 6: Propiedad -->
                <section id="propiedad" class="scroll-mt-28 space-y-4">
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">06.</span>
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
                    <h2 class="text-xl font-serif font-bold text-[#EED3BA] tracking-tight flex items-center gap-2">
                        <span class="text-[#EED3BA] text-sm font-mono font-bold">07.</span>
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
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            tocLinks.forEach(link => {
                link.classList.remove('text-[#EED3BA]', 'font-bold', 'bg-[#4B262F]', 'border-l-4', 'border-[#EED3BA]', 'pl-4');
                link.classList.add('text-[#EED3BA]/70', 'font-medium');
                
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.remove('text-[#EED3BA]/70', 'font-medium');
                    link.classList.add('text-[#EED3BA]', 'font-bold', 'bg-[#4B262F]', 'border-l-4', 'border-[#EED3BA]', 'pl-4');
                }
            });
        });
    });
</script>
@endsection

