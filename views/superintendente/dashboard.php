<div class="row mb-4">
    <div class="col-12">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <label class="form-label mb-0 me-2">Filtrar por Ano:</label>
                    <select class="form-select form-select-sm d-inline-block w-auto" onchange="window.location.href='?ano='+this.value">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $ano ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm <?= $periodo == 'mes' ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="filtrarPeriodo('mes')">Mês</button>
                    <button type="button" class="btn btn-sm <?= $periodo == 'trimestre' ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="filtrarPeriodo('trimestre')">Trimestre</button>
                    <button type="button" class="btn btn-sm <?= $periodo == 'ano' ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="filtrarPeriodo('ano')">Ano</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($orcamento['valor_total'] ?? 0, 0, ',', '.') ?></div>
                    <div class="stat-label">Orçamento Total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($reservaTecnica, 0, ',', '.') ?></div>
                    <div class="stat-label">Reserva Técnica</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($valorDisponivel, 0, ',', '.') ?></div>
                    <div class="stat-label">Disponível</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-arrow-down-up"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($totalDistribuido, 0, ',', '.') ?></div>
                    <div class="stat-label">Repassado</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="bi bi-graph-down"></i>
                </div>
                <div>
                    <div class="stat-value">R$ <?= number_format($totalGasto, 0, ',', '.') ?></div>
                    <div class="stat-label">Gastos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary me-3">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $totalUnidades ?></div>
                    <div class="stat-label">Unidades</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h5 class="card-title mb-4"><i class="bi bi-bar-chart me-2"></i>Gastos x Horas por Unidade</h5>
            <canvas id="chartGastosHoras" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h5 class="card-title mb-4"><i class="bi bi-pie-chart me-2"></i>Distribuição de Gastos</h5>
            <canvas id="chartDistribuicao"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Status das Unidades</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Unidade</th>
                    <th class="text-end">Repassado (R$)</th>
                    <th class="text-end">Gasto (R$)</th>
                    <th class="text-end">Horas</th>
                    <th class="text-end">Saldo (R$)</th>
                    <th class="text-center">Uso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidadesStats as $u): ?>
                    <?php 
                    $saldo = $u['orcamento_distribuido'] - $u['gasto_total'];
                    $percentual = $u['orcamento_distribuido'] > 0 ? ($u['gasto_total'] / $u['orcamento_distribuido']) * 100 : 0;
                    $corBarra = $percentual > 80 ? 'danger' : ($percentual > 50 ? 'warning' : 'success');
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                        <td class="text-end">R$ <?= number_format($u['orcamento_distribuido'], 2, ',', '.') ?></td>
                        <td class="text-end">R$ <?= number_format($u['gasto_total'], 2, ',', '.') ?></td>
                        <td class="text-end"><?= number_format($u['horas_total'], 0, ',', '.') ?>h</td>
                        <td class="text-end <?= $saldo < 0 ? 'text-danger' : 'text-success' ?>">
                            R$ <?= number_format($saldo, 2, ',', '.') ?>
                        </td>
                        <td style="width: 150px;">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-<?= $corBarra ?>" style="width: <?= min($percentual, 100) ?>%">
                                    <?= number_format($percentual, 0) ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filtrarPeriodo(p) {
    const url = new URL(window.location.href);
    url.searchParams.set('periodo', p);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const unidades = <?= json_encode(array_column($unidadesStats, 'nome')) ?>;
    const gastos = <?= json_encode(array_column($unidadesStats, 'gasto_total')) ?>;
    const horas = <?= json_encode(array_column($unidadesStats, 'horas_total')) ?>;
    
    new Chart(document.getElementById('chartGastosHoras'), {
        type: 'bar',
        data: {
            labels: unidades,
            datasets: [
                {
                    label: 'Gasto (R$)',
                    data: gastos,
                    backgroundColor: 'rgba(26, 68, 128, 0.8)',
                    borderRadius: 5
                },
                {
                    label: 'Horas',
                    data: horas,
                    backgroundColor: 'rgba(0, 169, 28, 0.8)',
                    borderRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    const cores = ['#1a4480', '#005ea2', '#00a91c', '#ffbe2e', '#d54309', '#5c5c5c', '#8168b3', '#0076d6'];
    
    new Chart(document.getElementById('chartDistribuicao'), {
        type: 'doughnut',
        data: {
            labels: unidades,
            datasets: [{
                data: gastos,
                backgroundColor: cores.slice(0, unidades.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });
});
</script>
