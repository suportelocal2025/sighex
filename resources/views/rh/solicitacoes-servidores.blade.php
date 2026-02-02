@extends('layouts.app')

@section('title', 'RH - Solicitações de Servidores')
@section('header', 'Solicitações de Novos Servidores')

@section('sidebar')
    <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    <a href="/rh/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/rh/relatorios" class="nav-link"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="mb-3">
    <a href="/rh/servidores" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar para Servidores
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-person-plus me-2 text-primary"></i>Solicitações de Inclusão de Servidores</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <th>Matrícula</th>
                    <th>Nome</th>
                    <th>Unidade</th>
                    <th>Cargo</th>
                    <th>Solicitante</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitacoes as $s)
                @php
                    $statusClass = match($s->status) {
                        'pendente' => 'bg-warning text-dark',
                        'aprovada' => 'bg-success',
                        'rejeitada' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                @endphp
                <tr>
                    <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $s->matricula }}</strong></td>
                    <td>{{ $s->nome }}</td>
                    <td>{{ $s->unidade->nome ?? '-' }}</td>
                    <td>{{ $s->cargo ?? '-' }}</td>
                    <td>{{ $s->solicitante->nome ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td class="text-center">
                        @if($s->status === 'pendente')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#aprovarModal{{ $s->id }}">
                            <i class="bi bi-check-lg"></i> Habilitar
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejeitarModal{{ $s->id }}">
                            <i class="bi bi-x-lg"></i> Rejeitar
                        </button>
                        @elseif($s->status === 'rejeitada')
                        <span class="text-muted small" title="{{ $s->motivo_rejeicao }}">
                            <i class="bi bi-info-circle"></i> {{ Str::limit($s->motivo_rejeicao, 30) }}
                        </span>
                        @else
                        <span class="text-success"><i class="bi bi-check-circle"></i> Aprovado</span>
                        @endif
                    </td>
                </tr>

                <!-- Modal Aprovar -->
                <div class="modal fade" id="aprovarModal{{ $s->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Habilitar Servidor</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="/rh/aprovar-solicitacao-servidor" method="POST">
                                @csrf
                                <input type="hidden" name="solicitacao_id" value="{{ $s->id }}">
                                <div class="modal-body">
                                    <p>Deseja habilitar o servidor abaixo?</p>
                                    <ul class="list-unstyled">
                                        <li><strong>Matrícula:</strong> {{ $s->matricula }}</li>
                                        <li><strong>Nome:</strong> {{ $s->nome }}</li>
                                        <li><strong>Unidade:</strong> {{ $s->unidade->nome ?? '-' }}</li>
                                        <li><strong>Cargo:</strong> {{ $s->cargo ?? '-' }}</li>
                                    </ul>
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle"></i> O servidor será cadastrado como ATIVO e APTO para escala extra.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg"></i> Habilitar Servidor
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Rejeitar -->
                <div class="modal fade" id="rejeitarModal{{ $s->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Rejeitar Solicitação</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="/rh/rejeitar-solicitacao-servidor" method="POST">
                                @csrf
                                <input type="hidden" name="solicitacao_id" value="{{ $s->id }}">
                                <div class="modal-body">
                                    <p>Rejeitar a solicitação de inclusão de <strong>{{ $s->nome }}</strong>?</p>
                                    <div class="mb-3">
                                        <label class="form-label">Motivo da Rejeição <span class="text-danger">*</span></label>
                                        <textarea name="motivo_rejeicao" class="form-control" rows="3" required minlength="5" placeholder="Informe o motivo da rejeição..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-lg"></i> Rejeitar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        Nenhuma solicitação encontrada
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
