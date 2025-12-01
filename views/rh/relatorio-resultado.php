<?php if (isset($printMode) && $printMode): ?>
<style>
@media print {
    body { padding: 20px; }
    .no-print { display: none !important; }
}
</style>
<?php endif; ?>

<div class="card no-print mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/rh/relatorios" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>
            Relatório de <?= $tipo === 'horas' ? 'Horas Aprovadas' : 'Valores Executados' ?> - <?= $ano ?>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <?php if (!empty($dados)): ?>
                        <?php foreach (array_keys($dados[0]) as $col): ?>
                            <th><?= ucfirst(str_replace('_', ' ', $col)) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Nenhum dado encontrado para os filtros selecionados</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dados as $row): ?>
                        <tr>
                            <?php foreach ($row as $key => $value): ?>
                                <td>
                                    <?php if (in_array($key, ['valor_executado', 'total_horas'])): ?>
                                        <?= is_numeric($value) ? number_format($value, 2, ',', '.') : htmlspecialchars($value ?? '-') ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($value ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="text-muted text-center mt-3 no-print">
    <small>Total de registros: <?= count($dados) ?></small>
</div>
