@extends('layouts.app')

@section('title', 'Diretor - Dashboard')
@section('header', 'Dashboard do Diretor')

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/diretor/alertas" class="nav-link"><i class="bi bi-bell"></i> Alertas 
        @php $totalAlertas = $alertasMargemVermelho->count() + $alertasMargemAmarelo->count() + $escalasRejeitadas; @endphp
        @if($totalAlertas > 0)<span class="badge bg-danger">{{ $totalAlertas }}</span>@endif
    </a>
@endsection

@section('content')
<div class="row g-3 mb-4">
    @php $totalAlertas = $alertasMargemVermelho->count() + $alertasMargemAmarelo->count() + $escalasRejeitadas; @endphp
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="/diretor/alertas" class="text-decoration-none">
            <div class="card card-stat h-100 {{ $totalAlertas > 0 ? 'border-danger border-2' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-2 me-2 flex-shrink-0">
                                <i class="bi bi-bell text-danger"></i>
                            </div>
                            <div class="min-width-0">
                                <h6 class="text-muted mb-0 small">Alertas</h6>
                                <h4 class="mb-0">{{ $totalAlertas }}</h4>
                            </div>
                        </div>
                        @if($alertasMargemVermelho->count() > 0)
                        <span class="badge bg-danger rounded-pill">{{ $alertasMargemVermelho->count() }}</span>
                        @endif
                        @if($alertasMargemAmarelo->count() > 0)
                        <span class="badge bg-warning text-dark rounded-pill">{{ $alertasMargemAmarelo->count() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    </div>
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
        <div class="d-flex justify-content-between align-items-end" style="height: 200px; gap: 4px;">
            @php
                $alturaMaxima = 180;
                $maxValor = max($maxOrcamentoMes, $orcamentoMensalBase) * 1.2;
            @endphp
            @foreach($mesesInfo as $m => $info)
            @php
                $alturaOrcamento = $maxValor > 0 ? ($info['orcamento'] / $maxValor) * $alturaMaxima : 0;
                $alturaGasto = $maxValor > 0 ? ($info['gasto'] / $maxValor) * $alturaMaxima : 0;
                $alturaDiferenca = abs($alturaOrcamento - $alturaGasto);
                
                if ($info['gasto'] > $info['orcamento']) {
                    $corBarra = '#dc3545';
                    $corFundo = '#28a745';
                } else {
                    $corBarra = '#28a745';
                    $corFundo = '#e9ecef';
                }
            @endphp
            <div class="text-center flex-fill" style="min-width: 0;">
                <div class="position-relative mx-auto" style="width: 100%; max-width: 50px; height: {{ $alturaMaxima }}px;">
                    <div class="position-absolute bottom-0 start-0 end-0 rounded-top" 
                         style="height: {{ max($alturaOrcamento, $alturaGasto) }}px; background-color: {{ $corFundo }};"
                         title="Orçamento: R$ {{ number_format($info['orcamento'], 0, ',', '.') }}">
                    </div>
                    <div class="position-absolute bottom-0 start-0 end-0 rounded-top" 
                         style="height: {{ $alturaGasto }}px; background-color: {{ $info['gasto'] > $info['orcamento'] ? '#dc3545' : '#28a745' }};"
                         title="Gasto: R$ {{ number_format($info['gasto'], 0, ',', '.') }}">
                    </div>
                    @if($info['ultrapassouMargem'])
                    <div class="position-absolute top-0 start-50 translate-middle-x">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 0.7rem;"></i>
                    </div>
                    @endif
                    @if($info['mesAtual'])
                    <div class="position-absolute" style="bottom: -2px; left: 50%; transform: translateX(-50%);">
                        <div class="bg-primary rounded-circle" style="width: 6px; height: 6px;"></div>
                    </div>
                    @endif
                </div>
                <div class="mt-1 {{ $info['mesAtual'] ? 'fw-bold text-primary' : '' }}" style="font-size: 0.7rem;">{{ $info['nome'] }}</div>
                <div class="text-muted" style="font-size: 0.6rem;">{{ number_format($info['orcamento']/1000, 0) }}k</div>
            </div>
            @endforeach
        </div>
        <div class="mt-3 d-flex justify-content-center gap-4">
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #e9ecef;"></div>
                <small class="text-muted">Orçamento</small>
            </div>
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #28a745;"></div>
                <small class="text-muted">Gasto (dentro do limite)</small>
            </div>
            <div class="d-flex align-items-center">
                <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #dc3545;"></div>
                <small class="text-muted">Gasto (acima do limite)</small>
            </div>
        </div>
        <div class="mt-2 text-center">
            <small class="text-muted">Base mensal: R$ {{ number_format($orcamentoMensalBase, 0, ',', '.') }} | Total anual: R$ {{ number_format($orcamento, 0, ',', '.') }}</small>
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
