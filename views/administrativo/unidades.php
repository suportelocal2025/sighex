<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Unidades Cadastradas</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalImportar">
            <i class="bi bi-upload me-2"></i>Importar CSV
        </button>
        <a href="/admin/unidades/nova" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nova Unidade
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Local</th>
                    <th>Responsável</th>
                    <th class="text-center">Equipes</th>
                    <th class="text-center">Módulos</th>
                    <th class="text-center">Servidores</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($unidades)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Nenhuma unidade cadastrada</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($unidades as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($u['local'] ?? '-') ?></td>
                            <td>
                                <?php if ($u['responsavel_nome']): ?>
                                    <?= htmlspecialchars($u['responsavel_nome']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($u['responsavel_email']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= $u['total_equipes'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= $u['total_modulos'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $u['total_servidores'] ?></span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/unidades/<?= $u['id'] ?>/editar" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" onclick="excluirUnidade(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nome']) ?>')" title="Excluir">
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

<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Unidades via CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/unidades/importar" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        O arquivo CSV deve ter as colunas: <strong>Nome;Local</strong> (separado por ponto e vírgula)
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
function excluirUnidade(id, nome) {
    if (confirm(`Tem certeza que deseja excluir a unidade "${nome}"? Esta ação não pode ser desfeita.`)) {
        window.location.href = `/admin/unidades/${id}/excluir`;
    }
}
</script>
