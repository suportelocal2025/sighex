@extends('layouts.app')

@section('title', 'Superintendente - Escalas')
@section('header', 'Gestão de Escalas')

@section('sidebar')
    <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
    <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
    <a href="/superintendente/escalas" class="nav-link active"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/superintendente/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Escalas - {{ $ano }}</h5>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="/superintendente/escalas?status=todos" class="btn btn-sm {{ $status === 'todos' ? 'btn-primary' : 'btn-outline-primary' }}">Todas</a>
                    <a href="/superintendente/escalas?status=pendente" class="btn btn-sm {{ $status === 'pendente' ? 'btn-warning' : 'btn-outline-warning' }}">Pendentes</a>
                    <a href="/superintendente/escalas?status=aprovada" class="btn btn-sm {{ $status === 'aprovada' ? 'btn-success' : 'btn-outline-success' }}">Aprovadas</a>
                    <a href="/superintendente/escalas?status=executada" class="btn btn-sm {{ $status === 'executada' ? 'btn-info' : 'btn-outline-info' }}">Executadas</a>
                </div>
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
                    <tr class="{{ $escala->excede_margem && $escala->status === 'pendente' ? 'table-danger' : ($escala->usa_margem ? 'table-warning' : '') }}">
                        <td>
                            {{ $escala->unidade->nome ?? 'N/A' }}
                            @if($escala->excede_margem && $escala->status === 'pendente')
                                <span class="badge bg-danger ms-1"><i class="bi bi-exclamation-triangle"></i> Requer sua aprovação</span>
                            @elseif($escala->usa_margem)
                                <span class="badge bg-warning text-dark ms-1"><i class="bi bi-info-circle"></i> Usa margem</span>
                            @endif
                        </td>
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
                        <td>{{ $escala->data_envio ? \Carbon\Carbon::parse($escala->data_envio)->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <a href="/superintendente/escala/{{ $escala->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detalhar
                            </a>
                            @if($escala->excede_margem && $escala->status === 'pendente')
                                <form method="POST" action="/superintendente/aprovar-escala" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Aprovar escala que excede a margem orçamentária?')">
                                        <i class="bi bi-check-lg"></i> Aprovar
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $escala->id }}">
                                    <i class="bi bi-x-lg"></i> Rejeitar
                                </button>
                            @endif
                        </td>
                    </tr>
                    @if($escala->excede_margem && $escala->status === 'pendente')
                    <tr class="table-light">
                        <td colspan="5" class="small">
                            <i class="bi bi-info-circle text-danger"></i>
                            <strong>Orçamento do mês:</strong> R$ {{ number_format($escala->orcamento_mes, 2, ',', '.') }} |
                            <strong>Limite com margem:</strong> R$ {{ number_format($escala->limite_margem, 2, ',', '.') }} |
                            <strong>Valor previsto:</strong> <span class="text-danger fw-bold">R$ {{ number_format($escala->valor_previsto, 2, ',', '.') }}</span>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nenhuma escala encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($escalas->where('excede_margem', true)->where('status', 'pendente') as $escala)
<div class="modal fade" id="rejectModal{{ $escala->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/superintendente/rejeitar-escala">
                @csrf
                <input type="hidden" name="escala_id" value="{{ $escala->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Rejeitar Escala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Unidade:</strong> {{ $escala->unidade->nome }}</p>
                    <p><strong>Período:</strong> {{ str_pad($escala->mes, 2, '0', STR_PAD_LEFT) }}/{{ $escala->ano }}</p>
                    <div class="mb-3">
                        <label class="form-label">Motivo da rejeição *</label>
                        <textarea name="motivo_rejeicao" class="form-control" rows="3" required minlength="10" placeholder="Informe o motivo da rejeição (mínimo 10 caracteres)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rejeitar Escala</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
