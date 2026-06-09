@extends('layouts.app')

@section('title', 'Detalle de Análisis - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex-1 flex flex-col items-center justify-center text-center gap-5 max-w-lg mx-auto w-full min-h-[70vh]">
    <!-- Empty State Icon -->
    <div class="w-16 h-16 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-550 shadow-inner">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    </div>
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold text-white tracking-tight">Ningún análisis activo</h1>
        <p class="text-[13px] text-zinc-500 leading-relaxed">
            No has seleccionado ningún contrato para auditar. Sube un archivo en tu Dashboard para generar el reporte y visualizar el detalle aquí.
        </p>
    </div>
    <a href="{{ url('/dashboard') }}" class="mt-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-[13px] font-bold text-white shadow-lg shadow-violet-500/10 hover:shadow-violet-500/20 transition-all duration-200">
        Ir al Dashboard
    </a>
</div>
@endsection
