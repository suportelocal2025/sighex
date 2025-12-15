<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-people me-2"></i>Gestão de Usuários</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="novoUsuario()">
        <i class="bi bi-plus-lg me-2"></i>Novo Usuário
    </button>
</div>

<?php if (isset($mensagem)): ?>
<div class="alert alert-<?= $tipoMensagem ?? 'info' ?> alert-dismissible fade show">
    <?= htmlspecialchars($mensagem) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Unidade Vinculada</th>
                        <th>Status</th>
                        <th width="150">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Nenhum usuário cadastrado
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($usuario['nome']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td>
                            <?php
                            $badgeClass = match($usuario['papel']) {
                                'superintendente' => 'bg-primary',
                                'diretor' => 'bg-success',
                                'rh' => 'bg-info',
                                'administrativo' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            $papelLabel = match($usuario['papel']) {
                                'superintendente' => 'Superintendente',
                                'diretor' => 'Diretor/Gestor',
                                'rh' => 'RH',
                                'administrativo' => 'Administrativo',
                                default => $usuario['papel']
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $papelLabel ?></span>
                        </td>
                        <td>
                            <?php if ($usuario['unidade_nome']): ?>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($usuario['unidade_nome']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($usuario['ativo']): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" 
                                    onclick="editarUsuario(<?= htmlspecialchars(json_encode($usuario)) ?>)"
                                    title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary me-1" 
                                    onclick="resetarSenha(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')"
                                    title="Resetar Senha">
                                <i class="bi bi-key"></i>
                            </button>
                            <?php if ($usuario['id'] != $_SESSION['usuario']['id']): ?>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="excluirUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')"
                                    title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/usuarios/salvar" method="POST">
                <input type="hidden" name="id" id="usuarioId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioTitulo">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="usuarioNome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="usuarioEmail" class="form-control" required>
                    </div>
                    <div class="mb-3" id="senhaGroup">
                        <label class="form-label">Senha <span class="text-danger" id="senhaObrigatoria">*</span></label>
                        <input type="password" name="senha" id="usuarioSenha" class="form-control" minlength="6">
                        <small class="text-muted" id="senhaHelp">Mínimo 6 caracteres</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil <span class="text-danger">*</span></label>
                        <select name="papel" id="usuarioPapel" class="form-select" required onchange="toggleUnidadeSelect()">
                            <option value="">Selecione o perfil...</option>
                            <option value="superintendente">Superintendente</option>
                            <option value="diretor">Diretor/Gestor de Unidade</option>
                            <option value="rh">RH</option>
                            <option value="administrativo">Administrativo</option>
                        </select>
                    </div>
                    <div class="mb-3" id="unidadeGroup" style="display: none;">
                        <label class="form-label">Unidade Vinculada <span class="text-danger">*</span></label>
                        <select name="unidade_id" id="usuarioUnidade" class="form-select">
                            <option value="">Selecione a unidade...</option>
                            <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= $unidade['id'] ?>"><?= htmlspecialchars($unidade['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Obrigatório para Diretores/Gestores</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="ativo" id="usuarioAtivo" class="form-check-input" value="1" checked>
                            <label class="form-check-label" for="usuarioAtivo">Usuário ativo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalResetSenha" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="/admin/usuarios/resetar-senha" method="POST">
                <input type="hidden" name="id" id="resetUserId">
                <div class="modal-header">
                    <h5 class="modal-title">Resetar Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Resetar senha de: <strong id="resetUserName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="nova_senha" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">Resetar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function novoUsuario() {
    document.getElementById('modalUsuarioTitulo').textContent = 'Novo Usuário';
    document.getElementById('usuarioId').value = '';
    document.getElementById('usuarioNome').value = '';
    document.getElementById('usuarioEmail').value = '';
    document.getElementById('usuarioSenha').value = '';
    document.getElementById('usuarioSenha').required = true;
    document.getElementById('senhaObrigatoria').style.display = 'inline';
    document.getElementById('senhaHelp').textContent = 'Mínimo 6 caracteres';
    document.getElementById('usuarioPapel').value = '';
    document.getElementById('usuarioUnidade').value = '';
    document.getElementById('usuarioAtivo').checked = true;
    document.getElementById('unidadeGroup').style.display = 'none';
}

function editarUsuario(usuario) {
    document.getElementById('modalUsuarioTitulo').textContent = 'Editar Usuário';
    document.getElementById('usuarioId').value = usuario.id;
    document.getElementById('usuarioNome').value = usuario.nome;
    document.getElementById('usuarioEmail').value = usuario.email;
    document.getElementById('usuarioSenha').value = '';
    document.getElementById('usuarioSenha').required = false;
    document.getElementById('senhaObrigatoria').style.display = 'none';
    document.getElementById('senhaHelp').textContent = 'Deixe em branco para manter a senha atual';
    document.getElementById('usuarioPapel').value = usuario.papel;
    document.getElementById('usuarioUnidade').value = usuario.unidade_id || '';
    document.getElementById('usuarioAtivo').checked = usuario.ativo == 1;
    toggleUnidadeSelect();
    
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}

function toggleUnidadeSelect() {
    const papel = document.getElementById('usuarioPapel').value;
    const unidadeGroup = document.getElementById('unidadeGroup');
    const unidadeSelect = document.getElementById('usuarioUnidade');
    
    if (papel === 'diretor') {
        unidadeGroup.style.display = 'block';
        unidadeSelect.required = true;
    } else {
        unidadeGroup.style.display = 'none';
        unidadeSelect.required = false;
        unidadeSelect.value = '';
    }
}

function resetarSenha(id, nome) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('resetUserName').textContent = nome;
    const modal = new bootstrap.Modal(document.getElementById('modalResetSenha'));
    modal.show();
}

function excluirUsuario(id, nome) {
    if (confirm(`Deseja realmente excluir o usuário "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/usuarios/excluir';
        form.innerHTML = `<input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
