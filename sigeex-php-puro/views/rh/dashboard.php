<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light p-3 me-3">
                        <i class="bi bi-hourglass-split text-secondary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Pendentes</div>
                        <div class="fs-3 fw-bold text-dark"><?= $estatisticas['pendentes'] ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-secondary" style="width: <?= min(100, ($estatisticas['pendentes'] ?? 0) * 10) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Aprovadas</div>
                        <div class="fs-3 fw-bold text-dark"><?= $estatisticas['aprovadas'] ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: <?= min(100, ($estatisticas['aprovadas'] ?? 0) * 10) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light p-3 me-3">
                        <i class="bi bi-check-all text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Executadas</div>
                        <div class="fs-3 fw-bold text-dark"><?= $estatisticas['executadas'] ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: <?= min(100, ($estatisticas['executadas'] ?? 0) * 10) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light p-3 me-3">
                        <i class="bi bi-x-circle text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Rejeitadas</div>
                        <div class="fs-3 fw-bold text-dark"><?= $estatisticas['rejeitadas'] ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: <?= min(100, ($estatisticas['rejeitadas'] ?? 0) * 10) ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-list-check me-2 text-primary"></i>Escalas Recentes</h5>
        <a href="/rh/escalas" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-right me-1"></i>Ver Todas
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="border-0">Unidade</th>
                    <th class="border-0">Mês/Ano</th>
                    <th class="border-0 text-center">Horas</th>
                    <th class="border-0 text-center">Status</th>
                    <th class="border-0 text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                foreach (array_slice($escalas, 0, 10) as $e): 
                    $statusClass = match($e['status']) {
                        'pendente' => 'bg-light text-secondary',
                        'aprovada' => 'bg-success-subtle text-success',
                        'executada' => 'bg-primary-subtle text-primary',
                        'rejeitada' => 'bg-danger-subtle text-danger',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($e['unidade_nome']) ?></strong></td>
                        <td class="text-muted"><?= $meses[$e['mes']] ?>/<?= $e['ano'] ?></td>
                        <td class="text-center"><?= number_format($e['total_horas'], 0, ',', '.') ?>h</td>
                        <td class="text-center">
                            <span class="badge <?= $statusClass ?> fw-normal px-3 py-2">
                                <?= ucfirst($e['status']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="/rh/escalas/<?= $e['id'] ?>" class="btn btn-sm btn-light border">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($escalas)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Nenhuma escala encontrada
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
