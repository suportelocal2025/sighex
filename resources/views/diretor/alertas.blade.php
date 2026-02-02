@extends('layouts.app')

@section('title', 'Diretor - Alertas')
@section('header', 'Central de Alertas')

@section('sidebar')
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/diretor/alertas" class="nav-link active"><i class="bi bi-bell"></i> Alertas</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-bell me-2"></i>Alertas da Minha Unidade</h2>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Ano</label>
                <select name="ano" class="form-select">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $ano ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mês</label>
                <select name="mes" class="form-select">
                    <option value="">Todos os meses</option>
                    @php $nomesMeses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']; @endphp
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>{{ $nomesMeses[$m] }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="vermelho" {{ $tipo == 'vermelho' ? 'selected' : '' }}>Vermelho</option>
                    <option value="amarelo" {{ $tipo == 'amarelo' ? 'selected' : '' }}>Amarelo</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3>{{ $alertasPrazo->count() }}</h3>
                <p class="mb-0">Alertas de Prazo</p>
                <small>Prazos e Correções</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3>{{ $alertasVermelho->count() }}</h3>
                <p class="mb-0">Alertas Vermelho</p>
                <small>Margem Excedida</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body text-center">
                <h3>{{ $alertasAmarelo->count() }}</h3>
                <p class="mb-0">Alertas Amarelo</p>
                <small>Acima do Previsto</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>{{ $escalasRejeitadas }}</h3>
                <p class="mb-0">Escalas Rejeitadas</p>
                <small>Necessitam Correção</small>
            </div>
        </div>
    </div>
</div>

@if($alertasPrazo->count() > 0)
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Alertas de Prazo</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            @foreach($alertasPrazo as $alerta)
            <div class="list-group-item list-group-item-action {{ str_contains($alerta->tipo, '5dias') || str_contains($alerta->tipo, '6horas') ? 'list-group-item-danger' : '' }}">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        @if(str_contains($alerta->tipo, 'correcao'))
                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                        @else
                            <i class="bi bi-clock text-primary me-1"></i>
                        @endif
                        {{ $alerta->titulo }}
                    </h6>
                    <small class="text-muted">{{ $alerta->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-1">{{ $alerta->mensagem }}</p>
                @if($alerta->prazo_limite)
                <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>Prazo: {{ $alerta->prazo_limite->format('d/m/Y H:i') }}
                    @if($alerta->prazo_limite->isPast())
                        <span class="badge bg-danger ms-2">Expirado</span>
                    @elseif($alerta->prazo_limite->diffInHours(now()) < 6)
                        <span class="badge bg-warning text-dark ms-2">Urgente</span>
                    @endif
                </small>
                @endif
                @if($alerta->escala_id)
                <div class="mt-2">
                    <a href="/diretor/escala-mensal?mes={{ $alerta->mes }}&ano={{ $alerta->ano }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Ver Escala
                    </a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if($alertasVermelho->count() > 0)
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i>Alertas Vermelho - Margem Excedida</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Limite c/ Margem</th>
                        <th>Valor Executado</th>
                        <th>Excedente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @php $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']; @endphp
                    @foreach($alertasVermelho as $alerta)
                    <tr>
                        <td><strong>{{ $meses[$alerta->mes] }}/{{ $alerta->ano }}</strong></td>
                        <td>R$ {{ number_format($alerta->limite_margem ?? 0, 2, ',', '.') }}</td>
                        <td class="text-danger fw-bold">R$ {{ number_format($alerta->valor_executado, 2, ',', '.') }}</td>
                        <td><span class="badge bg-danger">+R$ {{ number_format($alerta->valor_executado - ($alerta->limite_margem ?? 0), 2, ',', '.') }}</span></td>
                        <td>
                            <a href="/diretor/escala-mensal?mes={{ $alerta->mes }}&ano={{ $alerta->ano }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Escala
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($alertasAmarelo->count() > 0)
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Alertas Amarelo - Acima do Previsto</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Previsto</th>
                        <th>Valor Executado</th>
                        <th>Acima</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @php $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']; @endphp
                    @foreach($alertasAmarelo as $alerta)
                    <tr>
                        <td><strong>{{ $meses[$alerta->mes] }}/{{ $alerta->ano }}</strong></td>
                        <td>R$ {{ number_format($alerta->orcamento_mes ?? 0, 2, ',', '.') }}</td>
                        <td class="text-warning fw-bold">R$ {{ number_format($alerta->valor_executado, 2, ',', '.') }}</td>
                        <td><span class="badge bg-warning text-dark">+R$ {{ number_format($alerta->valor_executado - ($alerta->orcamento_mes ?? 0), 2, ',', '.') }}</span></td>
                        <td>
                            <a href="/diretor/escala-mensal?mes={{ $alerta->mes }}&ano={{ $alerta->ano }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Escala
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($escalasRejeitadas > 0)
<div class="card mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Escalas Rejeitadas</h5>
    </div>
    <div class="card-body">
        <p>Você tem {{ $escalasRejeitadas }} escala(s) rejeitada(s) que necessitam de correção.</p>
        <a href="/diretor/escala-mensal" class="btn btn-info text-white">
            <i class="bi bi-calendar3 me-1"></i>Ver Escalas
        </a>
    </div>
</div>
@endif

@if($alertasVermelho->count() == 0 && $alertasAmarelo->count() == 0 && $escalasRejeitadas == 0 && $alertasPrazo->count() == 0)
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>Nenhum alerta encontrado para os filtros selecionados.
</div>
@endif
@endsection
