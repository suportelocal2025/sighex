<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Servidores da Unidade</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Matrícula</th>
                    <th class="text-center">Ativo na Extra</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servidores)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Nenhum servidor cadastrado nesta unidade</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($servidores as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['nome']) ?></strong></td>
                            <td><?= htmlspecialchars($s['matricula']) ?></td>
                            <td class="text-center">
                                <?php if ($s['ativo_extra']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Sim</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-x-lg me-1"></i>Não</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="text-muted text-center mt-3">
    <small>Total: <?= count($servidores) ?> servidor(es)</small>
</div>
