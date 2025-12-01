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
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">
            <div class="text-center">
                <span class="badge bg-<?= 
                    $escala['status'] == 'rascunho' ? 'secondary' :
                    ($escala['status'] == 'pendente' ? 'warning' :
                    ($escala['status'] == 'aprovada' ? 'success' :
                    ($escala['status'] == 'executada' ? 'info' : 'danger')))
                ?> fs-5 w-100 py-2">
                    Status: <?= ucfirst($escala['status']) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if ($escala['status'] == 'rejeitada'): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Escala Rejeitada:</strong> <?= htmlspecialchars($escala['motivo_rejeicao']) ?>
    <p class="mb-0 mt-2">Você pode editar a escala e enviar novamente após as correções.</p>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Resumo por Equipe</h5>
            </div>
            <div class="card-body">
                <?php if (empty($resumoEquipes)): ?>
                    <p class="text-muted text-center py-4">Nenhuma alocação registrada</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Equipe</th>
                                <th class="text-end">Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumoEquipes as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['equipe']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($r['total_horas'], 0, ',', '.') ?>h</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Resumo por Módulo</h5>
            </div>
            <div class="card-body">
                <?php if (empty($resumoModulos)): ?>
                    <p class="text-muted text-center py-4">Nenhuma alocação registrada</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Módulo/Setor</th>
                                <th class="text-end">Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumoModulos as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['modulo']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($r['total_horas'], 0, ',', '.') ?>h</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Total de Horas do Mês</h4>
            </div>
            <div class="col-md-6 text-end">
                <span class="display-4 text-primary"><?= number_format($escala['total_horas'], 0, ',', '.') ?>h</span>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body p-4">
        <?php if ($escala['status'] == 'rascunho'): ?>
            <form action="/diretor/escala/confirmar-envio" method="POST" onsubmit="return confirm('Confirma o envio da escala para aprovação do RH?')">
                <input type="hidden" name="escala_id" value="<?= $escala['id'] ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Pronto para enviar?</h5>
                        <p class="text-muted mb-0">Revise os dados acima antes de enviar para aprovação.</p>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" <?= $escala['total_horas'] == 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-send me-2"></i>Enviar para Aprovação do RH
                    </button>
                </div>
            </form>
        <?php elseif ($escala['status'] == 'pendente'): ?>
            <div class="text-center py-3">
                <i class="bi bi-hourglass-split text-warning" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Aguardando Aprovação</h5>
                <p class="text-muted">Sua escala foi enviada em <?= date('d/m/Y H:i', strtotime($escala['enviado_em'])) ?></p>
            </div>
        <?php elseif ($escala['status'] == 'aprovada'): ?>
            <div class="text-center py-3">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Escala Aprovada</h5>
                <p class="text-muted">Aprovada em <?= date('d/m/Y H:i', strtotime($escala['aprovado_em'])) ?></p>
            </div>
        <?php elseif ($escala['status'] == 'executada'): ?>
            <div class="text-center py-3">
                <i class="bi bi-check-all text-info" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Escala Executada</h5>
                <p class="text-muted">
                    Executada em <?= date('d/m/Y H:i', strtotime($escala['executado_em'])) ?><br>
                    Valor: R$ <?= number_format($escala['valor_executado'], 2, ',', '.') ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function alterarPeriodo() {
    const mes = document.getElementById('selectMes').value;
    const ano = document.getElementById('selectAno').value;
    window.location.href = `/diretor/enviar-escala?mes=${mes}&ano=${ano}`;
}
</script>
