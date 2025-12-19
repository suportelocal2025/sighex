@extends('layouts.app')

@section('title', 'Administrativo - Servidores')
@section('header', 'Gestão de Servidores')

@section('sidebar')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-gear"></i> Usuários</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Servidores / Policiais Penais</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServidor" onclick="novoServidor()">
            <i class="bi bi-plus-circle"></i> Novo Servidor
        </button>
    </div>
    <div class="card-body">
        <form class="row g-3 mb-4" method="GET" action="/admin/servidores">
            <div class="col-md-4">
                <select name="unidade_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas as Unidades</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>Unidade</th>
                        <th>Cargo</th>
                        <th>Escala Extra</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servidores as $servidor)
                    <tr>
                        <td><code>{{ $servidor->matricula }}</code></td>
                        <td>{{ $servidor->nome }}</td>
                        <td>{{ $servidor->unidade->nome ?? 'N/A' }}</td>
                        <td>{{ $servidor->cargo ?? '-' }}</td>
                        <td>
                            @if($servidor->apto_escala_extra)
                                <span class="badge bg-success">Apto</span>
                            @else
                                <span class="badge bg-secondary">Não Apto</span>
                            @endif
                        </td>
                        <td>
                            @if($servidor->ativo)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarServidor({{ json_encode($servidor->only(['id', 'nome', 'matricula', 'unidade_id', 'cargo', 'email', 'telefone', 'apto_escala_extra', 'ativo'])) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/admin/servidor/{{ $servidor->id }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este servidor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhum servidor encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalServidor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/admin/servidor">
                @csrf
                <input type="hidden" name="id" id="servidor_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalServidorTitulo">Novo Servidor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome" id="servidor_nome" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Matrícula *</label>
                            <input type="text" name="matricula" id="servidor_matricula" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unidade *</label>
                            <select name="unidade_id" id="servidor_unidade" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($unidades as $unidade)
                                    <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" id="servidor_cargo" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="servidor_email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="servidor_telefone" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="apto_escala_extra" id="servidor_apto" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="servidor_apto">Apto para Escala Extra</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" name="ativo" id="servidor_ativo" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="servidor_ativo">Servidor Ativo</label>
                            </div>
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
@endsection

@push('scripts')
<script>
function novoServidor() {
    document.getElementById('modalServidorTitulo').textContent = 'Novo Servidor';
    document.getElementById('servidor_id').value = '';
    document.getElementById('servidor_nome').value = '';
    document.getElementById('servidor_matricula').value = '';
    document.getElementById('servidor_unidade').value = '';
    document.getElementById('servidor_cargo').value = '';
    document.getElementById('servidor_email').value = '';
    document.getElementById('servidor_telefone').value = '';
    document.getElementById('servidor_apto').checked = true;
    document.getElementById('servidor_ativo').checked = true;
}

function editarServidor(servidor) {
    document.getElementById('modalServidorTitulo').textContent = 'Editar Servidor';
    document.getElementById('servidor_id').value = servidor.id;
    document.getElementById('servidor_nome').value = servidor.nome;
    document.getElementById('servidor_matricula').value = servidor.matricula;
    document.getElementById('servidor_unidade').value = servidor.unidade_id;
    document.getElementById('servidor_cargo').value = servidor.cargo || '';
    document.getElementById('servidor_email').value = servidor.email || '';
    document.getElementById('servidor_telefone').value = servidor.telefone || '';
    document.getElementById('servidor_apto').checked = servidor.apto_escala_extra;
    document.getElementById('servidor_ativo').checked = servidor.ativo;
    new bootstrap.Modal(document.getElementById('modalServidor')).show();
}
</script>
@endpush
