<?php $__env->startSection('title', 'Diretor - Alertas'); ?>
<?php $__env->startSection('header', 'Central de Alertas'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="/diretor" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/diretor/escala-mensal" class="nav-link"><i class="bi bi-calendar3"></i> Escala Mensal</a>
    <a href="/diretor/servidores" class="nav-link"><i class="bi bi-people"></i> Servidores</a>
    <a href="/diretor/alertas" class="nav-link active"><i class="bi bi-bell"></i> Alertas</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col">
        <h2><i class="bi bi-bell me-2"></i>Alertas da Minha Unidade</h2>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Ano</label>
                <select name="ano" class="form-select">
                    <?php for($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($y == $ano ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mês</label>
                <select name="mes" class="form-select">
                    <option value="">Todos os meses</option>
                    <?php $nomesMeses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']; ?>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e($mes == $m ? 'selected' : ''); ?>><?php echo e($nomesMeses[$m]); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="vermelho" <?php echo e($tipo == 'vermelho' ? 'selected' : ''); ?>>Vermelho</option>
                    <option value="amarelo" <?php echo e($tipo == 'amarelo' ? 'selected' : ''); ?>>Amarelo</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo e($alertasPrazo->count()); ?></h3>
                <p class="mb-0">Alertas de Prazo</p>
                <small>Prazos e Correções</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3><?php echo e($alertasVermelho->count()); ?></h3>
                <p class="mb-0">Alertas Vermelho</p>
                <small>Margem Excedida</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body text-center">
                <h3><?php echo e($alertasAmarelo->count()); ?></h3>
                <p class="mb-0">Alertas Amarelo</p>
                <small>Acima do Previsto</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><?php echo e($escalasRejeitadas); ?></h3>
                <p class="mb-0">Escalas Rejeitadas</p>
                <small>Necessitam Correção</small>
            </div>
        </div>
    </div>
</div>

<?php if($alertasPrazo->count() > 0): ?>
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Alertas de Prazo</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            <?php $__currentLoopData = $alertasPrazo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="list-group-item list-group-item-action <?php echo e(str_contains($alerta->tipo, '5dias') || str_contains($alerta->tipo, '6horas') ? 'list-group-item-danger' : ''); ?>">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        <?php if(str_contains($alerta->tipo, 'correcao')): ?>
                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                        <?php else: ?>
                            <i class="bi bi-clock text-primary me-1"></i>
                        <?php endif; ?>
                        <?php echo e($alerta->titulo); ?>

                    </h6>
                    <small class="text-muted"><?php echo e($alerta->created_at->diffForHumans()); ?></small>
                </div>
                <p class="mb-1"><?php echo e($alerta->mensagem); ?></p>
                <?php if($alerta->prazo_limite): ?>
                <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>Prazo: <?php echo e($alerta->prazo_limite->format('d/m/Y H:i')); ?>

                    <?php if($alerta->prazo_limite->isPast()): ?>
                        <span class="badge bg-danger ms-2">Expirado</span>
                    <?php elseif($alerta->prazo_limite->diffInHours(now()) < 6): ?>
                        <span class="badge bg-warning text-dark ms-2">Urgente</span>
                    <?php endif; ?>
                </small>
                <?php endif; ?>
                <?php if($alerta->escala_id): ?>
                <div class="mt-2">
                    <a href="/diretor/escala-mensal?mes=<?php echo e($alerta->mes); ?>&ano=<?php echo e($alerta->ano); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Ver Escala
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($alertasVermelho->count() > 0): ?>
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i>Alertas Vermelho - Margem Excedida</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Limite c/ Margem</th>
                        <th>Valor Executado</th>
                        <th>Excedente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']; ?>
                    <?php $__currentLoopData = $alertasVermelho; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($meses[$alerta->mes]); ?>/<?php echo e($alerta->ano); ?></strong></td>
                        <td>R$ <?php echo e(number_format($alerta->limite_margem ?? 0, 2, ',', '.')); ?></td>
                        <td class="text-danger fw-bold">R$ <?php echo e(number_format($alerta->valor_executado, 2, ',', '.')); ?></td>
                        <td><span class="badge bg-danger">+R$ <?php echo e(number_format($alerta->valor_executado - ($alerta->limite_margem ?? 0), 2, ',', '.')); ?></span></td>
                        <td>
                            <a href="/diretor/escala-mensal?mes=<?php echo e($alerta->mes); ?>&ano=<?php echo e($alerta->ano); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Escala
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($alertasAmarelo->count() > 0): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Alertas Amarelo - Acima do Previsto</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Previsto</th>
                        <th>Valor Executado</th>
                        <th>Acima</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']; ?>
                    <?php $__currentLoopData = $alertasAmarelo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($meses[$alerta->mes]); ?>/<?php echo e($alerta->ano); ?></strong></td>
                        <td>R$ <?php echo e(number_format($alerta->orcamento_mes ?? 0, 2, ',', '.')); ?></td>
                        <td class="text-warning fw-bold">R$ <?php echo e(number_format($alerta->valor_executado, 2, ',', '.')); ?></td>
                        <td><span class="badge bg-warning text-dark">+R$ <?php echo e(number_format($alerta->valor_executado - ($alerta->orcamento_mes ?? 0), 2, ',', '.')); ?></span></td>
                        <td>
                            <a href="/diretor/escala-mensal?mes=<?php echo e($alerta->mes); ?>&ano=<?php echo e($alerta->ano); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Escala
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($escalasRejeitadas > 0): ?>
<div class="card mb-4 border-info">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Escalas Rejeitadas</h5>
    </div>
    <div class="card-body">
        <p>Você tem <?php echo e($escalasRejeitadas); ?> escala(s) rejeitada(s) que necessitam de correção.</p>
        <a href="/diretor/escala-mensal" class="btn btn-info text-white">
            <i class="bi bi-calendar3 me-1"></i>Ver Escalas
        </a>
    </div>
</div>
<?php endif; ?>

<?php if($alertasVermelho->count() == 0 && $alertasAmarelo->count() == 0 && $escalasRejeitadas == 0 && $alertasPrazo->count() == 0): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>Nenhum alerta encontrado para os filtros selecionados.
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/diretor/alertas.blade.php ENDPATH**/ ?>