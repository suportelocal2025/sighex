@extends('layouts.app')

@section('title', 'Superintendente - Alertas')
@section('header', 'Central de Alertas')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/alertas" class="nav-link active"><i class="bi bi-bell"></i> Alertas</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-bell me-2"></i>Central de Alertas</h2>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Ano</label>
                <select name="ano" class="form-select">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $ano ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
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
                <label class="form-label">Unidade</label>
                <select name="unidade_id" class="form-select">
                    <option value="">Todas as unidades</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ $unidadeId == $unidade->id ? 'selected' : '' }}>{{ $unidade->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
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
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3>{{ count($alertasVermelho) }}</h3>
                <p class="mb-0">Alertas Vermelho</p>
                <small>Margem Excedida</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning">
            <div class="card-body text-center">
                <h3>{{ count($alertasAmarelo) }}</h3>
                <p class="mb-0">Alertas Amarelo</p>
                <small>Acima do Previsto</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h3>{{ count($alertasVermelho) + count($alertasAmarelo) }}</h3>
                <p class="mb-0">Total de Alertas</p>
                <small>{{ $ano }}</small>
            </div>
        </div>
    </div>
</div>

@if(count($alertasVermelho) > 0)
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i>Alertas Vermelho - Margem Excedida</h5>
        <form method="POST" action="/superintendente/enviar-alerta-email" class="d-inline">
            @csrf
            <input type="hidden" name="ano" value="{{ $ano }}">
            <input type="hidden" name="tipo" value="vermelho">
            <button type="submit" class="btn btn-sm btn-light">
                <i class="bi bi-envelope me-1"></i>Enviar por Email
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Mês</th>
                        <th>Limite c/ Margem</th>
                        <th>Gasto</th>
                        <th>Excedente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertasVermelho as $alerta)
                    <tr>
                        <td><strong>{{ $alerta['unidade_nome'] }}</strong></td>
                        <td>{{ $alerta['mes_nome'] }}/{{ $ano }}</td>
                        <td>R$ {{ number_format($alerta['limite'], 2, ',', '.') }}</td>
                        <td class="text-danger fw-bold">R$ {{ number_format($alerta['gasto'], 2, ',', '.') }}</td>
                        <td><span class="badge bg-danger">+R$ {{ number_format($alerta['excedente'], 2, ',', '.') }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if(count($alertasAmarelo) > 0)
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Alertas Amarelo - Acima do Previsto</h5>
        <form method="POST" action="/superintendente/enviar-alerta-email" class="d-inline">
            @csrf
            <input type="hidden" name="ano" value="{{ $ano }}">
            <input type="hidden" name="tipo" value="amarelo">
            <button type="submit" class="btn btn-sm btn-dark">
                <i class="bi bi-envelope me-1"></i>Enviar por Email
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Mês</th>
                        <th>Previsto</th>
                        <th>Gasto</th>
                        <th>Acima</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertasAmarelo as $alerta)
                    <tr>
                        <td><strong>{{ $alerta['unidade_nome'] }}</strong></td>
                        <td>{{ $alerta['mes_nome'] }}/{{ $ano }}</td>
                        <td>R$ {{ number_format($alerta['orcamento'], 2, ',', '.') }}</td>
                        <td class="text-warning fw-bold">R$ {{ number_format($alerta['gasto'], 2, ',', '.') }}</td>
                        <td><span class="badge bg-warning text-dark">+R$ {{ number_format($alerta['excedente'], 2, ',', '.') }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if(count($alertasVermelho) == 0 && count($alertasAmarelo) == 0)
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>Nenhum alerta encontrado para os filtros selecionados.
</div>
@endif
@endsection
