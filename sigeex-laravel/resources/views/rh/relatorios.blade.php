@extends('layouts.app')

@section('title', 'RH - Relatórios')
@section('header', 'Relatórios')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-clock-history display-1 text-primary mb-3"></i>
                <h4>Relatório de Horas</h4>
                <p class="text-muted">Horas trabalhadas por servidor em determinado período.</p>
                <a href="#" class="btn btn-primary">
                    <i class="bi bi-download"></i> Gerar Relatório
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-cash-stack display-1 text-success mb-3"></i>
                <h4>Relatório Financeiro</h4>
                <p class="text-muted">Valores executados por escala e unidade.</p>
                <a href="#" class="btn btn-success">
                    <i class="bi bi-download"></i> Gerar Relatório
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
