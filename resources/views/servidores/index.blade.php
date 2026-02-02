@extends('layouts.app')

@section('title', 'Servidores')
@section('header', 'Busca de Servidores')

@section('sidebar')
    @php $user = Auth::user(); @endphp
    @if($user->papel === 'superintendente')
        <a href="/superintendente" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="/superintendente/orcamento" class="nav-link"><i class="bi bi-wallet2"></i> Orçamento</a>
        <a href="/superintendente/distribuicao" class="nav-link"><i class="bi bi-diagram-3"></i> Distribuição</a>
        <a href="/superintendente/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
        <a href="/superintendente/alertas" class="nav-link"><i class="bi bi-bell"></i> Alertas</a>
    @elseif($user->papel === 'diretor')
        <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    @elseif($user->papel === 'rh')
        <a href="/rh" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="/rh/escalas" class="nav-link"><i class="bi bi-calendar-check"></i> Escalas</a>
    @elseif($user->papel === 'administrativo')
        <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
        <a href="/admin/usuarios" class="nav-link"><i class="bi bi-people"></i> Usuários</a>
    @endif
    <a href="/servidores" class="nav-link active"><i class="bi bi-person-badge"></i> Servidores</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Buscar Servidor</h5>
        @if($podeImportar ?? false)
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportar">
            <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
        </button>
        @endif
    </div>
    <div class="card-body">
        <form method="GET" action="/servidores" class="mb-4">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="busca" class="form-control form-control-lg" 
                               placeholder="Digite a matrícula ou nome completo do servidor (mínimo 3 caracteres)..."
                               value="{{ $busca ?? '' }}" autofocus>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
        
        @if($busca && strlen($busca) >= 3)
            @if($servidores->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>Unidade</th>
                            <th>Cargo</th>
                            <th>Status</th>
                            <th>Escala Extra</th>
                            @if($podeEditar)
                            <th>Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servidores as $servidor)
                        <tr>
                            <td><strong>{{ $servidor->matricula }}</strong></td>
                            <td>{{ $servidor->nome }}</td>
                            <td>{{ $servidor->unidade->nome ?? 'N/A' }}</td>
                            <td>{{ $servidor->cargo ?? '-' }}</td>
                            <td>
                                @if($servidor->ativo)
                                    @if($servidor->inativo_indefinido)
                                        <span class="badge bg-secondary">Inativo Indefinido</span>
                                    @elseif($servidor->inativo_inicio && $servidor->inativo_fim)
                                        @php
                                            $hoje = now();
                                            $emPeriodoInativo = $hoje >= $servidor->inativo_inicio && $hoje <= $servidor->inativo_fim;
                                        @endphp
                                        @if($emPeriodoInativo)
                                            <span class="badge bg-warning text-dark" title="{{ $servidor->motivo_inativo }}">
                                                Inativo até {{ $servidor->inativo_fim->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">Ativo</span>
                                        @endif
                                    @else
                                        <span class="badge bg-success">Ativo</span>
                                    @endif
                                @else
                                    <span class="badge bg-danger">Inativo</span>
                                @endif
                            </td>
                            <td>
                                @if($servidor->apto_escala_extra)
                                    <span class="badge bg-success">Apto</span>
                                @else
                                    <span class="badge bg-secondary">Inapto</span>
                                @endif
                            </td>
                            @if($podeEditar)
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalStatus"
                                        data-id="{{ $servidor->id }}"
                                        data-nome="{{ $servidor->nome }}"
                                        data-matricula="{{ $servidor->matricula }}"
                                        data-ativo="{{ $servidor->ativo ? '1' : '0' }}"
                                        data-apto="{{ $servidor->apto_escala_extra ? '1' : '0' }}"
                                        data-motivo="{{ $servidor->motivo_inativo }}"
                                        data-inicio="{{ $servidor->inativo_inicio?->format('Y-m-d') }}"
                                        data-fim="{{ $servidor->inativo_fim?->format('Y-m-d') }}"
                                        data-indefinido="{{ $servidor->inativo_indefinido ? '1' : '0' }}">
                                    <i class="bi bi-pencil"></i> Editar Status
                                </button>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Nenhum servidor encontrado com o termo "{{ $busca }}".
            </div>
            @endif
        @elseif($busca && strlen($busca) < 3)
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Digite pelo menos 3 caracteres para buscar.
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="bi bi-person-badge" style="font-size: 4rem;"></i>
                <p class="mt-3">Use o campo acima para buscar servidores por matrícula ou nome.</p>
            </div>
        @endif
    </div>
</div>

@if($podeEditar)
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/servidores/alterar-status">
                @csrf
                <input type="hidden" name="servidor_id" id="statusServidorId">
                <div class="modal-header">
                    <h5 class="modal-title">Alterar Status do Servidor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong id="statusServidorNome"></strong> - <span id="statusServidorMatricula"></span></p>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Status Geral</label>
                            <select name="ativo" id="statusAtivo" class="form-select" onchange="toggleInatividade()">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Escala Extra</label>
                            <select name="apto_escala_extra" id="statusApto" class="form-select">
                                <option value="1">Apto</option>
                                <option value="0">Inapto</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="camposInatividade" style="display: none;">
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Motivo da Inatividade</label>
                            <select name="motivo_inativo" id="statusMotivo" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="Férias">Férias</option>
                                <option value="Licença Médica">Licença Médica</option>
                                <option value="Licença Prêmio">Licença Prêmio</option>
                                <option value="Afastamento">Afastamento</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="inativo_indefinido" id="statusIndefinido" value="1" onchange="togglePeriodo()">
                            <label class="form-check-label" for="statusIndefinido">Inativo por tempo indeterminado</label>
                        </div>
                        
                        <div id="camposPeriodo">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">Data Início</label>
                                    <input type="date" name="inativo_inicio" id="statusInicio" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Data Fim</label>
                                    <input type="date" name="inativo_fim" id="statusFim" class="form-control">
                                </div>
                            </div>
                            <small class="text-muted">Após a data fim, o servidor voltará automaticamente a ficar ativo.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($podeImportar ?? false)
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/servidores/importar-csv" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Importar Servidores via CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">O arquivo CSV deve conter as seguintes colunas na ordem:</p>
                    <ol class="small">
                        <li><strong>Matrícula</strong></li>
                        <li><strong>Nome</strong></li>
                        <li><strong>Unidade</strong> (nome da unidade)</li>
                        <li><strong>Cargo</strong></li>
                        <li><strong>Escala Extra</strong> (Sim/Não ou 1/0)</li>
                        <li><strong>Status</strong> (Ativo/Inativo ou 1/0)</li>
                    </ol>
                    <div class="mb-3">
                        <label class="form-label">Arquivo CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        O sistema aceita separadores <code>;</code> ou <code>,</code>. Servidores existentes serão atualizados pela matrícula.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleInatividade() {
    const ativo = document.getElementById('statusAtivo').value;
    const campos = document.getElementById('camposInatividade');
    campos.style.display = ativo === '0' ? 'block' : 'none';
}

function togglePeriodo() {
    const indefinido = document.getElementById('statusIndefinido').checked;
    const campos = document.getElementById('camposPeriodo');
    campos.style.display = indefinido ? 'none' : 'block';
}

document.getElementById('modalStatus')?.addEventListener('show.bs.modal', function(event) {
    const btn = event.relatedTarget;
    document.getElementById('statusServidorId').value = btn.dataset.id;
    document.getElementById('statusServidorNome').textContent = btn.dataset.nome;
    document.getElementById('statusServidorMatricula').textContent = btn.dataset.matricula;
    document.getElementById('statusAtivo').value = btn.dataset.ativo;
    document.getElementById('statusApto').value = btn.dataset.apto;
    document.getElementById('statusMotivo').value = btn.dataset.motivo || '';
    document.getElementById('statusInicio').value = btn.dataset.inicio || '';
    document.getElementById('statusFim').value = btn.dataset.fim || '';
    document.getElementById('statusIndefinido').checked = btn.dataset.indefinido === '1';
    
    toggleInatividade();
    togglePeriodo();
});
</script>
@endpush
