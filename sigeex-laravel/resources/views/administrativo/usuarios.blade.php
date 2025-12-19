@extends('layouts.app')

@section('title', 'Administrativo - Usuários')
@section('header', 'Gestão de Usuários')

@section('sidebar')
    <a href="/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/admin/unidades" class="nav-link"><i class="bi bi-building"></i> Unidades</a>
    <a href="/admin/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/admin/usuarios" class="nav-link"><i class="bi bi-person-gear"></i> Usuários</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Usuários do Sistema</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="novoUsuario()">
            <i class="bi bi-plus-circle"></i> Novo Usuário
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Unidade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nome }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>
                            <span class="badge bg-{{ $usuario->papel === 'superintendente' ? 'primary' : ($usuario->papel === 'diretor' ? 'success' : ($usuario->papel === 'rh' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($usuario->papel) }}
                            </span>
                        </td>
                        <td>{{ $usuario->unidade->nome ?? '-' }}</td>
                        <td>
                            @if($usuario->ativo)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario({{ json_encode($usuario->only(['id', 'nome', 'email', 'papel', 'unidade_id', 'ativo'])) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="resetarSenha({{ $usuario->id }}, '{{ $usuario->nome }}')">
                                <i class="bi bi-key"></i>
                            </button>
                            @if($usuario->id !== Auth::id())
                            <form action="/admin/usuario/{{ $usuario->id }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir este usuário?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhum usuário encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/usuario">
                @csrf
                <input type="hidden" name="id" id="usuario_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioTitulo">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" id="usuario_nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="usuario_email" class="form-control" required>
                    </div>
                    <div class="mb-3" id="divSenha">
                        <label class="form-label">Senha *</label>
                        <input type="password" name="senha" id="usuario_senha" class="form-control" minlength="6">
                        <small class="text-muted" id="senhaHelp">Mínimo 6 caracteres</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil *</label>
                        <select name="papel" id="usuario_papel" class="form-select" required onchange="toggleUnidade()">
                            <option value="">Selecione...</option>
                            <option value="superintendente">Superintendente</option>
                            <option value="diretor">Diretor/Gestor</option>
                            <option value="rh">RH</option>
                            <option value="administrativo">Administrativo</option>
                        </select>
                    </div>
                    <div class="mb-3" id="divUnidade" style="display: none;">
                        <label class="form-label">Unidade *</label>
                        <select name="unidade_id" id="usuario_unidade" class="form-select">
                            <option value="">Selecione...</option>
                            @foreach($unidades as $unidade)
                                <option value="{{ $unidade->id }}">{{ $unidade->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="ativo" id="usuario_ativo" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="usuario_ativo">Usuário Ativo</label>
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

<div class="modal fade" id="modalResetSenha" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/usuario/resetar-senha">
                @csrf
                <input type="hidden" name="id" id="reset_usuario_id">
                <div class="modal-header">
                    <h5 class="modal-title">Resetar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Redefinir senha do usuário: <strong id="reset_usuario_nome"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Nova Senha *</label>
                        <input type="password" name="nova_senha" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Resetar Senha</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function novoUsuario() {
    document.getElementById('modalUsuarioTitulo').textContent = 'Novo Usuário';
    document.getElementById('usuario_id').value = '';
    document.getElementById('usuario_nome').value = '';
    document.getElementById('usuario_email').value = '';
    document.getElementById('usuario_senha').value = '';
    document.getElementById('usuario_senha').required = true;
    document.getElementById('usuario_papel').value = '';
    document.getElementById('usuario_unidade').value = '';
    document.getElementById('usuario_ativo').checked = true;
    document.getElementById('divSenha').style.display = 'block';
    document.getElementById('senhaHelp').textContent = 'Mínimo 6 caracteres';
    toggleUnidade();
}

function editarUsuario(usuario) {
    document.getElementById('modalUsuarioTitulo').textContent = 'Editar Usuário';
    document.getElementById('usuario_id').value = usuario.id;
    document.getElementById('usuario_nome').value = usuario.nome;
    document.getElementById('usuario_email').value = usuario.email;
    document.getElementById('usuario_senha').value = '';
    document.getElementById('usuario_senha').required = false;
    document.getElementById('usuario_papel').value = usuario.papel;
    document.getElementById('usuario_unidade').value = usuario.unidade_id || '';
    document.getElementById('usuario_ativo').checked = usuario.ativo;
    document.getElementById('divSenha').style.display = 'block';
    document.getElementById('senhaHelp').textContent = 'Deixe em branco para manter a senha atual';
    toggleUnidade();
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function resetarSenha(id, nome) {
    document.getElementById('reset_usuario_id').value = id;
    document.getElementById('reset_usuario_nome').textContent = nome;
    new bootstrap.Modal(document.getElementById('modalResetSenha')).show();
}

function toggleUnidade() {
    const papel = document.getElementById('usuario_papel').value;
    const divUnidade = document.getElementById('divUnidade');
    const unidadeSelect = document.getElementById('usuario_unidade');
    
    if (papel === 'diretor') {
        divUnidade.style.display = 'block';
        unidadeSelect.required = true;
    } else {
        divUnidade.style.display = 'none';
        unidadeSelect.required = false;
        unidadeSelect.value = '';
    }
}
</script>
@endpush
