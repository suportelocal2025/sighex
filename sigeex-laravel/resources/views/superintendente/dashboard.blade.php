@extends('layouts.app')

@section('title', 'Superintendente - Dashboard')
@section('header', 'Dashboard do Superintendente')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-cash-stack text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Orçamento Total</h6>
                        <h4 class="mb-0">R$ {{ number_format($valorTotal, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-piggy-bank text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Reserva Técnica</h6>
                        <h4 class="mb-0">R$ {{ number_format($reservaTecnica, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-send text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Distribuído</h6>
                        <h4 class="mb-0">R$ {{ number_format($valorDistribuido, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Disponível</h6>
                        <h4 class="mb-0">R$ {{ number_format($valorDisponivel, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Distribuição por Unidade - {{ $ano }}</h5>
            </div>
            <div class="card-body">
                <canvas id="chartDistribuicao" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Resumo</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Unidades Ativas</span>
                        <strong>{{ $unidades }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Escalas Aprovadas</span>
                        <strong class="text-success">{{ $escalasAprovadas }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Escalas Executadas</span>
                        <strong class="text-info">{{ $escalasExecutadas }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Valor Gasto</span>
                        <strong class="text-danger">R$ {{ number_format($valorGasto, 2, ',', '.') }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('chartDistribuicao').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($distribuicoes->pluck('unidade.nome')) !!},
        datasets: [{
            label: 'Distribuído',
            data: {!! json_encode($distribuicoes->pluck('valor_distribuido')) !!},
            backgroundColor: 'rgba(26, 35, 126, 0.8)',
        }, {
            label: 'Gasto',
            data: {!! json_encode($distribuicoes->pluck('valor_gasto')) !!},
            backgroundColor: 'rgba(220, 53, 69, 0.8)',
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => 'R$ ' + value.toLocaleString('pt-BR')
                }
            }
        }
    }
});
</script>
@endpush
