@extends('layouts.app')

@section('title', 'Superintendente - Detalhar Escala')
@section('header', 'Detalhes da Escala')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link active"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
@php
    $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $statusClass = match($escala->status) {
        'pendente' => 'bg-warning text-dark',
        'aprovada' => 'bg-success',
        'executada' => 'bg-info',
        'rejeitada' => 'bg-danger',
        default => 'bg-secondary'
    };
@endphp

<div class="mb-3">
    <a href="/superintendente/escalas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações da Escala</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Unidade</label>
                        <p class="fw-bold mb-0">{{ $escala->unidade->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-muted small">Mês/Ano</label>
                        <p class="fw-bold mb-0">{{ $meses[$escala->mes] }}/{{ $escala->ano }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0"><span class="badge {{ $statusClass }}">{{ ucfirst($escala->status) }}</span></p>
                    </div>
                </div>
                @if($escala->status === 'executada' && $escala->valor_executado)
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Valor Executado</label>
                        <p class="fw-bold text-success mb-0">R$ {{ number_format($escala->valor_executado, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Resumo</h5>
            </div>
            <div class="card-body text-center">
                <div class="display-4 text-primary fw-bold">
                    {{ $alocacoes->sum(fn($a) => ($a->horas ?? 0) + ($a->horas_abono ?? 0)) }}h
                </div>
                <p class="text-muted">Total de Horas</p>
                <div class="fs-4 text-secondary">
                    {{ $resumoPorServidor->count() }}
                </div>
                <p class="text-muted mb-0">Servidores</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Resumo por Servidor</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Servidor</th>
                        <th>Matrícula</th>
                        <th>Dias</th>
                        <th class="text-end">Total Horas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumoPorServidor as $servidor)
                    <tr>
                        <td>{{ $servidor['nome'] }}</td>
                        <td>{{ $servidor['matricula'] }}</td>
                        <td>{{ implode(', ', array_map(fn($d) => str_pad($d, 2, '0', STR_PAD_LEFT), $servidor['dias'])) }}</td>
                        <td class="text-end fw-bold">{{ $servidor['total_horas'] }}h</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
