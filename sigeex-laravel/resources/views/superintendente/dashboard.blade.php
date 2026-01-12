@extends('layouts.app')

@section('title', 'Superintendente - Dashboard')
@section('header', 'Dashboard do Superintendente')

@section('sidebar')
    <a href="/superintendente" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@push('styles')
<style>
.stat-card {
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.stat-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a4480;
}
.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
}
</style>
@endpush

@section('content')
@if(count($escalasAguardandoAprovacao) > 0)
<div class="alert alert-warning mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <h6 class="alert-heading d-flex align-items-center mb-0">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            Escalas Aguardando Sua Aprovação ({{ count($escalasAguardandoAprovacao) }})
        </h6>
        <a href="/superintendente/escalas?status=pendente" class="btn btn-sm btn-warning">
            <i class="bi bi-arrow-right-circle me-1"></i>Ver Escalas
        </a>
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Unidade</th>
                    <th>Período</th>
                    <th>Orçamento Mês</th>
                    <th>Limite (c/ margem)</th>
                    <th>Valor Previsto</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($escalasAguardandoAprovacao as $escala)
                <tr>
                    <td><strong>{{ $escala->unidade->nome ?? 'N/A' }}</strong></td>
                    <td>{{ str_pad($escala->mes, 2, '0', STR_PAD_LEFT) }}/{{ $escala->ano }}</td>
                    <td>R$ {{ number_format($escala->orcamento_mes, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($escala->limite_margem, 2, ',', '.') }}</td>
                    <td class="text-danger fw-bold">R$ {{ number_format($escala->valor_previsto, 2, ',', '.') }}</td>
                    <td>
                        <form method="POST" action="/superintendente/aprovar-escala" class="d-inline">
                            @csrf
                            <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Aprovar escala que excede a margem?')">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        <a href="/superintendente/escala/{{ $escala->id }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(count($alertasViolacao) > 0)
<div class="alert alert-danger mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <h6 class="alert-heading d-flex align-items-center mb-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Alertas de Margem Ultrapassada ({{ count($alertasViolacao) }})
        </h6>
        <form method="POST" action="/superintendente/enviar-alerta-email" class="d-inline">
            @csrf
            <input type="hidden" name="ano" value="{{ $ano }}">
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-envelope me-1"></i>Enviar por Email
            </button>
        </form>
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Unidade</th>
                    <th>Mês</th>
                    <th>Limite</th>
                    <th>Gasto</th>
                    <th>Excedente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alertasViolacao as $alerta)
                <tr>
                    <td><strong>{{ $alerta['unidade_nome'] }}</strong></td>
                    <td>{{ $alerta['mes_nome'] }}/{{ $ano }}</td>
                    <td>R$ {{ number_format($alerta['limite'], 2, ',', '.') }}</td>
                    <td class="text-danger fw-bold">R$ {{ number_format($alerta['gasto'], 2, ',', '.') }}</td>
                    <td class="text-danger">+R$ {{ number_format($alerta['excedente'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="row mb-4">
    <div class="col-12">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <label class="form-label mb-0 me-2">Filtrar por Ano:</label>
                    <select class="form-select form-select-sm d-inline-block w-auto" onchange="window.location.href='?ano='+this.value+'&periodo={{ $periodo }}'">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $ano ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm {{ $periodo == 'mes' ? 'btn-primary' : 'btn-outline-primary' }}" onclick="filtrarPeriodo('mes')">Mês</button>
                    <button type="button" class="btn btn-sm {{ $periodo == 'trimestre' ? 'btn-primary' : 'btn-outline-primary' }}" onclick="filtrarPeriodo('trimestre')">Trimestre</button>
                    <button type="button" class="btn btn-sm {{ $periodo == 'ano' ? 'btn-primary' : 'btn-outline-primary' }}" onclick="filtrarPeriodo('ano')">Ano</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-value">R$ {{ number_format($orcamento?->valor_total ?? 0, 0, ',', '.') }}</div>
                    <div class="stat-label">Orçamento Total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <div>
                    <div class="stat-value">R$ {{ number_format($reservaTecnica, 0, ',', '.') }}</div>
                    <div class="stat-label">Reserva Técnica</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="stat-value">R$ {{ number_format($valorDisponivel, 0, ',', '.') }}</div>
                    <div class="stat-label">Disponível</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-arrow-down-up"></i>
                </div>
                <div>
                    <div class="stat-value">R$ {{ number_format($totalDistribuido, 0, ',', '.') }}</div>
                    <div class="stat-label">Repassado</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="bi bi-graph-down"></i>
                </div>
                <div>
                    <div class="stat-value">R$ {{ number_format($totalGasto, 0, ',', '.') }}</div>
                    <div class="stat-label">Gastos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $totalUnidades }}</div>
                    <div class="stat-label">Unidades</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 h-100">
            <h6 class="card-title mb-3"><i class="bi bi-bar-chart me-2"></i>Gastos x Horas por Unidade</h6>
            <canvas id="chartGastosHoras" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100 d-flex flex-column">
            <h6 class="card-title mb-3"><i class="bi bi-pie-chart me-2"></i>Distribuição de Gastos</h6>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="max-height: 220px;">
                <canvas id="chartDistribuicao"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Status das Unidades</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Unidade</th>
                    <th class="text-end">Repassado (R$)</th>
                    <th class="text-end">Gasto (R$)</th>
                    <th class="text-end">Horas</th>
                    <th class="text-end">Saldo (R$)</th>
                    <th class="text-center">Uso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unidadesStats as $u)
                    @php
                        $saldo = $u->orcamento_distribuido - $u->gasto_total;
                        $percentual = $u->orcamento_distribuido > 0 ? ($u->gasto_total / $u->orcamento_distribuido) * 100 : 0;
                        $corBarra = $percentual > 80 ? 'danger' : ($percentual > 50 ? 'warning' : 'success');
                    @endphp
                    <tr>
                        <td><strong>{{ $u->nome }}</strong></td>
                        <td class="text-end">R$ {{ number_format($u->orcamento_distribuido, 2, ',', '.') }}</td>
                        <td class="text-end">R$ {{ number_format($u->gasto_total, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($u->horas_total, 0, ',', '.') }}h</td>
                        <td class="text-end {{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                            R$ {{ number_format($saldo, 2, ',', '.') }}
                        </td>
                        <td style="width: 150px;">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $corBarra }}" style="width: {{ min($percentual, 100) }}%">
                                    {{ number_format($percentual, 0) }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filtrarPeriodo(p) {
    const url = new URL(window.location.href);
    url.searchParams.set('periodo', p);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const unidades = {!! json_encode($unidadesStats->pluck('nome')) !!};
    const gastos = {!! json_encode($unidadesStats->pluck('gasto_total')) !!};
    const horas = {!! json_encode($unidadesStats->pluck('horas_total')) !!};
    
    new Chart(document.getElementById('chartGastosHoras'), {
        type: 'bar',
        data: {
            labels: unidades,
            datasets: [
                {
                    label: 'Gasto (R$)',
                    data: gastos,
                    backgroundColor: 'rgba(26, 68, 128, 0.8)',
                    borderRadius: 5
                },
                {
                    label: 'Horas',
                    data: horas,
                    backgroundColor: 'rgba(0, 169, 28, 0.8)',
                    borderRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    const cores = ['#1a4480', '#005ea2', '#00a91c', '#ffbe2e', '#d54309', '#5c5c5c', '#8168b3', '#0076d6'];
    
    new Chart(document.getElementById('chartDistribuicao'), {
        type: 'doughnut',
        data: {
            labels: unidades,
            datasets: [{
                data: gastos,
                backgroundColor: cores.slice(0, unidades.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });
});
</script>
@endpush
