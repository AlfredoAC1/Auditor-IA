@extends('layouts.app')

@section('title', 'Panel de Administración - Auditor-IA')

@section('content')
<div class="p-6 md:p-8 lg:p-10 flex flex-col gap-8 max-w-7xl mx-auto w-full">

    {{-- Header --}}
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b-2 border-gunmetal">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-serif font-bold text-icy tracking-tight">Panel de Administración</h1>
                <span class="px-2.5 py-0.5 text-[11px] font-mono font-bold uppercase tracking-wider bg-icy/20 text-icy border border-icy/40 shadow-brutal-sm">
                    SUPERADMIN
                </span>
            </div>
            <p class="text-[13px] font-mono text-icy/70">Control global de usuarios, consumo de tokens, métricas de análisis y soporte</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.create') }}" 
               class="flex items-center gap-2 px-4 py-2 bg-navy-dark hover:bg-navy-light border border-icy/50 text-[12px] font-mono font-bold text-icy shadow-brutal-sm hover:-translate-x-px hover:-translate-y-px transition-all uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Usuario / Admin
            </a>
            <a href="{{ route('admin.tickets.index') }}" 
               class="relative flex items-center gap-2 px-4 py-2 bg-gunmetal-card hover:bg-gunmetal border border-gunmetal text-[12px] font-mono font-bold text-icy shadow-brutal-sm transition-all uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Tickets
                @if($stats['new_tickets_count'] > 0)
                    <span class="px-1.5 py-0.2 bg-amber-500 text-black text-[10px] font-black rounded-xs">
                        {{ $stats['new_tickets_count'] }}
                    </span>
                @endif
            </a>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-[#1B4332] border-2 border-gunmetal text-icy px-4 py-3 shadow-brutal-sm text-[13px] font-mono font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Total Users --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col justify-between shadow-brutal">
            <div class="flex items-center justify-between text-icy/60 mb-2">
                <span class="text-[11px] font-mono uppercase font-bold tracking-wider">Usuarios</span>
                <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="text-3xl font-mono font-black text-icy">{{ number_format($stats['total_users']) }}</div>
            <a href="{{ route('admin.users.index') }}" class="text-[11px] font-mono text-icy/60 hover:text-icy underline mt-2">Gestionar usuarios →</a>
        </div>

        {{-- Total Organizations --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col justify-between shadow-brutal">
            <div class="flex items-center justify-between text-icy/60 mb-2">
                <span class="text-[11px] font-mono uppercase font-bold tracking-wider">Organizaciones</span>
                <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="text-3xl font-mono font-black text-icy">{{ number_format($stats['total_organizations']) }}</div>
            <a href="{{ route('admin.organizations.index') }}" class="text-[11px] font-mono text-icy/60 hover:text-icy underline mt-2">Ver tenants →</a>
        </div>

        {{-- Total Analyses --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col justify-between shadow-brutal">
            <div class="flex items-center justify-between text-icy/60 mb-2">
                <span class="text-[11px] font-mono uppercase font-bold tracking-wider">Análisis Totales</span>
                <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div class="text-3xl font-mono font-black text-icy">{{ number_format($stats['total_analyses']) }}</div>
            <a href="{{ route('admin.analyses.index') }}" class="text-[11px] font-mono text-icy/60 hover:text-icy underline mt-2">Ver auditoría →</a>
        </div>

        {{-- Total Tokens --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col justify-between shadow-brutal">
            <div class="flex items-center justify-between text-icy/60 mb-2">
                <span class="text-[11px] font-mono uppercase font-bold tracking-wider">Tokens IA</span>
                <svg class="w-4 h-4 text-icy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div class="text-2xl lg:text-3xl font-mono font-black text-icy truncate" title="{{ number_format($stats['total_tokens']) }}">
                {{ $stats['total_tokens'] >= 1000000 ? number_format($stats['total_tokens'] / 1000000, 2) . 'M' : number_format($stats['total_tokens'] / 1000, 1) . 'k' }}
            </div>
            <span class="text-[11px] font-mono text-icy/60 mt-2">In/Out combinados</span>
        </div>

        {{-- Tickets Status --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-5 flex flex-col justify-between shadow-brutal col-span-2 md:col-span-1">
            <div class="flex items-center justify-between text-icy/60 mb-2">
                <span class="text-[11px] font-mono uppercase font-bold tracking-wider">Tickets Bug</span>
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-mono font-black text-amber-400">{{ $stats['new_tickets_count'] }}</span>
                <span class="text-[12px] font-mono text-icy/60">nuevos / {{ $stats['resolved_tickets_count'] }} resueltos</span>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="text-[11px] font-mono text-icy/60 hover:text-icy underline mt-2">Revisar tickets →</a>
        </div>
    </div>

    {{-- Graphs Row 1: Token Usage Over Time & Analyses Distribution --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Token Usage Trend (Area / Line Chart) --}}
        <div class="lg:col-span-2 bg-gunmetal-card border-2 border-gunmetal p-6 shadow-brutal flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gunmetal pb-3">
                <div>
                    <h2 class="text-lg font-serif font-bold text-icy">Tendencia de Consumo de Tokens (Últimos 14 días)</h2>
                    <p class="text-[12px] font-mono text-icy/60">Desglose diario de tokens de entrada (prompt) y salida (completado)</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-mono">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-icy inline-block"></span> Total</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-sky-400/60 inline-block"></span> Entrada</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-indigo-400/60 inline-block"></span> Salida</span>
                </div>
            </div>
            <div class="h-64 sm:h-72 w-full relative">
                <canvas id="tokenTrendChart"></canvas>
            </div>
        </div>

        {{-- Analysis Type Distribution (Doughnut) --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-6 shadow-brutal flex flex-col gap-4">
            <div class="border-b border-gunmetal pb-3">
                <h2 class="text-lg font-serif font-bold text-icy">Tipos de Análisis</h2>
                <p class="text-[12px] font-mono text-icy/60">Distribución de modalidades solicitadas</p>
            </div>
            <div class="h-64 sm:h-72 w-full relative flex items-center justify-center">
                <canvas id="analysisTypeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Graphs Row 2: Analyses per User & Token Usage by User --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Analyses per User (Bar Chart) --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-6 shadow-brutal flex flex-col gap-4">
            <div class="border-b border-gunmetal pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-serif font-bold text-icy">Análisis por Usuario (Top Activos)</h2>
                    <p class="text-[12px] font-mono text-icy/60">Cantidad de documentos procesados por usuario</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-[11px] font-mono text-icy hover:underline">Ver todos</a>
            </div>
            <div class="h-64 sm:h-72 w-full relative">
                <canvas id="analysesPerUserChart"></canvas>
            </div>
        </div>

        {{-- Token Usage by User (Horizontal Bar Chart) --}}
        <div class="bg-gunmetal-card border-2 border-gunmetal p-6 shadow-brutal flex flex-col gap-4">
            <div class="border-b border-gunmetal pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-serif font-bold text-icy">Consumo de Tokens por Usuario</h2>
                    <p class="text-[12px] font-mono text-icy/60">Usuarios con mayor impacto en cuota LLM</p>
                </div>
                <a href="{{ route('admin.analyses.index') }}" class="text-[11px] font-mono text-icy hover:underline">Auditar llamadas</a>
            </div>
            <div class="h-64 sm:h-72 w-full relative">
                <canvas id="tokensByUserChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.color = '#A4D8FF';
    Chart.defaults.font.family = "'JetBrains Mono', monospace";
    Chart.defaults.font.size = 11;

    const gridStyle = {
        color: 'rgba(53, 57, 60, 0.6)',
        borderColor: '#35393C',
    };

    // -------------------------------------------------------------
    // Chart 1: Token Usage Trend (Line Chart)
    // -------------------------------------------------------------
    const trendCtx = document.getElementById('tokenTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($tokenTrend['labels']),
                datasets: [
                    {
                        label: 'Total Tokens',
                        data: @json($tokenTrend['total_tokens']),
                        borderColor: '#A4D8FF',
                        backgroundColor: 'rgba(164, 216, 255, 0.1)',
                        fill: true,
                        tension: 0.25,
                        borderWidth: 2,
                        pointBackgroundColor: '#A4D8FF',
                    },
                    {
                        label: 'Tokens Entrada',
                        data: @json($tokenTrend['input_tokens']),
                        borderColor: '#38BDF8',
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        borderWidth: 1.5,
                        pointRadius: 2,
                    },
                    {
                        label: 'Tokens Salida',
                        data: @json($tokenTrend['output_tokens']),
                        borderColor: '#818CF8',
                        backgroundColor: 'transparent',
                        borderDash: [2, 2],
                        borderWidth: 1.5,
                        pointRadius: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F1E36',
                        titleColor: '#A4D8FF',
                        bodyColor: '#E2F1FF',
                        borderColor: '#35393C',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: { grid: gridStyle },
                    y: { 
                        grid: gridStyle,
                        beginAtZero: true,
                        ticks: {
                            callback: (v) => v >= 1000 ? (v/1000) + 'k' : v
                        }
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 2: Analysis Type Distribution (Doughnut)
    // -------------------------------------------------------------
    const typesCtx = document.getElementById('analysisTypeChart');
    if (typesCtx) {
        const typeLabels = Object.keys(@json($analysisTypes));
        const typeValues = Object.values(@json($analysisTypes));
        new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: [
                        '#A4D8FF',
                        '#38BDF8',
                        '#818CF8',
                        '#34D399',
                        '#FBBF24',
                        '#F87171'
                    ],
                    borderColor: '#15181B',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
                    },
                    tooltip: {
                        backgroundColor: '#0F1E36',
                        titleColor: '#A4D8FF',
                        bodyColor: '#E2F1FF',
                        borderColor: '#35393C',
                        borderWidth: 1,
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 3: Analyses per User (Bar Chart)
    // -------------------------------------------------------------
    const userAnalysesCtx = document.getElementById('analysesPerUserChart');
    if (userAnalysesCtx) {
        const userAnalysesData = @json($analysesPerUser);
        new Chart(userAnalysesCtx, {
            type: 'bar',
            data: {
                labels: userAnalysesData.map(u => u.name || u.email),
                datasets: [{
                    label: 'Cantidad de Análisis',
                    data: userAnalysesData.map(u => u.count),
                    backgroundColor: '#A4D8FF',
                    borderColor: '#35393C',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F1E36',
                        titleColor: '#A4D8FF',
                        bodyColor: '#E2F1FF',
                        borderColor: '#35393C',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: { grid: gridStyle },
                    y: { 
                        grid: gridStyle,
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 4: Token Usage by User (Horizontal Bar Chart)
    // -------------------------------------------------------------
    const userTokensCtx = document.getElementById('tokensByUserChart');
    if (userTokensCtx) {
        const userTokensData = @json($tokenUsageByUser);
        new Chart(userTokensCtx, {
            type: 'bar',
            data: {
                labels: userTokensData.map(u => u.name || u.email),
                datasets: [
                    {
                        label: 'Entrada',
                        data: userTokensData.map(u => u.input_tokens),
                        backgroundColor: '#38BDF8',
                    },
                    {
                        label: 'Salida',
                        data: userTokensData.map(u => u.output_tokens),
                        backgroundColor: '#818CF8',
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    },
                    tooltip: {
                        backgroundColor: '#0F1E36',
                        titleColor: '#A4D8FF',
                        bodyColor: '#E2F1FF',
                        borderColor: '#35393C',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: { 
                        stacked: true,
                        grid: gridStyle,
                        ticks: {
                            callback: (v) => v >= 1000 ? (v/1000) + 'k' : v
                        }
                    },
                    y: { 
                        stacked: true,
                        grid: gridStyle 
                    }
                }
            }
        });
    }
});
</script>
@endpush
