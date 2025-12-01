<div class="row justify-content-center">
    <div class="col-lg-10">
        <form action="/admin/unidades/salvar" method="POST">
            <input type="hidden" name="id" value="<?= $unidade['id'] ?? '' ?>">
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Dados da Unidade</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome da Unidade *</label>
                            <input type="text" name="nome" class="form-control" 
                                   value="<?= htmlspecialchars($unidade['nome'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Local</label>
                            <input type="text" name="local" class="form-control" 
                                   value="<?= htmlspecialchars($unidade['local'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Diretor Responsável</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nome do Diretor</label>
                            <input type="text" name="responsavel_nome" class="form-control" 
                                   value="<?= htmlspecialchars($responsavel['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="responsavel_email" class="form-control" 
                                   value="<?= htmlspecialchars($responsavel['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Senha <?= $unidade ? '(deixe em branco para manter)' : '' ?></label>
                            <input type="password" name="responsavel_senha" class="form-control">
                        </div>
                    </div>
                    <?php if (!$unidade): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Ao criar a unidade, o sistema criará automaticamente 4 equipes padrão (A, B, C, D).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($unidade && !empty($modulos ?? [])): ?>
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Módulos/Setores</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarModulo()">
                        <i class="bi bi-plus-lg"></i> Adicionar
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-2" id="listaModulos">
                        <?php foreach ($modulos as $m): ?>
                            <div class="col-md-3" id="modulo-<?= $m['id'] ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($m['nome']) ?>" readonly>
                                    <button type="button" class="btn btn-outline-danger" onclick="removerModulo(<?= $m['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Equipes</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach ($equipes ?? [] as $e): ?>
                            <div class="col-md-3">
                                <div class="card text-center py-3">
                                    <strong><?= htmlspecialchars($e['nome']) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="/admin/unidades" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-2"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($unidade): ?>
<script>
async function adicionarModulo() {
    const nome = prompt('Nome do módulo/setor:');
    if (!nome || !nome.trim()) return;
    
    const form = new FormData();
    form.append('unidade_id', <?= $unidade['id'] ?>);
    form.append('nome', nome.trim());
    
    const response = await fetch('/admin/modulos/adicionar', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao adicionar');
    }
}

async function removerModulo(id) {
    if (!confirm('Remover este módulo?')) return;
    
    const form = new FormData();
    form.append('id', id);
    
    const response = await fetch('/admin/modulos/remover', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        document.getElementById('modulo-' + id).remove();
    } else {
        alert(result.message || 'Erro ao remover');
    }
}
</script>
<?php endif; ?>
