<?php 
$meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
?>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card p-3">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Unidade</small>
                    <h5 class="mb-0"><?= htmlspecialchars($escala['unidade_nome']) ?></h5>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Período</small>
                    <h5 class="mb-0"><?= $meses[$escala['mes']] ?>/<?= $escala['ano'] ?></h5>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Total de Horas</small>
                    <h5 class="mb-0"><?= number_format($escala['total_horas'], 0, ',', '.') ?>h</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 text-center">
            <span class="badge bg-<?= 
                $escala['status'] == 'pendente' ? 'warning' :
                ($escala['status'] == 'aprovada' ? 'success' :
                ($escala['status'] == 'executada' ? 'info' : 
                ($escala['status'] == 'rejeitada' ? 'danger' : 'secondary')))
            ?> fs-5 py-2">
                <?= ucfirst($escala['status']) ?>
            </span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Resumo por Servidor</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Servidor</th>
                            <th>Matrícula</th>
                            <th class="text-center">Horas</th>
                            <th class="text-center">Abono</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumoPorServidor as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nome']) ?></td>
                                <td><?= htmlspecialchars($r['matricula']) ?></td>
                                <td class="text-center"><?= number_format($r['horas'], 1) ?></td>
                                <td class="text-center"><?= number_format($r['abono'], 1) ?></td>
                                <td class="text-center fw-bold"><?= number_format($r['total'], 1) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Data de Envio:</th>
                        <td><?= $escala['enviado_em'] ? date('d/m/Y H:i', strtotime($escala['enviado_em'])) : '-' ?></td>
                    </tr>
                    <?php if ($escala['aprovado_em']): ?>
                    <tr>
                        <th>Data de Aprovação:</th>
                        <td><?= date('d/m/Y H:i', strtotime($escala['aprovado_em'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($escala['executado_em']): ?>
                    <tr>
                        <th>Data de Execução:</th>
                        <td><?= date('d/m/Y H:i', strtotime($escala['executado_em'])) ?></td>
                    </tr>
                    <tr>
                        <th>Valor Executado:</th>
                        <td class="text-success fw-bold">R$ <?= number_format($escala['valor_executado'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($escala['motivo_rejeicao']): ?>
                    <tr>
                        <th>Motivo da Rejeição:</th>
                        <td class="text-danger"><?= htmlspecialchars($escala['motivo_rejeicao']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$alocacoesAgrupadas = [];
foreach ($alocacoes as $a) {
    $key = $a['servidor_id'] . '_' . $a['equipe_id'] . '_' . $a['modulo_id'];
    if (!isset($alocacoesAgrupadas[$key])) {
        $alocacoesAgrupadas[$key] = [
            'servidor_nome' => $a['servidor_nome'],
            'matricula' => $a['matricula'],
            'equipe_nome' => $a['equipe_nome'],
            'modulo_nome' => $a['modulo_nome'],
            'is_lider' => $a['is_lider'],
            'dias' => [],
            'horas' => 0,
            'horas_abono' => 0
        ];
    }
    $alocacoesAgrupadas[$key]['dias'][] = str_pad($a['dia'], 2, '0', STR_PAD_LEFT);
    $alocacoesAgrupadas[$key]['horas'] += $a['horas'];
    $alocacoesAgrupadas[$key]['horas_abono'] += $a['horas_abono'];
    if ($a['is_lider']) {
        $alocacoesAgrupadas[$key]['is_lider'] = true;
    }
}
foreach ($alocacoesAgrupadas as &$ag) {
    sort($ag['dias']);
}
unset($ag);
?>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Alocações Detalhadas</h5>
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Servidor</th>
                    <th>Matrícula</th>
                    <th>Equipe</th>
                    <th>Módulo</th>
                    <th>Dias</th>
                    <th class="text-center">Horas</th>
                    <th class="text-center">Abono</th>
                    <th class="text-center">Líder</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alocacoesAgrupadas as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['servidor_nome']) ?></td>
                        <td><?= htmlspecialchars($a['matricula']) ?></td>
                        <td><?= htmlspecialchars($a['equipe_nome']) ?></td>
                        <td><?= htmlspecialchars($a['modulo_nome']) ?></td>
                        <td><span class="badge bg-light text-dark"><?= implode(', ', $a['dias']) ?></span></td>
                        <td class="text-center"><?= number_format($a['horas'], 1) ?></td>
                        <td class="text-center"><?= number_format($a['horas_abono'], 1) ?></td>
                        <td class="text-center">
                            <?php if ($a['is_lider']): ?>
                                <span class="badge bg-warning"><i class="bi bi-star-fill"></i></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <a href="/rh/escalas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
</div>
