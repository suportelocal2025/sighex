@extends('layouts.app')

@section('title', 'Superintendente - Relatórios')
@section('header', 'Relatórios')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/relatorios" class="nav-link active"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-earmark-bar-graph display-1 text-primary mb-3"></i>
                <h4>Relatório de Orçamento</h4>
                <p class="text-muted">Visão consolidada do orçamento anual, distribuição e gastos por unidade.</p>
                <a href="#" class="btn btn-primary">
                    <i class="bi bi-download"></i> Gerar Relatório
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-check display-1 text-success mb-3"></i>
                <h4>Relatório de Escalas</h4>
                <p class="text-muted">Resumo das escalas aprovadas e executadas por período.</p>
                <a href="#" class="btn btn-success">
                    <i class="bi bi-download"></i> Gerar Relatório
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-clock-history display-1 text-info mb-3"></i>
                <h4>Relatório de Horas</h4>
                <p class="text-muted">Horas trabalhadas por servidor com detalhamento de horas diurnas e noturnas.</p>
                <a href="/superintendente/relatorio-horas" class="btn btn-info">
                    <i class="bi bi-clock"></i> Acessar Relatório
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-currency-dollar display-1 text-warning mb-3"></i>
                <h4>Relatório Financeiro</h4>
                <p class="text-muted">Valores executados por escala e unidade com comparação ao orçamento previsto.</p>
                <a href="/superintendente/relatorio-financeiro" class="btn btn-warning">
                    <i class="bi bi-cash-stack"></i> Acessar Relatório
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
