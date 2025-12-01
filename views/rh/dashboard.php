<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-dark">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-dark bg-opacity-10 text-dark me-3">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $estatisticas['pendentes'] ?></div>
                    <div class="stat-label">Pendentes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-white bg-opacity-20 text-white me-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $estatisticas['aprovadas'] ?></div>
                    <div class="stat-label">Aprovadas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-white bg-opacity-20 text-white me-3">
                    <i class="bi bi-check-all"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $estatisticas['executadas'] ?></div>
                    <div class="stat-label">Executadas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-danger text-white">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-white bg-opacity-20 text-white me-3">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $estatisticas['rejeitadas'] ?></div>
                    <div class="stat-label">Rejeitadas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Escalas Recentes</h5>
        <a href="/rh/escalas" class="btn btn-primary btn-sm">
            <i class="bi bi-arrow-right me-2"></i>Ver Todas
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Unidade</th>
                    <th>Mês/Ano</th>
                    <th class="text-center">Horas</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                foreach (array_slice($escalas, 0, 10) as $e): 
                ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($e['unidade_nome']) ?></strong></td>
                        <td><?= $meses[$e['mes']] ?>/<?= $e['ano'] ?></td>
                        <td class="text-center"><?= number_format($e['total_horas'], 0, ',', '.') ?>h</td>
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
                        <td class="text-center">
                            <a href="/rh/escalas/<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
