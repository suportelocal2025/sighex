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

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-wallet2 text-primary"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Orçamento</h6>
                        <h4 class="mb-0 text-truncate">R$ {{ number_format($orcamento, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-graph-down text-danger"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Gasto</h6>
                        <h4 class="mb-0 text-truncate">R$ {{ number_format($gasto, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-cash-coin text-success"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Disponível</h6>
                        <h4 class="mb-0 text-truncate">R$ {{ number_format($disponivel, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stat h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                        <i class="bi bi-clock-history text-info"></i>
                    </div>
                    <div class="min-width-0">
                        <h6 class="text-muted mb-0 small">Horas Exec.</h6>
                        <h4 class="mb-0 text-truncate">{{ number_format($horasExecutadas, 0, ',', '.') }}h</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Orçamento Mensal {{ $ano }} <small class="text-muted">(Margem: {{ number_format($marginPercentual, 0) }}%)</small></h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($mesesInfo as $m => $info)
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card h-100 {{ $info['mesAtual'] ? 'border-primary border-2' : '' }} {{ $info['ultrapassouMargem'] ? 'bg-danger bg-opacity-10' : '' }}">
                    <div class="card-body p-2 text-center">
                        <div class="fw-bold {{ $info['mesAtual'] ? 'text-primary' : '' }}">{{ $info['nome'] }}</div>
                        @if($info['ultrapassouMargem'])
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        @endif
                        <div class="progress my-2" style="height: 8px;">
                            @php
                                $barColor = $info['percentualUso'] > 100 ? 'bg-danger' : ($info['percentualUso'] > 80 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $barColor }}" style="width: {{ min(100, $info['percentualUso']) }}%"></div>
                        </div>
                        <div class="small">
                            <div class="text-muted">Orç: R$ {{ number_format($info['orcamento'], 0, ',', '.') }}</div>
                            <div class="{{ $info['gasto'] > $info['orcamento'] ? 'text-danger fw-bold' : '' }}">
                                Gasto: R$ {{ number_format($info['gasto'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
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
