<?php if (!$unidade): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Você não está vinculado a nenhuma unidade. Entre em contato com o administrador.
</div>
<?php else: ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($stats['orcamento_anual'], 0, ',', '.') ?></div>
                    <div class="stat-label">Orçamento Anual</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="bi bi-graph-down"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($stats['total_gasto'], 0, ',', '.') ?></div>
                    <div class="stat-label">Total Gasto</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($stats['disponivel'], 0, ',', '.') ?></div>
                    <div class="stat-label">Disponível</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($stats['horas_executadas'], 0, ',', '.') ?>h</div>
                    <div class="stat-label">Horas Executadas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$totalAlertas = count($stats['escalas_rejeitadas']) + count($stats['escalas_aprovadas']);
?>

<?php if (count($stats['escalas_rejeitadas']) > 0): ?>
<div class="alert alert-danger mb-4 d-flex align-items-center">
    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
    <div class="flex-grow-1">
        <strong>Escalas Rejeitadas!</strong> 
        <?php foreach ($stats['escalas_rejeitadas'] as $esc): ?>
            <span class="badge bg-danger ms-2"><?= $meses[$esc['mes']] ?>/<?= $esc['ano'] ?></span>
        <?php endforeach; ?>
        <div class="small mt-1">Verifique os motivos e faça as correções necessárias.</div>
    </div>
    <a href="/diretor/escala-mensal" class="btn btn-danger btn-sm">
        <i class="bi bi-pencil me-1"></i>Corrigir
    </a>
</div>
<?php endif; ?>

<?php if (count($stats['escalas_aprovadas']) > 0): ?>
<div class="alert alert-success mb-4 d-flex align-items-center">
    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
    <div class="flex-grow-1">
        <strong>Escalas Aprovadas!</strong> 
        <?php foreach ($stats['escalas_aprovadas'] as $esc): ?>
            <span class="badge bg-success ms-2"><?= $meses[$esc['mes']] ?>/<?= $esc['ano'] ?></span>
        <?php endforeach; ?>
        <div class="small mt-1">Aguardando execução pelo RH.</div>
    </div>
</div>
<?php endif; ?>

<?php if (count($stats['escalas_pendentes']) > 0): ?>
<div class="alert alert-warning mb-4 d-flex align-items-center">
    <i class="bi bi-hourglass-split me-3 fs-4"></i>
    <div class="flex-grow-1">
        <strong>Escalas Pendentes de Aprovação</strong> 
        <?php foreach ($stats['escalas_pendentes'] as $esc): ?>
            <span class="badge bg-warning text-dark ms-2"><?= $meses[$esc['mes']] ?>/<?= $esc['ano'] ?></span>
        <?php endforeach; ?>
        <div class="small mt-1">Aguardando análise do RH.</div>
    </div>
</div>
<?php endif; ?>

<?php 
$percentualUso = $stats['orcamento_anual'] > 0 ? ($stats['total_gasto'] / $stats['orcamento_anual']) * 100 : 0;
if ($percentualUso > 80): 
?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Atenção!</strong> O uso do orçamento está em <?= number_format($percentualUso, 1) ?>%. 
    Revise os gastos para não ultrapassar o limite.
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 h-100">
            <h6 class="card-title mb-3"><i class="bi bi-bar-chart me-2"></i>Gastos x Saldo Mensal</h6>
            <canvas id="chartMensal" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100 d-flex flex-column">
            <h6 class="card-title mb-3"><i class="bi bi-calendar-check me-2"></i>Escala do Mês</h6>
            <?php if ($stats['escala_mes_atual']): ?>
                <div class="text-center flex-grow-1 d-flex flex-column justify-content-center py-2">
                    <div class="fs-1 text-<?= 
                        $stats['escala_mes_atual']['status'] == 'aprovada' ? 'success' : 
                        ($stats['escala_mes_atual']['status'] == 'rejeitada' ? 'danger' : 
                        ($stats['escala_mes_atual']['status'] == 'pendente' ? 'warning' : 'secondary')) 
                    ?>">
                        <i class="bi bi-<?= 
                            $stats['escala_mes_atual']['status'] == 'aprovada' ? 'check-circle' : 
                            ($stats['escala_mes_atual']['status'] == 'rejeitada' ? 'x-circle' : 
                            ($stats['escala_mes_atual']['status'] == 'pendente' ? 'hourglass-split' : 'pencil-square')) 
                        ?>"></i>
                    </div>
                    <h5 class="mt-2 mb-1"><?= ucfirst($stats['escala_mes_atual']['status']) ?></h5>
                    <p class="text-muted small mb-3"><?= number_format($stats['escala_mes_atual']['total_horas'], 0, ',', '.') ?> horas</p>
                    <a href="/diretor/escala-mensal" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye me-2"></i>Ver Escala
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center flex-grow-1 d-flex flex-column justify-content-center py-2">
                    <div class="fs-1 text-muted">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h6 class="mt-2 mb-1">Nenhuma escala</h6>
                    <p class="text-muted small mb-3">Monte a escala deste mês</p>
                    <a href="/diretor/escala-mensal" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-2"></i>Criar Escala
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="card-title mb-3"><i class="bi bi-building me-2"></i>Informações da Unidade</h5>
            <table class="table table-borderless">
                <tr>
                    <th>Nome:</th>
                    <td><?= htmlspecialchars($unidade['nome']) ?></td>
                </tr>
                <tr>
                    <th>Local:</th>
                    <td><?= htmlspecialchars($unidade['local'] ?? '-') ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="card-title mb-3"><i class="bi bi-lightning me-2"></i>Ações Rápidas</h5>
            <div class="d-grid gap-2">
                <a href="/diretor/escala-mensal" class="btn btn-outline-primary">
                    <i class="bi bi-calendar3 me-2"></i>Montar Escala Mensal
                </a>
                <a href="/diretor/enviar-escala" class="btn btn-outline-success">
                    <i class="bi bi-send me-2"></i>Enviar Escala para Aprovação
                </a>
                <a href="/diretor/servidores" class="btn btn-outline-secondary">
                    <i class="bi bi-people me-2"></i>Ver Servidores
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    const gastosMensais = <?= json_encode($stats['gastos_mensais']) ?>;
    
    const dadosGastos = new Array(12).fill(0);
    gastosMensais.forEach(g => {
        dadosGastos[g.mes - 1] = parseFloat(g.gasto);
    });
    
    new Chart(document.getElementById('chartMensal'), {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Gasto (R$)',
                data: dadosGastos,
                backgroundColor: 'rgba(26, 68, 128, 0.8)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<?php endif; ?>
