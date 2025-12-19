@extends('layouts.app')

@section('title', 'Diretor - Dashboard')
@section('header', 'Dashboard do Diretor')

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
@endsection

@section('content')
@if($escalasRejeitadas > 0)
<div class="alert alert-danger d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
    <div>
        <strong>Atenção!</strong> Você tem {{ $escalasRejeitadas }} escala(s) rejeitada(s) que precisa(m) de correção.
    </div>
</div>
@endif

@if($escalasAprovadas > 0)
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
    <div>
        {{ $escalasAprovadas }} escala(s) aprovada(s) neste ano.
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-wallet2 text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Orçamento</h6>
                        <h4 class="mb-0">R$ {{ number_format($orcamento, 2, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded-3 p-3 me-3">
                        <i class="bi bi-graph-down text-danger fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Gasto</h6>
                        <h4 class="mb-0">R$ {{ number_format($gasto, 2, ',', '.') }}</h4>
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
                        <i class="bi bi-cash-coin text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Disponível</h6>
                        <h4 class="mb-0">R$ {{ number_format($disponivel, 2, ',', '.') }}</h4>
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
                        <i class="bi bi-clock-history text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Horas Executadas</h6>
                        <h4 class="mb-0">{{ number_format($horasExecutadas, 0, ',', '.') }}h</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Escalas {{ $ano }}</h5>
        <a href="/diretor/escala-mensal" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Escala
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($escalas as $escala)
                    <tr>
                        <td>{{ str_pad($escala->mes, 2, '0', STR_PAD_LEFT) }}/{{ $escala->ano }}</td>
                        <td>
                            @switch($escala->status)
                                @case('rascunho')
                                    <span class="badge bg-secondary">Rascunho</span>
                                    @break
                                @case('pendente')
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                    @break
                                @case('aprovada')
                                    <span class="badge bg-success">Aprovada</span>
                                    @break
                                @case('rejeitada')
                                    <span class="badge bg-danger">Rejeitada</span>
                                    @break
                                @case('executada')
                                    <span class="badge bg-info">Executada</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <a href="/diretor/escala-mensal?mes={{ $escala->mes }}&ano={{ $escala->ano }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Nenhuma escala encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
