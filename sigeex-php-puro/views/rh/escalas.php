<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Ano</label>
                <select class="form-select" id="filtroAno" onchange="filtrar()">
                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filtroStatus" onchange="filtrar()">
                    <option value="todos" <?= $statusFiltro == 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="pendente" <?= $statusFiltro == 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                    <option value="aprovada" <?= $statusFiltro == 'aprovada' ? 'selected' : '' ?>>Aprovadas</option>
                    <option value="executada" <?= $statusFiltro == 'executada' ? 'selected' : '' ?>>Executadas</option>
                    <option value="rejeitada" <?= $statusFiltro == 'rejeitada' ? 'selected' : '' ?>>Rejeitadas</option>
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
                    <th>Unidade</th>
                    <th>Mês/Ano</th>
                    <th class="text-center">Total Horas</th>
                    <th>Data Envio</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Valor Exec.</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                foreach ($escalas as $e): 
                ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($e['unidade_nome']) ?></strong></td>
                        <td><?= $meses[$e['mes']] ?>/<?= $e['ano'] ?></td>
                        <td class="text-center"><?= number_format($e['total_horas'], 0, ',', '.') ?>h</td>
                        <td><?= $e['enviado_em'] ? date('d/m/Y H:i', strtotime($e['enviado_em'])) : '-' ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= 
                                $e['status'] == 'pendente' ? 'warning' :
                                ($e['status'] == 'aprovada' ? 'success' :
                                ($e['status'] == 'executada' ? 'info' : 
                                ($e['status'] == 'rejeitada' ? 'danger' : 'secondary')))
                            ?>">
                                <?= ucfirst($e['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?= $e['valor_executado'] ? 'R$ ' . number_format($e['valor_executado'], 2, ',', '.') : '-' ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="/rh/escalas/<?= $e['id'] ?>" class="btn btn-outline-primary" title="Detalhar">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($e['status'] == 'pendente'): ?>
                                    <button class="btn btn-success" onclick="aprovar(<?= $e['id'] ?>)" title="Aprovar">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="rejeitar(<?= $e['id'] ?>)" title="Rejeitar">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($e['status'] == 'aprovada'): ?>
                                    <button class="btn btn-info text-white" onclick="executar(<?= $e['id'] ?>)" title="Marcar como Executada">
                                        <i class="bi bi-check-all"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($escalas)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Nenhuma escala encontrada</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalRejeitar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeitar Escala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejeitarEscalaId">
                <div class="mb-3">
                    <label class="form-label">Motivo da Rejeição *</label>
                    <textarea id="motivoRejeicao" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRejeicao()">Rejeitar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExecutar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marcar como Executada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="executarEscalaId">
                <div class="mb-3">
                    <label class="form-label">Valor Financeiro Total (R$) *</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" id="valorExecutado" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" onclick="confirmarExecucao()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
function filtrar() {
    const ano = document.getElementById('filtroAno').value;
    const status = document.getElementById('filtroStatus').value;
    window.location.href = `/rh/escalas?ano=${ano}&status=${status}`;
}

async function aprovar(id) {
    if (!confirm('Confirma a aprovação desta escala?')) return;
    
    const form = new FormData();
    form.append('escala_id', id);
    
    const response = await fetch('/rh/escalas/aprovar', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao aprovar');
    }
}

function rejeitar(id) {
    document.getElementById('rejeitarEscalaId').value = id;
    document.getElementById('motivoRejeicao').value = '';
    new bootstrap.Modal(document.getElementById('modalRejeitar')).show();
}

async function confirmarRejeicao() {
    const id = document.getElementById('rejeitarEscalaId').value;
    const motivo = document.getElementById('motivoRejeicao').value;
    
    if (!motivo.trim()) {
        alert('Informe o motivo da rejeição');
        return;
    }
    
    const form = new FormData();
    form.append('escala_id', id);
    form.append('motivo', motivo);
    
    const response = await fetch('/rh/escalas/rejeitar', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao rejeitar');
    }
}

function executar(id) {
    document.getElementById('executarEscalaId').value = id;
    document.getElementById('valorExecutado').value = '';
    new bootstrap.Modal(document.getElementById('modalExecutar')).show();
}

async function confirmarExecucao() {
    const id = document.getElementById('executarEscalaId').value;
    const valor = document.getElementById('valorExecutado').value;
    
    if (!valor.trim()) {
        alert('Informe o valor executado');
        return;
    }
    
    const form = new FormData();
    form.append('escala_id', id);
    form.append('valor_executado', valor);
    
    const response = await fetch('/rh/escalas/executar', { method: 'POST', body: form });
    const result = await response.json();
    
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao executar');
    }
}
</script>
