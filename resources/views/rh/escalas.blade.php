@extends('layouts.app')

@section('title', 'RH - Escalas')
@section('header', 'Gestão de Escalas')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link active"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/rh/escalas" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">Ano</label>
                <select name="ano" class="form-select form-select-sm">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Mês</label>
                <select name="mes" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @php
                        $nomesMeses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                    @endphp
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>{{ $nomesMeses[$m] }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Unidade</label>
                <select name="unidade_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ $unidadeId == $u->id ? 'selected' : '' }}>{{ $u->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="todos" {{ $status === 'todos' ? 'selected' : '' }}>Todas</option>
                    <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendentes</option>
                    <option value="aprovada" {{ $status === 'aprovada' ? 'selected' : '' }}>Aprovadas</option>
                    <option value="executada" {{ $status === 'executada' ? 'selected' : '' }}>Executadas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="/rh/escalas/exportar-excel?ano={{ $ano }}&mes={{ $mes }}&unidade_id={{ $unidadeId }}&status={{ $status }}" class="btn btn-success btn-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check me-2"></i>Escalas
                    @if($mes)
                        - {{ $nomesMeses[$mes] }}/{{ $ano }}
                    @else
                        - {{ $ano }}
                    @endif
                </h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary">{{ $escalas->count() }} escala(s)</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Mês/Ano</th>
                        <th>Status</th>
                        <th>Data Envio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($escalas as $escala)
                    <tr>
                        <td>{{ $escala->unidade->nome ?? 'N/A' }}</td>
                        <td>{{ str_pad($escala->mes, 2, '0', STR_PAD_LEFT) }}/{{ $escala->ano }}</td>
                        <td>
                            @switch($escala->status)
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
                        <td>{{ $escala->data_envio ? $escala->data_envio->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <a href="/rh/escala/{{ $escala->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detalhar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma escala encontrada com os filtros selecionados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
