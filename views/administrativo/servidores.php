<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Servidores Cadastrados</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalImportar">
            <i class="bi bi-upload me-2"></i>Importar CSV
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServidor" onclick="novoServidor()">
            <i class="bi bi-plus-lg me-2"></i>Novo Servidor
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Filtrar por Unidade</label>
                <select class="form-select" id="filtroUnidade" onchange="filtrar()">
                    <option value="">Todas</option>
                    <?php foreach ($unidades as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $filtroUnidade == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ativo na Extra</label>
                <select class="form-select" id="filtroAtivo" onchange="filtrar()">
                    <option value="">Todos</option>
                    <option value="1" <?= $filtroAtivo === '1' ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= $filtroAtivo === '0' ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Matrícula</th>
                    <th>Lotação</th>
                    <th class="text-center">Ativo na Extra</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servidores)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Nenhum servidor encontrado</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($servidores as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($s['matricula']) ?></td>
                            <td><?= htmlspecialchars($s['unidade_nome'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php if ($s['ativo_extra']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Sim</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-x-lg me-1"></i>Não</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editarServidor(<?= htmlspecialchars(json_encode($s)) ?>)" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="excluirServidor(<?= $s['id'] ?>)" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="text-muted text-center mt-3">
    <small>Total: <?= count($servidores) ?> servidor(es)</small>
</div>

<div class="modal fade" id="modalServidor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Novo Servidor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="servidorId">
                <div class="mb-3">
                    <label class="form-label">Nome *</label>
                    <input type="text" id="servidorNome" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Matrícula *</label>
                    <input type="text" id="servidorMatricula" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lotação</label>
                    <select id="servidorUnidade" class="form-select">
                        <option value="">Sem lotação</option>
                        <?php foreach ($unidades as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" id="servidorAtivo" class="form-check-input" checked>
                        <label class="form-check-label" for="servidorAtivo">Ativo na Escala Extra</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarServidor()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Servidores via CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/servidores/importar" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        O arquivo CSV deve ter as colunas: <strong>Nome;Matricula;Lotacao;Ativo</strong> 
                        (separado por ponto e vírgula). Ativo deve ser "Sim" ou "Não".
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Arquivo CSV</label>
                        <input type="file" name="arquivo" class="form-control" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filtrar() {
    const unidade = document.getElementById('filtroUnidade').value;
    const ativo = document.getElementById('filtroAtivo').value;
    let url = '/admin/servidores?';
    if (unidade) url += `unidade_id=${unidade}&`;
    if (ativo !== '') url += `ativo=${ativo}`;
    window.location.href = url;
}

function novoServidor() {
    document.getElementById('tituloModal').textContent = 'Novo Servidor';
    document.getElementById('servidorId').value = '';
    document.getElementById('servidorNome').value = '';
    document.getElementById('servidorMatricula').value = '';
    document.getElementById('servidorUnidade').value = '';
    document.getElementById('servidorAtivo').checked = true;
}

function editarServidor(s) {
    document.getElementById('tituloModal').textContent = 'Editar Servidor';
    document.getElementById('servidorId').value = s.id;
    document.getElementById('servidorNome').value = s.nome;
    document.getElementById('servidorMatricula').value = s.matricula;
    document.getElementById('servidorUnidade').value = s.unidade_id || '';
    document.getElementById('servidorAtivo').checked = s.ativo_extra;
    new bootstrap.Modal(document.getElementById('modalServidor')).show();
}

async function salvarServidor() {
    const form = new FormData();
    form.append('id', document.getElementById('servidorId').value);
    form.append('nome', document.getElementById('servidorNome').value);
    form.append('matricula', document.getElementById('servidorMatricula').value);
    form.append('unidade_id', document.getElementById('servidorUnidade').value);
    form.append('ativo_extra', document.getElementById('servidorAtivo').checked ? '1' : '0');
    
    const response = await fetch('/admin/servidores/salvar', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao salvar');
    }
}

async function excluirServidor(id) {
    if (!confirm('Tem certeza que deseja excluir este servidor?')) return;
    
    const form = new FormData();
    form.append('id', id);
    
    const response = await fetch('/admin/servidores/excluir', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao excluir');
    }
}
</script>
