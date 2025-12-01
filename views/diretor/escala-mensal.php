<div class="row mb-4">
    <div class="col-md-8">
        <div class="card p-3">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <label class="form-label mb-0">Mês:</label>
                    <select class="form-select" id="selectMes" onchange="alterarPeriodo()">
                        <?php 
                        $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                        foreach ($meses as $i => $m): ?>
                            <option value="<?= $i + 1 ?>" <?= ($i + 1) == $mes ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-0">Ano:</label>
                    <select class="form-select" id="selectAno" onchange="alterarPeriodo()">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="ms-auto">
                    <span class="badge bg-<?= 
                        $escala['status'] == 'rascunho' ? 'secondary' :
                        ($escala['status'] == 'pendente' ? 'warning' :
                        ($escala['status'] == 'aprovada' ? 'success' :
                        ($escala['status'] == 'executada' ? 'info' : 'danger')))
                    ?> fs-6">
                        Status: <?= ucfirst($escala['status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small>Total de Horas</small>
                    <h3 class="mb-0" id="totalHoras"><?= number_format($escala['total_horas'], 0, ',', '.') ?>h</h3>
                </div>
                <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
            </div>
        </div>
    </div>
</div>

<?php if ($escala['status'] == 'rejeitada'): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Escala Rejeitada:</strong> <?= htmlspecialchars($escala['motivo_rejeicao']) ?>
</div>
<?php endif; ?>

<?php if ($escala['status'] == 'rascunho'): ?>
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Adicionar Alocação</h5>
    </div>
    <div class="card-body">
        <form id="formAlocacao">
            <input type="hidden" name="escala_id" value="<?= $escala['id'] ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Servidor</label>
                    <select name="servidor_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($servidores as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?> (<?= $s['matricula'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Equipe</label>
                    <select name="equipe_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($equipes as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Módulo/Setor</label>
                    <select name="modulo_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($modulos as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Dia</label>
                    <select name="dia" class="form-select" required>
                        <?php for ($d = 1; $d <= $diasNoMes; $d++): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Horas</label>
                    <input type="number" name="horas" class="form-control" min="0" max="24" step="0.5" value="12" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Abono</label>
                    <input type="number" name="horas_abono" class="form-control" min="0" max="24" step="0.5" value="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Líder</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_lider" value="1" class="form-check-input">
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Alocações do Mês</h5>
        <button class="btn btn-outline-primary btn-sm" onclick="imprimirEscala()">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover" id="tabelaAlocacoes">
            <thead>
                <tr>
                    <th>Servidor</th>
                    <th>Matrícula</th>
                    <th>Equipe</th>
                    <th>Módulo</th>
                    <th class="text-center">Dia</th>
                    <th class="text-center">Horas</th>
                    <th class="text-center">Abono</th>
                    <th class="text-center">Líder</th>
                    <?php if ($escala['status'] == 'rascunho'): ?>
                    <th class="text-center">Ação</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alocacoes as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['servidor_nome']) ?></td>
                        <td><?= htmlspecialchars($a['matricula']) ?></td>
                        <td>
                            <?php 
                            $eq = array_filter($equipes, fn($e) => $e['id'] == $a['equipe_id']);
                            echo htmlspecialchars(reset($eq)['nome'] ?? '-');
                            ?>
                        </td>
                        <td>
                            <?php 
                            $mod = array_filter($modulos, fn($m) => $m['id'] == $a['modulo_id']);
                            echo htmlspecialchars(reset($mod)['nome'] ?? '-');
                            ?>
                        </td>
                        <td class="text-center"><?= $a['dia'] ?></td>
                        <td class="text-center"><?= number_format($a['horas'], 1) ?></td>
                        <td class="text-center"><?= number_format($a['horas_abono'], 1) ?></td>
                        <td class="text-center">
                            <?php if ($a['is_lider']): ?>
                                <span class="badge bg-warning"><i class="bi bi-star-fill"></i></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($escala['status'] == 'rascunho'): ?>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger" onclick="removerAlocacao(<?= $a['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function alterarPeriodo() {
    const mes = document.getElementById('selectMes').value;
    const ano = document.getElementById('selectAno').value;
    window.location.href = `/diretor/escala-mensal?mes=${mes}&ano=${ano}`;
}

document.getElementById('formAlocacao')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = new FormData(this);
    
    const response = await fetch('/diretor/escala/salvar-alocacao', {
        method: 'POST',
        body: form
    });
    
    const result = await response.json();
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao salvar');
    }
});

async function removerAlocacao(id) {
    if (!confirm('Remover esta alocação?')) return;
    
    const form = new FormData();
    form.append('id', id);
    
    const response = await fetch('/diretor/escala/remover-alocacao', {
        method: 'POST',
        body: form
    });
    
    const result = await response.json();
    if (result.success) {
        location.reload();
    } else {
        alert(result.message || 'Erro ao remover');
    }
}

function imprimirEscala() {
    window.print();
}
</script>
