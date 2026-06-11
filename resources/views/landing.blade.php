@extends('layouts.guest')

@section('title', 'Auditor-IA — Analiza contratos con IA antes de firmar')
@section('meta_description', 'Sube tus contratos PDF o DOCX y obtén análisis de riesgos, cláusulas y recomendaciones basados en la legislación chilena.')

@section('content')
<div class="relative overflow-hidden">

    {{-- Background glow --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-violet-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-fuchsia-600/5 rounded-full blur-[100px]"></div>
    </div>

    {{-- Hero --}}
    <section class="relative max-w-6xl mx-auto px-6 pt-20 pb-24 md:pt-28 md:pb-32 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-violet-500/10 border border-violet-500/20 text-[12px] font-semibold text-violet-300 mb-8">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
            Inteligencia artificial para documentos legales
        </div>

        <h1 class="text-4xl md:text-6xl font-bold text-white tracking-tight leading-[1.1] max-w-4xl mx-auto mb-6">
            Entiende tus contratos
            <span class="bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">antes de firmar</span>
        </h1>

        <p class="text-lg md:text-xl text-zinc-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            Sube contratos en PDF o DOCX y recibe un análisis claro de cláusulas, riesgos y recomendaciones según la legislación chilena.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-[15px] font-bold text-white shadow-lg shadow-violet-500/20 transition-all">
                Analizar mi primer contrato
            </a>
            <a href="#planes" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-transparent border border-zinc-700/60 hover:bg-zinc-800/30 text-[15px] font-medium text-zinc-300 transition-all">
                Ver planes
            </a>
        </div>
    </section>

    {{-- How it works --}}
    <section id="como-funciona" class="relative max-w-6xl mx-auto px-6 pb-24 scroll-mt-24">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight mb-3">Cómo funciona</h2>
            <p class="text-zinc-400 text-[15px]">Tres pasos para entender cualquier documento legal</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            @foreach ([
                ['step' => '01', 'title' => 'Sube tu documento', 'desc' => 'Arrastra un PDF o DOCX de tu contrato, arriendo, NDA u otro documento legal.'],
                ['step' => '02', 'title' => 'Procesamiento inteligente', 'desc' => 'Extraemos el texto, lo analizamos con IA y lo cruzamos con el marco legal chileno.'],
                ['step' => '03', 'title' => 'Recibe tu informe', 'desc' => 'Obtén un resumen en lenguaje simple, riesgos identificados y recomendaciones accionables.'],
            ] as $feature)
            <div class="bg-[#161616] border border-zinc-800/60 rounded-2xl p-8 flex flex-col gap-4 hover:border-zinc-700/60 transition-colors">
                <span class="text-[13px] font-bold text-violet-400 tracking-widest">{{ $feature['step'] }}</span>
                <h3 class="text-lg font-bold text-white">{{ $feature['title'] }}</h3>
                <p class="text-[14px] text-zinc-400 leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Pricing --}}
    <x-pricing-section />

    {{-- Final CTA --}}
    <section class="relative max-w-6xl mx-auto px-6 pb-24">
        <div class="bg-[#161616] border border-zinc-800/60 rounded-3xl p-10 md:p-14 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight mb-4">
                Protege tus decisiones legales
            </h2>
            <p class="text-zinc-400 text-[15px] max-w-xl mx-auto mb-8">
                Comienza gratis con 3 documentos al mes. Escala a Pro o Lifetime cuando necesites más capacidad.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('login') }}" class="inline-flex px-8 py-4 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-[15px] font-bold text-white shadow-lg shadow-violet-500/20 transition-all">
                    Crear cuenta gratis
                </a>
                <a href="{{ route('dashboard') }}" class="inline-flex px-8 py-4 rounded-xl bg-[#1d1d1d] hover:bg-[#262626] border border-zinc-800 hover:border-zinc-700 text-[15px] font-bold text-white transition-all">
                    Ir al panel
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
