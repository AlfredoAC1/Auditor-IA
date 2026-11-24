@extends('layouts.guest')

@section('title', 'Auditor-IA — Analiza contratos con IA antes de firmar')
@section('meta_description', 'Sube tus contratos PDF o DOCX y obtén análisis de riesgos, cláusulas y recomendaciones basados en la legislación chilena.')

@section('content')
<div class="relative overflow-hidden bg-gunmetal-dark">

    {{-- Hero --}}
    <section class="relative max-w-6xl mx-auto px-6 pt-20 pb-24 md:pt-28 md:pb-32 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-navy-dark border border-icy/40 text-[12px] font-mono font-bold text-icy shadow-brutal-sm mb-8 uppercase tracking-wider">
            <span class="w-1.5 h-1.5 bg-icy"></span>
            Inteligencia artificial para documentos legales
        </div>

        <h1 class="text-4xl md:text-6xl font-serif font-bold text-icy tracking-tight leading-[1.1] max-w-4xl mx-auto mb-6">
            Entiende tus contratos
            <span class="text-icy underline decoration-navy-dark underline-offset-8">antes de firmar</span>
        </h1>

        <p class="text-lg md:text-xl font-mono text-icy/70 max-w-2xl mx-auto mb-10 leading-relaxed">
            Sube contratos en PDF o DOCX y recibe un análisis claro de cláusulas, riesgos y recomendaciones según la legislación chilena.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ ($isStaticDemo ?? false) ? route('demo.dashboard') : route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[15px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                {{ ($isStaticDemo ?? false) ? 'Explorar Demo Pro' : 'Analizar mi primer contrato' }}
            </a>
            <a href="#planes" class="w-full sm:w-auto px-8 py-4 bg-gunmetal-dark border-2 border-gunmetal hover:bg-navy-light text-[15px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                Ver planes
            </a>
        </div>
    </section>

    {{-- How it works --}}
    <section id="como-funciona" class="relative max-w-6xl mx-auto px-6 pb-24 scroll-mt-24">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight mb-3">Cómo funciona</h2>
            <p class="font-mono text-icy/70 text-[15px]">Tres pasos para entender cualquier documento legal</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            @foreach ([
                ['step' => '01', 'title' => 'Sube tu documento', 'desc' => 'Arrastra un PDF o DOCX de tu contrato, arriendo, NDA u otro documento legal.'],
                ['step' => '02', 'title' => 'Procesamiento inteligente', 'desc' => 'Extraemos el texto, lo analizamos con IA y lo cruzamos con el marco legal chileno.'],
                ['step' => '03', 'title' => 'Recibe tu informe', 'desc' => 'Obtén un resumen en lenguaje simple, riesgos identificados y recomendaciones accionables.'],
            ] as $feature)
            <div class="bg-gunmetal-card border-2 border-gunmetal p-8 flex flex-col gap-4 shadow-brutal">
                <span class="text-[13px] font-mono font-bold text-icy uppercase tracking-widest">{{ $feature['step'] }}</span>
                <h3 class="text-lg font-serif font-bold text-icy">{{ $feature['title'] }}</h3>
                <p class="text-[14px] font-sans text-icy/80 leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Pricing --}}
    <x-pricing-section />

    {{-- Final CTA --}}
    <section class="relative max-w-6xl mx-auto px-6 pb-24">
        <div class="bg-gunmetal-card border-2 border-gunmetal p-10 md:p-14 text-center shadow-brutal">
            <h2 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight mb-4">
                Protege tus decisiones legales
            </h2>
            <p class="font-mono text-icy/70 text-[15px] max-w-xl mx-auto mb-8">
                Comienza gratis con 3 documentos al mes. Escala a Pro o Lifetime cuando necesites más capacidad.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @if($isStaticDemo ?? false)
                    <a href="{{ route('demo.dashboard') }}" class="inline-flex px-8 py-4 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[15px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                        Ir al Dashboard Demo Pro
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex px-8 py-4 bg-navy-dark hover:bg-navy-light border border-icy/40 text-[15px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                        Crear cuenta gratis
                    </a>
                    <a href="{{ route('dashboard') }}" class="inline-flex px-8 py-4 bg-gunmetal-dark border-2 border-gunmetal hover:bg-navy-light text-[15px] font-mono font-bold text-icy shadow-brutal hover:-translate-x-px hover:-translate-y-px active:translate-x-px active:translate-y-px active:shadow-none transition-all uppercase tracking-wider">
                        Ir al panel
                    </a>
                @endif
            </div>
        </div>
    </section>

</div>
@endsection


